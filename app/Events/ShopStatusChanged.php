<?php

namespace App\Events;

use App\Models\Vendor;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A shop opened, closed, paused bookings, or one of its specialists went on or
 * off a break.
 *
 * Public, for the same reason as QueueUpdated: the audience is guests sitting
 * on a vendor page, and whether a shop is open is already public. Carries
 * availability flags only.
 *
 * Without this a customer stares at a live booking form for a shop that closed
 * two minutes ago, and only finds out when the server refuses them.
 */
class ShopStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Vendor $vendor,
        public ?int $employeeId = null
    ) {
    }

    public function broadcastOn(): array
    {
        return [new Channel('shop.' . $this->vendor->id)];
    }

    public function broadcastAs(): string
    {
        return 'shop.status';
    }

    public function broadcastWith(): array
    {
        $vendor = $this->vendor->fresh() ?? $this->vendor;

        return [
            'vendor_id'       => $vendor->id,
            'is_open'         => (bool) $vendor->is_open,
            'bookings_paused' => (bool) $vendor->bookings_paused,
            'status'          => $vendor->status,
            // Set when the change was one specialist going on or off a break,
            // rather than the whole shop's state.
            'employee_id'     => $this->employeeId,
            'employees'       => $vendor->employees->map(fn ($employee) => [
                'id'        => $employee->id,
                'is_paused' => (bool) $employee->is_paused,
                'is_active' => (bool) $employee->is_active,
            ])->values()->all(),
        ];
    }
}
