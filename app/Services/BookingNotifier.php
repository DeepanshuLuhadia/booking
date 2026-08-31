<?php

namespace App\Services;

use App\Events\BookingChanged;
use App\Events\QueueUpdated;
use App\Events\ShopStatusChanged;
use App\Models\Booking;
use App\Models\Employee;
use App\Models\Vendor;
use App\Notifications\DirectPaymentDue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Every change to a booking or a shop's availability, announced once, to
 * everyone it affects.
 *
 * Two channels of announcement have to happen together for each transition:
 *
 *   - a BROADCAST, so the pages already open (the vendor's dashboard, the
 *     specialist's dashboard, the customer's queue) redraw without a refresh;
 *   - a PUSH, so the people who are not looking at a page still find out.
 *
 * They used to be wired up per call site, and the result was predictable —
 * some transitions broadcast nothing, and most of the ones the shop performed
 * told the customer nothing at all. A customer's token could be completed,
 * skipped, or deleted out from under them in silence.
 *
 * So the call sites no longer choose. Each one names the transition that
 * happened and this class decides who hears about it, on both channels. Adding
 * a new transition means adding a method here, which is a much harder thing to
 * half-do than remembering two separate calls at a controller.
 *
 * `$actor` is always one of customer|vendor|employee|system. It never changes
 * *who* is notified — it changes the wording, because "you cancelled" and "the
 * customer cancelled" are the same event read from opposite ends.
 */
class BookingNotifier
{
    public function __construct(private NotificationService $notifications)
    {
    }

    /*
    |--------------------------------------------------------------------------
    | Customer-initiated
    |--------------------------------------------------------------------------
    */

    /**
     * A booking was made. `$actor` is 'customer' for a self-service booking and
     * 'vendor' for one the shop keyed in at the counter.
     */
    public function created(Booking $booking, string $actor = 'customer'): void
    {
        $booking = $this->hydrate($booking);

        $this->announce($booking, 'created', $actor);

        // A booking the shop keyed in itself needs no "you have a new booking"
        // push back to the shop — they are standing at the screen that made it.
        // The assigned specialist may not be, so they still hear about it.
        if ($actor === 'customer') {
            $this->notifications->notifyVendorNewBooking($booking->vendor, $booking);
        } elseif ($booking->employee?->user) {
            $this->notifications->notifyShop(
                $booking->vendor,
                $booking,
                'New Appointment Assigned',
                "{$booking->customer_name} was booked in with {$booking->employee->name}."
            );
        }
    }

    /** The customer dropped their own booking from the My Bookings page. */
    public function cancelledByCustomer(Booking $booking): void
    {
        $booking = $this->hydrate($booking);

        $this->announce($booking, 'cancelled', 'customer');
        $this->notifications->notifyVendorBookingCancelled($booking->vendor, $booking);
    }

    /*
    |--------------------------------------------------------------------------
    | Shop-initiated — the half that used to tell the customer nothing
    |--------------------------------------------------------------------------
    */

    public function completed(Booking $booking, string $actor = 'vendor'): void
    {
        $booking = $this->hydrate($booking);

        $this->announce($booking, 'completed', $actor);
        $this->notifications->notifyCustomerBookingCompleted($booking);

        // The other side of the shop — an employee marking their own appointment
        // done is news to the owner's dashboard, and vice versa.
        $this->notifyOtherSideOfShop(
            $booking,
            $actor,
            'Appointment Completed',
            $this->label($booking) . " with {$booking->employee?->name} was marked complete."
        );
    }

    public function cancelledByShop(Booking $booking, string $actor = 'vendor'): void
    {
        $booking = $this->hydrate($booking);

        $this->announce($booking, 'cancelled', $actor);
        $this->notifications->notifyCustomerBookingCancelled($booking, $actor);

        $this->notifyOtherSideOfShop(
            $booking,
            $actor,
            'Appointment Cancelled',
            $this->label($booking) . " with {$booking->employee?->name} was cancelled."
        );
    }

    /**
     * The shop could not serve this customer and passed over them. Same shape
     * as a cancellation — the booking closes and the queue moves on — but the
     * customer is told to rebook rather than simply that their slot is free.
     */
    public function skipped(Booking $booking, string $actor = 'vendor'): void
    {
        $booking = $this->hydrate($booking);

        $this->announce($booking, 'skipped', $actor);
        $this->notifications->notifyCustomerBookingSkipped($booking);

        $this->notifyOtherSideOfShop(
            $booking,
            $actor,
            'Appointment Skipped',
            $this->label($booking) . " with {$booking->employee?->name} was skipped for non-availability."
        );
    }

