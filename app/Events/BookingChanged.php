<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A booking appeared, or moved to a new status.
 *
 * Broadcast to the people running the shop — the vendor's dashboard and the
 * assigned specialist's dashboard — so neither has to refresh to see a booking
 * arrive or a queue change underneath them.
 *
 * This is the PRIVATE half of the realtime story: the payload carries the
 * customer's name and number because the shop needs them to work the queue.
 * The public, customer-facing half is QueueUpdated, which carries none of it.
 */
class BookingChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  string  $action  created|completed|cancelled|skipped|expired|removed
     * @param  string  $actor   customer|vendor|employee|system — who did it, so the
     *                          dashboard can word its toast ("cancelled by the
     *                          customer" reads very differently from "you cancelled").
     */
    public function __construct(
        public Booking $booking,
        public string $action,
        public string $actor = 'system'
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('vendor.' . $this->booking->vendor_id),
            new PrivateChannel('employee.' . $this->booking->employee_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'booking.changed';
    }

    public function broadcastWith(): array
    {
        $booking  = $this->booking;
        $employee = $booking->employee;

        return [
            'action' => $this->action,
            'actor'  => $this->actor,
            'booking' => [
                'id'             => $booking->id,
                'vendor_id'      => $booking->vendor_id,
                'employee_id'    => $booking->employee_id,
                'employee_name'  => $employee?->name,
                'customer_name'  => $booking->customer_name,
                'customer_phone' => $booking->customer_phone,
                'token_number'   => $booking->token_number,
                'booking_type'   => $booking->booking_type,
                'status'         => $booking->status,
                // appointment_at, not booking_date: an after-midnight slot sits
                // on the previous day's sheet but happens the following morning.
                'slot_time'      => $booking->appointment_at?->format('h:i A'),
                'slot_start_time' => $booking->slot_start_time,
                'booking_date'   => $booking->appointment_date_label,
            ],
            'now_serving' => (int) ($employee?->now_serving_token ?? 0),
        ];
    }
}
