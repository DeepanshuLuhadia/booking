<?php

namespace App\Events;

use App\Models\Vendor;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Vendor status changed (approved, rejected, suspended, reinstated).
 *
 * Broadcast to the vendor and their account user so that their screen
 * (e.g., approval pending page) updates/redirects in real-time without requiring a page refresh.
 */
class VendorStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Vendor $vendor,
        public string $action = 'approved'
    ) {
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('vendor.' . $this->vendor->id),
        ];

        if ($this->vendor->user_id) {
            $channels[] = new PrivateChannel('App.Models.User.' . $this->vendor->user_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'vendor.status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'vendor_id'     => $this->vendor->id,
            'user_id'       => $this->vendor->user_id,
            'status'        => $this->vendor->status,
            'action'        => $this->action,
            'business_name' => $this->vendor->business_name,
            'redirect_url'  => route('vendor.dashboard'),
        ];
    }
}
