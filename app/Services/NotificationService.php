<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send a web push notification to a user.
     * For production, integrate with OneSignal or Pusher Beam.
     */
    public function sendWebPush($user, $title, $message, $data = [])
    {
        if (!$user) {
            return false;
        }
        // Placeholder for real integration
        Log::info("Web Push sent to {$user->email}: {$title} - {$message}", $data);
        
        // In a real app, you would use a broadcast or direct API call to OneSignal
        // broadcast(new BookingReceived($user, $title, $message));
        
        return true;
    }

    public function notifyVendorNewBooking($vendor, $booking)
    {
        $user = $vendor->user;

        if (!$user) {
            Log::warning("Vendor #{$vendor->id} has no linked user — skipping booking notification.");
            return;
        }
        $isPremium = $booking->booking_type === 'emergency';
        
        $title = $isPremium ? "🔥 PREMIUM BOOKING RECEIVED!" : "New Booking Received!";
        $message = "{$booking->customer_name} booked a slot with {$booking->employee->name} at {$booking->slot_start_time}.";
        
        if ($isPremium) {
            $message .= " (Premium Fee: ₹{$booking->emergency_fee})";
        }
        
        $this->sendWebPush($user, $title, $message, [
            'booking_id' => $booking->id,
            'is_premium' => $isPremium,
            'fee' => $booking->emergency_fee
        ]);
    }
}
