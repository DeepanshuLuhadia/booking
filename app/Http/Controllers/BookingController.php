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
                'slot_start' => 'required',
                'slot_end' => 'required',
                'booking_type' => 'required|in:normal,premium',
                'customer_name' => 'required|string|max:50',
                'customer_phone' => 'required|digits:10',
                'payment_id' => 'nullable|string'
            ]);

            $vendor = Vendor::with('user')->findOrFail($request->vendor_id);
            if (!$vendor->isSubscriptionActive()) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Booking is not allowed as the business subscription has expired or is inactive.',
                ], 403);
            }
            $employee = Employee::findOrFail($request->employee_id);

            // Pricing Logic: Use employee override if set, otherwise vendor default
            $baseServiceFee = $employee->service_fee_override ?? $vendor->service_fee;

            // Priority/Premium Booking Fee from Employee setting
            $premiumFee = $request->booking_type === 'premium' ? ($employee->premium_fee ?? 0) : 0;

            $tokenAmount = ($vendor->appointment_mode === 'token') ? $vendor->token_amount : 0;

            // Final amount customer pays online (Token + Premium Fee)
            $totalToPay = $tokenAmount + $premiumFee;

            // Token Generation & Time Slot Logic
            $tokenNumber = null;
            $slotStart = $request->slot_start;
            $slotEnd = $request->slot_end;

            if ($vendor->appointment_mode === 'token') {
                // Tokens are employee-specific as per user request
                $lastToken = Booking::where('employee_id', $employee->id)
                    ->where('booking_date', Carbon::today()->toDateString())
                    ->whereNotNull('token_number')
                    ->max('token_number');
                $tokenNumber = ($lastToken ?? 0) + 1;
                
                // Use current time with seconds to minimize unique constraint collisions
                $slotStart = Carbon::now()->format('H:i:s');
                $slotEnd = Carbon::now()->addMinutes(10)->format('H:i:s');
            }

            // Create booking
            $booking = Booking::create([
                'vendor_id'            => $vendor->id,
                'employee_id'          => $employee->id,
                'customer_id'          => null,
                'customer_name'        => $request->customer_name,
                'customer_phone'       => $request->customer_phone,
                'booking_date'         => Carbon::today()->toDateString(),
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

            // Eager load employee to prevent N+1 in NotificationService
            $booking->setRelation('employee', $employee);

            // Notify Vendor and Employee
            $notificationService->notifyVendorNewBooking($vendor, $booking);

            // Notify Customer if they provided FCM token via session
            $fcmToken = session('fcm_token');
            if ($fcmToken) {
                // We create a dummy user just to pass the fcm token to the service
                $dummyUser = new \App\Models\User(['fcm_token' => $fcmToken]);
                $notificationService->sendWebPush(
                    $dummyUser,
                    "Booking Confirmed!",
                    "Your appointment with {$employee->name} at {$vendor->business_name} is confirmed for {$slotStart}."
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Booking confirmed successfully!',
                'booking' => $booking
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
