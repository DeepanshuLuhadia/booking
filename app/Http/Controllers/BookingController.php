<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Employee;
use App\Models\Booking;
use App\Services\PaymentService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function store(Request $request, PaymentService $paymentService, NotificationService $notificationService)
    {
        try {
            $request->validate([
                'vendor_id' => 'required|exists:vendors,id',
                'employee_id' => 'required|exists:employees,id',
                // Slot times only matter for time-slot mode; token-mode slot times
                // are computed server-side, so they are validated conditionally below.
                'slot_start' => 'nullable',
                'slot_end' => 'nullable',
                'booking_type' => 'required|in:normal,premium',
                'customer_name' => 'required|string|max:50',
                'customer_phone' => 'required|digits:10',
                'payment_id' => 'nullable|string'
            ]);

            // 1. Phone Throttling (3 bookings per day)
            $phoneKey = 'booking-phone:' . $request->customer_phone;
            if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($phoneKey, 3)) {
                return response()->json(['success' => false, 'error' => 'Booking limit reached for this phone number today.'], 429);
            }

            // 2. Cross-Vendor Daily Limit (Max 3 active tokens)
            $activeBookingsToday = Booking::where('customer_phone', $request->customer_phone)
                ->where('booking_date', Carbon::today()->toDateString())
                ->whereIn('status', ['confirmed', 'pending'])
                ->count();
            
            if ($activeBookingsToday >= 3) {
                return response()->json(['success' => false, 'error' => 'You have reached the maximum number of active bookings for today.'], 429);
            }

            // 3. One Active Token Per Vendor (Duplicate Check)
            $duplicate = Booking::where('vendor_id', $request->vendor_id)
                ->where('booking_date', Carbon::today()->toDateString())
                ->whereIn('status', ['confirmed', 'pending'])
                ->where(function ($q) use ($request) {
                    $q->where('customer_phone', $request->customer_phone);
                    if (auth()->check()) {
                        $q->orWhere('customer_id', auth()->id());
                    }
                })->exists();

            if ($duplicate) {
                return response()->json(['success' => false, 'error' => 'You already have an active booking with this vendor today.'], 422);
            }

            $vendor = Vendor::with('user')->findOrFail($request->vendor_id);

            // Time-slot bookings carry a real customer-chosen slot; enforce its
            // format. Token bookings ignore the submitted slot entirely.
            if ($vendor->appointment_mode !== 'token') {
                $request->validate([
                    'slot_start' => 'required|date_format:H:i',
                    'slot_end'   => 'required|date_format:H:i|after:slot_start',
                ]);
            }

            // 4. Vendor Status Validation
            if ($vendor->status !== 'active' || !$vendor->is_open) {
                return response()->json(['success' => false, 'error' => 'This vendor is not currently accepting bookings.'], 403);
            }
            if ($vendor->bookings_paused) {
                return response()->json(['success' => false, 'error' => 'Bookings are currently paused by the vendor.'], 403);
            }
            if (!$vendor->isSubscriptionActive()) {
                return response()->json(['success' => false, 'error' => 'Booking is not allowed as the business subscription has expired or is inactive.'], 403);
            }

            $employee = Employee::findOrFail($request->employee_id);

            $baseServiceFee = $employee->service_fee_override ?? $vendor->service_fee;
            $premiumFee = $request->booking_type === 'premium' ? ($employee->premium_fee ?? 0) : 0;
            $tokenAmount = ($vendor->appointment_mode === 'token') ? $vendor->token_amount : 0;
            $totalToPay = $tokenAmount + $premiumFee;

            $bookingDate = Carbon::today()->toDateString();
            $avgTime = $vendor->avg_consultation_time ?: 15;

            // Time-slot mode persists the customer's chosen slot. Token-mode slot
            // times are derived from the token number inside the transaction (each
            // token gets a distinct estimated time — this also keeps the
            // (employee, date, slot_start_time) unique index collision-free).
            $requestedStart = $request->slot_start;
            $requestedEnd   = $request->slot_end;

            // 5 & 6. Transaction & Token Cap
            $booking = \Illuminate\Support\Facades\DB::transaction(function () use ($vendor, $employee, $request, $bookingDate, $avgTime, $requestedStart, $requestedEnd, $tokenAmount, $premiumFee, $totalToPay, $baseServiceFee) {
                $tokenNumber = null;

                if ($vendor->appointment_mode === 'token') {
                    // Token queue is per-employee. Lock the employee's rows for today
                    // so two concurrent requests cannot read the same MAX.
                    $lastToken = Booking::where('employee_id', $employee->id)
                        ->where('booking_date', $bookingDate)
                        ->whereNotNull('token_number')
                        ->lockForUpdate()
                        ->max('token_number') ?? 0;

                    // Optional daily cap set by the employee (null = unlimited).
                    if ($employee->max_daily_tokens && $lastToken >= $employee->max_daily_tokens) {
                        throw new \Exception('No more tokens available for this employee today.', 403);
                    }

                    $tokenNumber = $lastToken + 1;

                    // Estimated start = now + (position in queue) * avg service time.
                    $estStart  = Carbon::now()->addMinutes(($tokenNumber - 1) * $avgTime);
                    $slotStart = $estStart->format('H:i:s');
                    $slotEnd   = $estStart->copy()->addMinutes($avgTime)->format('H:i:s');
                } else {
                    $slotStart = $requestedStart;
                    $slotEnd   = $requestedEnd;
                }

                return Booking::create([
                    'vendor_id'            => $vendor->id,
                    'employee_id'          => $employee->id,
                    'customer_id'          => auth()->id(),
                    'customer_name'        => $request->customer_name,
                    'customer_phone'       => $request->customer_phone,
                    'booking_date'         => $bookingDate,
                    'slot_start_time'      => $slotStart,
                    'slot_end_time'        => $slotEnd,
                    'booking_type'         => $request->booking_type,
                    'token_required'       => ($vendor->appointment_mode === 'token'),
                    'token_number'         => $tokenNumber,
                    'token_amount'         => $tokenAmount,
                    'emergency_fee'        => $premiumFee,
                    'online_paid_amount'   => $totalToPay,
                    'status'               => 'confirmed',
                    'vendor_booked'        => false,
                    'razorpay_payment_id'  => $request->payment_id,
                    'notes'                => "Service Fee: ₹{$baseServiceFee}"
                ]);
            });

            \Illuminate\Support\Facades\RateLimiter::hit($phoneKey, 86400);

            $booking->setRelation('employee', $employee);
            $notificationService->notifyVendorNewBooking($vendor, $booking);

            $fcmToken = session('fcm_token');
            if ($fcmToken) {
                $dummyUser = new \App\Models\User(['fcm_token' => $fcmToken]);
                $notificationService->sendWebPush(
                    $dummyUser,
                    "Booking Confirmed!",
                    "Your appointment with {$employee->name} at {$vendor->business_name} is confirmed."
                );
            }

            $nowServing = $employee->now_serving_token ?? 0;
            $peopleAhead = max(0, ($booking->token_number ?? 0) - $nowServing);
            $approxWait = $peopleAhead * ($vendor->avg_consultation_time ?? 15);

            return response()->json([
                'success' => true,
                'message' => 'Booking confirmed successfully!',
                'booking' => [
                    'id'              => $booking->id,
                    'token_number'    => $booking->token_number,
                    'vendor_name'     => $vendor->business_name,
                    'now_serving'     => $nowServing,
                    'people_ahead'    => $peopleAhead,
                    'approx_wait_min' => $approxWait,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error'   => collect($e->errors())->flatten()->first(),
            ], 422);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('BookingController@store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'error'   => 'Booking could not be completed. Please try again.',
            ], 500);
        }
    }
}
