<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetDailyTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:reset-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset vendor tokens and expire uncompleted bookings daily';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
        $nowTime = $now->format('H:i:s');

        $this->info('Resetting daily queue counters...');

        // Reset token counter for ALL employees (new day = fresh queue)
        \App\Models\Employee::query()->update(['now_serving_token' => 0]);
        $this->info('Employee token counters reset to 0.');

        // Only reset bookings_paused for vendors NOT currently in their operating window.
        // A vendor open 11 PM → 2 AM should NOT have their pause flag cleared mid-shift.
        $vendorsToClearPause = \App\Models\Vendor::whereNotNull('global_opening_time')
            ->whereNotNull('global_closing_time')
            ->get()
            ->filter(function ($vendor) use ($nowTime) {
                $open  = $vendor->global_opening_time;
                $close = $vendor->global_closing_time;
                // Is vendor currently in their operating window?
                if ($open < $close) {
                    $inWindow = ($nowTime >= $open && $nowTime <= $close);
                } else {
                    // Midnight-crossing window (e.g. 22:00 → 02:00)
                    $inWindow = ($nowTime >= $open || $nowTime <= $close);
                }
                // Only clear pause if they are NOT currently in an active window
                return !$inWindow;
            })
            ->pluck('id');

        if ($vendorsToClearPause->isNotEmpty()) {
            \App\Models\Vendor::whereIn('id', $vendorsToClearPause)
                ->update(['bookings_paused' => false]);
            $this->info("Cleared pause state for {$vendorsToClearPause->count()} vendors outside their operating hours.");
        }

        // NOTE: is_open is intentionally NOT reset here.
        // It is the vendor's own intent flag (Open/Pause/Close buttons on dashboard).
        // A vendor working a night shift (e.g. 10 PM → 3 AM) should remain is_open=true
        // across midnight. Vendors must explicitly close their shop via the dashboard.

        $this->info('Expiring uncompleted old bookings...');
        $affected = \App\Models\Booking::where('booking_date', '<', $now->toDateString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->update(['status' => 'expired']);

        $this->info("Expired {$affected} bookings.");
        $this->info('Daily reset complete.');
    }
}
