<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Vendor;
use App\Services\ShiftService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Keep `is_open` in step with each vendor's operating hours.
|
| The comparison used to be a plain string range (now >= open && now <= close),
| which is false at every hour of the day for a midnight-crossing window like
| 22:00 → 02:00 — so overnight shops were force-closed a minute after they
| opened and could never take a booking. ShiftService resolves the window
| properly, wrap and all.
*/
Schedule::call(function (ShiftService $shifts) {
    Vendor::where('status', 'active')
        ->whereNotNull('global_opening_time')
        ->whereNotNull('global_closing_time')
        ->get()
        ->each(function ($vendor) use ($shifts) {
            $isOpen = $shifts->isWithinOperatingHours($vendor);

            if ($vendor->is_open !== $isOpen) {
                $vendor->updateQuietly(['is_open' => $isOpen]);
            }
        });
})->everyMinute();

Schedule::command('app:send-appointment-reminders')->everyTenMinutes();

/*
| Closed shop = clean queue. Runs every minute rather than once at midnight:
| a shop that shuts at 8 PM should not carry "now serving #14" until the small
| hours, and a shop trading through midnight must NOT be reset mid-service.
| Both follow from resetting on shift end instead of on calendar rollover.
*/
Schedule::command('booking:reset-daily')->everyMinute()->withoutOverlapping();
