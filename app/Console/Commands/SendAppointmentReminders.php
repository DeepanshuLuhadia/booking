<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Services\NotificationService;
use Carbon\Carbon;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-appointment-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends FCM push notifications to customers and employees 1 hour before their appointment.';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService)
    {
        $now = Carbon::now();
        // Look for bookings that start exactly within the next 60 minutes
        $start = $now->copy()->addMinutes(55)->format('H:i:00');
        $end = $now->copy()->addMinutes(65)->format('H:i:59');

        $bookings = Booking::with(['customer', 'employee.user', 'vendor.user'])
            ->where('booking_date', $now->toDateString())
            ->whereBetween('slot_start_time', [$start, $end])
            ->where('status', 'confirmed')
            ->get();

        foreach ($bookings as $booking) {
            $message = "Reminder: Your appointment with {$booking->employee->name} at {$booking->vendor->business_name} starts at {$booking->slot_start_time}.";
            
            // Notify Customer if they exist and have FCM token
            if ($booking->customer && $booking->customer->fcm_token) {
                $notificationService->sendWebPush(
                    $booking->customer,
                    "⏰ Appointment Reminder",
                    $message,
                    ['booking_id' => $booking->id]
                );
            }

            // Notify Employee if they exist and have FCM token
            if ($booking->employee && $booking->employee->user && $booking->employee->user->fcm_token) {
                $notificationService->sendWebPush(
                    $booking->employee->user,
                    "⏰ Upcoming Appointment",
                    "Reminder: You have an appointment with {$booking->customer_name} starting at {$booking->slot_start_time}.",
                    ['booking_id' => $booking->id]
                );
            }
        }

        $this->info("Sent {$bookings->count()} appointment reminders.");
    }
}
