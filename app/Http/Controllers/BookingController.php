<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Employee;
use App\Models\Booking;
use App\Services\BookingNotifier;
use App\Services\CustomerBookingService;
use App\Services\PaymentService;
use App\Services\NotificationService;
use App\Services\ShiftService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function store(
        Request $request,
        PaymentService $paymentService,
        NotificationService $notificationService,
        ShiftService $shifts,
        CustomerBookingService $customerBookings,
        BookingNotifier $bookingNotifier
    ) {
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

            $vendor = Vendor::with('user')->findOrFail($request->vendor_id);

            /*
            | The shift this booking belongs to, NOT the calendar date. A shop
            | trading 22:00 → 02:00 is still working the same sheet at 00:30, so
            | filing that booking under "today" would restart its token sequence
            | mid-shift and hide it from the queue the vendor is looking at.
            */
            $bookingDate = $shifts->businessDate($vendor);

            // 2. Cross-Vendor Daily Limit (Max 3 active tokens). Spans the two
            // live business dates so an overnight shift keeps counting the
            // tokens a customer already holds instead of resetting at midnight.
            $activeBookingsToday = Booking::where('customer_phone', $request->customer_phone)
                ->whereIn('booking_date', $shifts->liveBusinessDates())
                ->whereIn('status', ['confirmed', 'pending'])
                ->count();

            if ($activeBookingsToday >= 3) {
                // Point at the page that lists them. The limit counts bookings
                // made with *other* vendors too, which the customer cannot see
                // from here — without the link this reads as an arbitrary refusal.
                return response()->json([
                    'success'      => false,
                    'error'        => 'You have reached the maximum number of active bookings for today.',
                    'bookings_url' => route('bookings.mine'),
                ], 429);
            }

            /*
            | 3. One Active Token Per Vendor (Duplicate Check), scoped to the shift.
            |
            | Only `pending` / `confirmed` block. The moment the vendor marks the
            | appointment completed — or it is cancelled, skipped, or expired by
            | the nightly reset — this vendor is bookable again for the customer.
            */
            $duplicate = Booking::where('vendor_id', $vendor->id)
                ->where('booking_date', $bookingDate)
                ->whereIn('status', ['confirmed', 'pending'])
                ->where(function ($q) use ($request) {
                    $q->where('customer_phone', $request->customer_phone);
                    if (auth()->check()) {
                        $q->orWhere('customer_id', auth()->id());
                    }
                })->exists();

            if ($duplicate) {
                return response()->json([
                    'success'      => false,
                    'error'        => 'You already have an active booking with this business. You can book again once it is completed.',
                    'bookings_url' => route('bookings.mine'),
                ], 422);
            }

            // Time-slot bookings carry a real customer-chosen slot; enforce its
            // format. Token bookings ignore the submitted slot entirely.
            if ($vendor->appointment_mode !== 'token') {
                $request->validate([
                    'slot_start' => 'required|date_format:H:i',
                    // NOTE: cannot use `after:slot_start` here. Overnight shops
                    // legitimately generate slots that wrap past midnight
                    // (e.g. 23:45 -> 00:00), and `after` compares both values on
                    // the SAME day, so it wrongly rejects the wrapped end time.
                    // The slot values are server-generated and chosen from a fixed
                    // list, so we only guard the format and that the two differ.
                    'slot_end'   => 'required|date_format:H:i|different:slot_start',
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

                    // Verify slot availability under pessimistic lock to prevent concurrent double booking
                    $alreadyBooked = Booking::where('employee_id', $employee->id)
                        ->where('booking_date', $bookingDate)
                        ->where('slot_start_time', $slotStart)
                        ->whereIn('status', ['confirmed', 'pending'])
                        ->lockForUpdate()
                        ->exists();

                    if ($alreadyBooked) {
                        throw new \Exception('This slot was just booked by another customer. Please select another time.', 409);
                    }
                }

                return Booking::create([
                    'vendor_id'            => $vendor->id,
                    'employee_id'          => $employee->id,
                    'customer_id'          => auth()->id(),
                    'customer_name'        => $request->customer_name,
                    'customer_phone'       => $request->customer_phone,
                    /*
                    | The device to ping when this customer's token is called.
                    |
                    | Guests carry it in the session; a signed-in customer has it
                    | on their user record instead (FcmTokenController writes to
                    | one or the other), so both are consulted. Still null when
                    | they have not granted notifications yet — that case is
                    | repaired later by CustomerBookingService::attachDeviceToken,
                    | because permission is asked for only after this point.
                    */
                    'fcm_token'            => session('fcm_token')
                        ?? (auth()->check() ? auth()->user()->fcm_token : null),
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

            // Remember the guest so every booking page can recognise them on the
            // next visit and show their live token instead of the "book" button.
            // Guests have no account, so the phone number is the only identity we
            // hold — and the service keeps *every* number booked from this device,
            // not just the latest, so booking for a second person no longer hides
            // the first person's booking.
            $customerBookings->remember($booking->customer_phone, $request);

            // Invalidate discovery and slot caches so the next listing/slot request
            // reflects the newly confirmed booking immediately.
            \Illuminate\Support\Facades\Cache::forget('default_discovery_candidates');
            \Illuminate\Support\Facades\Cache::forget("slots:{$employee->id}:{$bookingDate}");

            $booking->setRelation('employee', $employee);
            $booking->setRelation('vendor', $vendor);

            // Announces on both channels at once — the shop's dashboards redraw
            // live, and the owner and specialist get their push. See BookingNotifier.
            $bookingNotifier->created($booking, 'customer');

            $fcmToken = session('fcm_token');
            if ($fcmToken) {
                $dummyUser = new \App\Models\User(['fcm_token' => $fcmToken]);
                
                // Smart Notification Title Logic
                $hour = now()->hour;
                $greeting = 'Booking Confirmed!';
                
                if ($hour >= 5 && $hour < 12) {
                    $greeting = 'Start your day right! 🌅';
                } elseif ($hour >= 12 && $hour < 17) {
                    $greeting = 'Good Afternoon! ☀️';
                } elseif ($hour >= 17 && $hour < 22) {
                    $greeting = 'Evening plans set! 🌙';
                }

                $cat = strtolower($vendor->category?->slug ?? '');
                if (in_array($cat, ['salon', 'barber', 'beauty'])) {
                    $title = "{$greeting} Your grooming appointment is confirmed. ✂️";
                } elseif (in_array($cat, ['clinic', 'doctor', 'health'])) {
                    $title = "{$greeting} Your checkup at {$vendor->business_name} is booked. 🩺";
                } elseif (in_array($cat, ['sports', 'gym', 'turf'])) {
                    $title = "{$greeting} Game on! Your slot at {$vendor->business_name} is confirmed. ⚽";
                } else {
                    $title = "{$greeting} Your slot with {$employee->name} is confirmed.";
                }

                $notificationService->sendWebPush(
                    $dummyUser,
                    $title,
                    "Your appointment with {$employee->name} at {$vendor->business_name} is confirmed."
                );
            }

            $nowServing = $employee->now_serving_token ?? 0;

            $queueVelocityService = new \App\Services\QueueVelocityService();

            // Counted, not `token - now_serving`. now_serving_token records the
            // last token *handled*, so subtracting it counts the customer who has
            // just been finished with as somebody still in front of you — the
            // confirmation screen said "1 ahead" when the queue was clear.
            $peopleAhead = $queueVelocityService->peopleAheadOf($employee, $booking->token_number ?? 0);
            $approxWait  = $queueVelocityService->calculateEstimatedWait($vendor, $employee, $booking->token_number ?? 0);
            $serving     = $queueVelocityService->servingState($employee);

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
                    'serving_label'   => $serving['serving_label'],
                    'serving_display' => $serving['serving_display'],
                    // The date the customer actually turns up on. Differs from
                    // the business date for after-midnight slots, so the
                    // confirmation screen must use this and not "today".
                    'booking_date'    => $booking->appointment_date_label,
                    'slot_time'       => $booking->appointment_at?->format('h:i A'),
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error'   => collect($e->errors())->flatten()->first(),
            ], 422);

        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::warning('BookingController@store database conflict: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error'   => 'This slot was just booked by another customer. Please choose a different time.',
            ], 409);

        } catch (\Throwable $e) {
            $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? (int) $e->getCode() : 500;
            \Illuminate\Support\Facades\Log::error('BookingController@store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage() ?: 'Booking could not be completed. Please try again.',
            ], $code);
        }
    }
}
