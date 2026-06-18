<?php

namespace App\Services;

use App\Models\User;
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
        
        return $this->fcmService->sendToToken($user->fcm_token, $title, $message, $data);
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
}