    /**
     * The shop deleted the booking outright. Announced before the row goes, so
     * the payload still has something to describe.
     */
    public function removed(Booking $booking, string $actor = 'vendor'): void
    {
        $booking = $this->hydrate($booking);

        $this->announce($booking, 'removed', $actor);
        $this->notifications->notifyCustomerBookingRemoved($booking);

        $this->notifyOtherSideOfShop(
            $booking,
            $actor,
            'Appointment Removed',
            $this->label($booking) . " with {$booking->employee?->name} was removed from the queue."
        );
    }

    /**
     * Bookings left standing on a shift that has finished, expired by the
     * nightly reset. Grouped by specialist so each queue is announced once
     * rather than once per row.
     */
    public function expired(Collection $bookings): void
    {
        // Per-model rather than on the collection: callers hand this both an
        // Eloquent collection (the nightly reset's query result) and a plain
        // one, and only the former knows how to eager-load.
        foreach ($bookings as $booking) {
            $this->hydrate($booking);

            // announce(), not a bare BookingChanged: the QueueUpdated it also
            // sends carries the booking id, which is what lets a customer still
            // sitting on their token screen recognise the expiry as theirs. A
            // per-employee summary broadcast could not tell them that.
            $this->announce($booking, 'expired', 'system');
            $this->notifications->notifyCustomerBookingExpired($booking);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Direct-to-vendor UPI payments
    |--------------------------------------------------------------------------
    | The platform is not in the money path and does not verify it. The customer
    | pays the shop's own UPI id from their own app; the shop reads its own app
    | to see the credit. All this class does is make sure the shop knows there
    | is a credit to look for, and knows which appointment it belongs to.
    */

    /**
     * The customer was handed to their UPI app to pay this shop directly.
     *
     * Fired alongside — not instead of — `created()`. The booking is already a
     * real, confirmed appointment by the time this runs; what this adds is the
     * one thing the booking announcement cannot say, which is that ₹X should be
     * arriving in the shop's account against it and nobody but the shop can
     * confirm that it did.
     *
     * Sent on three channels for the reason the money justifies: the FCM push
     * is the fast one, the stored notification is the one that survives a phone
     * being switched off, and the email reaches an owner who does not open the
     * dashboard daily.
     */
    public function directPaymentDue(Booking $booking): void
    {
        $booking = $this->hydrate($booking);

        $amount = number_format((float) $booking->requested_amount, 2);

        $this->notifications->notifyShop(
            $booking->vendor,
            $booking,
            'Online Payment — Please Check',
            "{$booking->customer_name} paid ₹{$amount} online for booking #{$booking->id}. "
                . 'Check your UPI app for the credit and confirm it with them at the counter.',
            ['type' => 'direct_payment_due', 'booking_id' => $booking->id]
        );

        // Never let a mail or database failure surface as a booking failure —
        // the appointment is already made and the money is already sent.
        try {
            $booking->vendor?->user?->notify(new DirectPaymentDue($booking));
        } catch (\Throwable $e) {
            Log::error('Direct payment notification failed for booking ' . $booking->id . ': ' . $e->getMessage());
        }
    }

    /**
     * The shop found the credit in its UPI app and ticked it off.
     *
     * Bookkeeping, not a gate: the appointment was confirmed when it was made
     * and nothing about it changes here. The customer is told anyway, because
     * having paid a stranger's UPI id and heard nothing back is the single most
     * uncomfortable moment in this flow.
     */
    public function paymentAcknowledged(Booking $booking): void
    {
        $booking = $this->hydrate($booking);

        $this->announce($booking, 'payment_verified', 'vendor');

        $this->notifications->notifyCustomer(
            $booking,
            'Payment Received',
            "{$booking->vendor?->business_name} has confirmed receiving your ₹"
                . number_format((float) $booking->requested_amount, 2)
                . ' payment.',
            ['type' => 'payment_verified', 'booking_id' => $booking->id]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Queue and availability
    |--------------------------------------------------------------------------
    */

    /**
     * The specialist called the next token. Redraws every queue on screen and
     * pings the customers now at the front (NotificationService guards against
     * telling the same person twice).
     */
    public function queueAdvanced(Employee $employee): void
    {
        if (!$employee) {
            return;
        }

        $this->broadcast(new QueueUpdated($employee));
        $this->notifications->notifyTokenQueue($employee);
    }

    /** A specialist went on, or came back from, a break. */
    public function employeePauseChanged(Employee $employee): void
    {
        $employee = $employee->fresh() ?? $employee;
        $vendor   = $employee->vendor;

        $this->broadcast(new QueueUpdated($employee));

        if ($vendor) {
            $this->broadcast(new ShopStatusChanged($vendor->load('employees'), $employee->id));
        }

        // Only the pause is worth a push. Resuming reaches people through the
        // queue moving again, and a "we're back" to everyone still waiting is
        // noise on top of the "you're next" they are about to get anyway.
        if ($employee->is_paused) {
            $this->notifications->notifyWaitingCustomers(
                $employee,
                'Queue Paused',
                "{$employee->name} at {$vendor?->business_name} has paused the queue for a moment. Your token is still held.",
                ['type' => 'queue_paused']
            );
        }
    }

    /** The shop opened, paused bookings, or shut for the day. */
    public function shopStatusChanged(Vendor $vendor, string $change = 'open'): void
    {
        $vendor = $vendor->fresh() ?? $vendor;
        $vendor->load('employees');

        $this->broadcast(new ShopStatusChanged($vendor));

        foreach ($vendor->employees as $employee) {
            $this->broadcast(new QueueUpdated($employee));
        }

        // Closing is the one that strands people — anyone still holding a token
        // needs to hear it rather than keep waiting for a turn that is not coming.
        if ($change === 'close') {
            foreach ($vendor->employees as $employee) {
                $this->notifications->notifyWaitingCustomers(
                    $employee,
                    'Shop Closed',
                    "{$vendor->business_name} has closed for the day. Please check with them about your booking.",
                    ['type' => 'shop_closed']
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Internals
    |--------------------------------------------------------------------------
    */

    /**
     * The broadcast pair every booking transition sends: the private, detailed
     * one for the shop's dashboards, and the public, anonymous one for the
     * customers watching the queue.
     */
    private function announce(Booking $booking, string $action, string $actor): void
    {
        $this->broadcast(new BookingChanged($booking, $action, $actor));

        if ($booking->employee) {
            $this->broadcast(new QueueUpdated($booking->employee, [
                'booking_id'   => $booking->id,
                'token_number' => $booking->token_number,
                'status'       => $booking->status,
                'action'       => $action,
            ]));
        }
    }

    /**
     * Fire a broadcast without ever letting it break the thing it is reporting on.
     *
     * Broadcasting is synchronous on the default queue connection, so an
     * unreachable socket server throws a BroadcastException straight up through
     * the caller. Unguarded, that is genuinely dangerous:
     *
     *   - a customer's booking is written, then the broadcast throws, and they
     *     are shown "booking failed" for an appointment that actually exists;
     *   - and because the throw happens before the notifications below it, the
     *     push nobody was waiting on never goes out either.
     *
     * Realtime is an enhancement. If Reverb is down the pages fall back to
     * polling and the pushes still go out — the only thing lost is the instant
     * redraw, which is exactly the trade the frontend already assumes.
     */
    private function broadcast(object $event): void
    {
        try {
            event($event);
        } catch (\Throwable $e) {
            Log::warning('Realtime broadcast failed (is `php artisan reverb:start` running?): ' . $e->getMessage(), [
                'event' => $event::class,
            ]);
        }
    }

    /**
     * Push to the side of the shop that did NOT perform the action — an owner
     * needs to know what their specialist did, and a specialist needs to know
     * what the owner did to their queue.
     */
    private function notifyOtherSideOfShop(Booking $booking, string $actor, string $title, string $message): void
    {
        $vendorUser   = $booking->vendor?->user;
        $employeeUser = $booking->employee?->user;

        $recipient = $actor === 'employee' ? $vendorUser : $employeeUser;

        // Owner and specialist are sometimes the same account; they performed
        // the action, so there is nobody else to tell.
        if (!$recipient || $recipient->id === ($actor === 'employee' ? $employeeUser?->id : $vendorUser?->id)) {
            return;
        }

        $this->notifications->sendWebPush($recipient, $title, $message, ['booking_id' => $booking->id]);
    }

    /** Relations every notification path reads; loaded once, defensively. */
    private function hydrate(Booking $booking): Booking
    {
        $booking->loadMissing(['employee.user', 'employee.vendor', 'vendor.user']);

        return $booking;
    }

    private function label(Booking $booking): string
    {
        return $booking->token_number
            ? "Token #{$booking->token_number}"
            : ($booking->appointment_at?->format('h:i A') ?? $booking->slot_start_time) . ' slot';
    }
}
