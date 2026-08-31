<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Services\NotificationService;
use App\Services\ShiftService;
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
     *
     * The window is matched against the appointment's real datetime, not
     * against booking_date + a time-of-day range. Those two part company for
     * after-midnight slots: a 00:30 appointment on an overnight shift is filed
     * under the previous day's business date, so the old date-plus-time match
     * never found it and the reminder was silently skipped.
     */
    public function handle(NotificationService $notificationService, ShiftService $shifts)
    {
        $now  = Carbon::now();
        $from = $now->copy()->addMinutes(55);
        $to   = $now->copy()->addMinutes(65);

        // Candidates are narrowed by business date first (cheap, indexed), then
        // filtered on the resolved appointment time. The neighbouring dates are
        // included because an overnight shift's slots straddle two of them.
        $candidates = Booking::with(['customer', 'employee.user', 'vendor.user'])
            ->whereIn('booking_date', array_merge(
                $shifts->liveBusinessDates($now),
                [$now->copy()->addDay()->toDateString()]
            ))
            ->where('status', 'confirmed')
            ->get();

        $bookings = $candidates->filter(function ($booking) use ($from, $to) {
            $startsAt = $booking->appointment_at;

            return $startsAt && $startsAt->betweenIncluded($from, $to);
        });

        foreach ($bookings as $booking) {
            $startsAt = $booking->appointment_at->format('h:i A');
            $message  = "Reminder: Your appointment with {$booking->employee->name} at {$booking->vendor->business_name} starts at {$startsAt}.";

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
                    "Reminder: You have an appointment with {$booking->customer_name} starting at {$startsAt}.",
                    ['booking_id' => $booking->id]
                );
            }
        }

        $this->info("Sent {$bookings->count()} appointment reminders.");

        return self::SUCCESS;
    }
}
