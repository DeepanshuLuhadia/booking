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
     * Tell the shop that a customer has cancelled their own appointment.
     *
     * The vendor is looking at a queue that just changed underneath them — a
     * token they were counting on is no longer coming — so this mirrors
     * notifyVendorNewBooking rather than leaving the cancellation to be
     * discovered when the customer fails to turn up.
     */
    public function notifyVendorBookingCancelled($vendor, $booking): void
    {
        $who = $booking->token_number
            ? "Token #{$booking->token_number}"
            : "The {$booking->slot_start_time} slot";

        $this->notifyShop(
            $vendor,
            $booking,
            "Booking Cancelled",
            "{$who} with {$booking->employee?->name} was cancelled by {$booking->customer_name}."
        );
    }

    /**
     * Push to both sides of the shop: the owner, and the specialist the booking
     * belongs to when that is a different account.
     *
     * Every shop-facing notification funnels through here so no future caller
     * can remember the owner and forget the specialist, which is exactly how
     * half these notifications went missing in the first place.
     */
    public function notifyShop($vendor, $booking, string $title, string $message, array $data = []): void
    {
        $data += ['booking_id' => $booking?->id];

        $owner = $vendor?->user;

        if ($owner && $owner->fcm_token) {
            $this->sendWebPush($owner, $title, $message, $data);
        } elseif ($vendor) {
            Log::info("Vendor #{$vendor->id} has no linked user or FCM token; shop push skipped.");
        }

        $employeeUser = $booking?->employee?->user;

        if ($employeeUser && $employeeUser->id !== ($owner->id ?? 0) && $employeeUser->fcm_token) {
            $this->sendWebPush($employeeUser, $title, $message, $data);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Customer-facing notifications
    |--------------------------------------------------------------------------
    | The shop acting on a booking is invisible to the customer otherwise: they
    | are sitting somewhere with a token, and being completed, cancelled or
    | skipped changes whether they should still be waiting. Each of these is a
    | point where the customer used to find out only by refreshing.
    */

    public function notifyCustomerBookingCompleted($booking): void
    {
        $this->notifyCustomer(
            $booking,
            "✅ Appointment Complete",
            "Your appointment with {$booking->employee?->name} at {$booking->vendor?->business_name} is complete. Thanks for visiting!",
            ['type' => 'booking_completed']
        );
    }

    /**
     * @param  string  $actor  'vendor' or 'employee' — a customer deserves to know
     *                         the shop cancelled on them, not just that it happened.
     */
    public function notifyCustomerBookingCancelled($booking, string $actor = 'vendor'): void
    {
        $by = $actor === 'employee'
            ? ($booking->employee?->name ?? 'your specialist')
            : ($booking->vendor?->business_name ?? 'the business');

        $this->notifyCustomer(
            $booking,
            "Appointment Cancelled",
            "{$by} has cancelled your " . $this->bookingLabel($booking) . ". You are free to book again.",
            ['type' => 'booking_cancelled']
        );
    }

    public function notifyCustomerBookingSkipped($booking): void
    {
        $this->notifyCustomer(
            $booking,
            "Token Skipped",
            "Token #{$booking->token_number} was skipped at {$booking->vendor?->business_name}. Please speak to the counter if you are still waiting.",
            ['type' => 'booking_skipped']
        );
    }

    public function notifyCustomerBookingRemoved($booking): void
    {
        $this->notifyCustomer(
            $booking,
            "Appointment Removed",
            "{$booking->vendor?->business_name} has removed your " . $this->bookingLabel($booking) . ". You are free to book again.",
            ['type' => 'booking_removed']
        );
    }

    public function notifyCustomerBookingExpired($booking): void
    {
        $this->notifyCustomer(
            $booking,
            "Appointment Expired",
            "Your " . $this->bookingLabel($booking) . " at {$booking->vendor?->business_name} expired when the shift closed.",
            ['type' => 'booking_expired']
        );
    }

    /**
     * Ping everyone still holding a live booking with this specialist — used
     * when the shop stops serving (a break, bookings paused, shutting early).
     * People are physically waiting; they need to hear it without refreshing.
     */
    public function notifyWaitingCustomers($employee, string $title, string $message, array $data = []): int
    {
        if (!$employee) {
            return 0;
        }

        $bookings = Booking::where('employee_id', $employee->id)
            ->where('booking_date', app(ShiftService::class)->businessDate($employee->vendor))
            ->whereIn('status', ['confirmed', 'pending'])
            ->with(['vendor', 'employee'])
            ->get();

        $sent = 0;
        foreach ($bookings as $booking) {
            if ($this->notifyCustomer($booking, $title, $message, $data)) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Push to whoever made this booking.
     *
     * A guest has no account, so their device token is stamped on the booking
     * row itself; a signed-in customer carries it on their user record. Try the
     * booking first — it is the one that is right for the device that actually
     * booked.
     */
    public function notifyCustomer($booking, string $title, string $message, array $data = []): bool
    {
        $recipient = $this->customerRecipient($booking);

        if (!$recipient) {
            return false;
        }

        return (bool) $this->sendWebPush($recipient, $title, $message, $data + [
            'booking_id'   => $booking->id,
            'token_number' => $booking->token_number,
        ]);
    }

    protected function customerRecipient($booking): ?User
    {
        if (!$booking) {
            return null;
        }

        if ($booking->fcm_token) {
            return new User(['fcm_token' => $booking->fcm_token]);
        }

        return $booking->customer_id ? User::find($booking->customer_id) : null;
    }

    /** "token #4" / "10:30 AM slot" — how the customer thinks of the booking. */
    protected function bookingLabel($booking): string
    {
        if ($booking->token_number) {
            return "token #{$booking->token_number}";
        }

        $time = $booking->appointment_at?->format('h:i A') ?? $booking->slot_start_time;

        return $time ? "{$time} slot" : 'appointment';
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
            // Business date of the shift this employee is working, so tokens
            // called after midnight still find their customer.
            ->where('booking_date', app(ShiftService::class)->businessDate($employee->vendor))
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
