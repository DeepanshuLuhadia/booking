<?php

namespace App\Services;

use App\Models\User;
use App\Models\Booking;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Send a web push notification to a user via FCM.
     */
    public function sendWebPush($user, $title, $message, $data = [])
    {
        if (!$user || !$user->fcm_token) {
            return false;
        }
        
        \App\Jobs\SendFcmNotificationJob::dispatch($user->fcm_token, $title, $message, $data);
        return true;
    }

    public function notifyVendorNewBooking($vendor, $booking)
    {
        $user = $vendor->user;

        $isPremium = $booking->booking_type === 'premium';
        $title = $isPremium ? "🔥 PRIORITY BOOKING RECEIVED!" : "New Booking Received!";
        $message = "{$booking->customer_name} booked a slot with {$booking->employee->name} at {$booking->slot_start_time}.";
        
        if ($isPremium) {
            $message .= " (Priority Fee: ₹{$booking->emergency_fee})";
        }
        
        // Notify Vendor Owner
        if ($user && $user->fcm_token) {
            $this->sendWebPush($user, $title, $message, [
                'booking_id' => $booking->id,
                'is_premium' => $isPremium,
                'fee' => $booking->emergency_fee
            ]);
        } else {
            Log::warning("Vendor #{$vendor->id} has no linked user or FCM token.");
        }

        // Notify Assigned Employee if they are a different user
        $employeeUser = $booking->employee->user ?? null;
        if ($employeeUser && $employeeUser->id !== ($user->id ?? 0) && $employeeUser->fcm_token) {
            $empTitle = $isPremium ? "🔥 NEW PRIORITY APPOINTMENT" : "New Appointment Assigned";
            $this->sendWebPush($employeeUser, $empTitle, $message, [
                'booking_id' => $booking->id,
            ]);
        }
    }

    /**
     * Ping the customers around the front of an employee's token queue after it
     * advances. Based on the employee's current `now_serving_token`:
     *   - token == now_serving      → "It's your turn now"
     *   - token == now_serving + 1  → "You're next"
     *
     * Each booking is notified at most once per stage (guarded by the
     * turn_notified_at / next_notified_at columns), so calling this repeatedly
     * for the same queue position is safe.
     */
    public function notifyTokenQueue($employee): void
    {
        if (!$employee) {
            return;
        }

        // Re-read so we act on the freshly-advanced token value.
        $employee = $employee instanceof Employee ? $employee->fresh() : Employee::find($employee);
        if (!$employee) {
            return;
        }

        $current = (int) $employee->now_serving_token;
        if ($current < 1) {
            return;
        }

        $this->pingTokenCustomer($employee, $current, 'turn');
        $this->pingTokenCustomer($employee, $current + 1, 'next');
    }

    /**
     * Send a single queue notification to the active booking holding $token for
     * this employee today, unless it has already been sent.
     */
    protected function pingTokenCustomer(Employee $employee, int $token, string $kind): void
    {
        $booking = Booking::where('employee_id', $employee->id)
            ->where('booking_date', Carbon::today()->toDateString())
            ->where('token_number', $token)
            ->whereIn('status', ['confirmed', 'pending'])
            ->first();

        if (!$booking || !$booking->fcm_token) {
            return;
        }

        $column = $kind === 'turn' ? 'turn_notified_at' : 'next_notified_at';
        if ($booking->{$column}) {
            return; // already notified for this stage
        }

        if ($kind === 'turn') {
            $title   = "🔔 It's your turn now!";
            $message = "Token #{$token} — {$employee->name} is ready for you. Please proceed.";
        } else {
            $title   = "⏳ You're next!";
            $message = "Token #{$token} — get ready, you're up right after the current customer.";
        }

        $customer = new User(['fcm_token' => $booking->fcm_token]);

        $sent = $this->sendWebPush($customer, $title, $message, [
            'booking_id'   => $booking->id,
            'token_number' => $token,
            'type'         => $kind === 'turn' ? 'your_turn' : 'up_next',
        ]);

        if ($sent) {
            $booking->forceFill([$column => Carbon::now()])->save();
        }
    }
}
