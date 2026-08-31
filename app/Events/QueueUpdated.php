<?php

namespace App\Events;

use App\Models\Booking;
use App\Models\Employee;
use App\Services\QueueVelocityService;
use App\Services\ShiftService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * One specialist's queue moved.
 *
 * This is the PUBLIC, customer-facing half of the realtime story: it goes to a
 * plain channel because the people who need it are guests with no account to
 * authorise against, and everything on it is already public through
 * /vendors/{slug}/queue-status.
 *
 * Because it is public, the payload is held to counters plus the token number
 * that changed. No customer name, no phone number, nothing that identifies a
 * person — a customer matches their own booking by the token they already hold.
 * Anything richer belongs on BookingChanged, which is private.
 */
class QueueUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $nowServing;
    public int $queueIndex;

    /**
     * Token numbers still waiting, in order. Sent so each customer screen can
     * work out its own position by counting the ones below its token, instead
     * of subtracting now_serving — which counts completed and cancelled tokens
     * as people still standing in front of you.
     *
     * Bare integers: nothing on this public channel identifies anybody.
     */
    public array $waitingTokens;

    /**
     * Whether anyone is actually in the chair, and how to word it. See
     * QueueVelocityService::servingState() — `now_serving_token` alone cannot
     * tell "being served" from "just finished".
     */
    public array $serving;

    /**
     * @param  array|null  $changed  ['booking_id' => int, 'token_number' => ?int, 'status' => string]
     *                               — the booking whose change moved the queue, so a
     *                               customer watching can tell whether it was theirs.
     */
    public function __construct(
        public Employee $employee,
        public ?array $changed = null
    ) {
        $employee = $employee->fresh() ?? $employee;

        $this->nowServing = (int) ($employee->now_serving_token ?? 0);

        // Highest token issued for the shift being worked — the same figure the
        // page rendered from, so the two cannot disagree.
        $this->queueIndex = (int) (Booking::where('employee_id', $employee->id)
            ->where('booking_date', app(ShiftService::class)->businessDate($employee->vendor))
            ->whereNotNull('token_number')
            ->max('token_number') ?? 0);

        $velocity            = app(QueueVelocityService::class);
        $this->waitingTokens = $velocity->waitingTokens($employee);
        $this->serving       = $velocity->servingState($employee);
    }

    public function broadcastOn(): array
    {
        return [new Channel('queue.' . $this->employee->id)];
    }

    public function broadcastAs(): string
    {
        return 'queue.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'employee_id' => $this->employee->id,
            'vendor_id'   => $this->employee->vendor_id,
            'now_serving'     => $this->nowServing,
            'queue_index'     => $this->queueIndex,
            'waiting_tokens'  => $this->waitingTokens,
            'serving_label'   => $this->serving['serving_label'],
            'serving_display' => $this->serving['serving_display'],
            'is_serving'      => $this->serving['is_serving'],
            'is_paused'       => (bool) $this->employee->is_paused,
            'changed'         => $this->changed,
        ];
    }
}
