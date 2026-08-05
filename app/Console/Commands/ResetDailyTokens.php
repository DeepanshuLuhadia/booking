<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Vendor;
use App\Services\BookingNotifier;
use App\Services\ShiftService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

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
    protected $description = 'Return every closed shop to a clean queue: tokens back to 0, pauses cleared, leftover bookings expired';

    /**
     * Execute the console command.
     *
     * Runs continuously (every minute) rather than once at midnight. Midnight
     * is the wrong moment for two reasons: a shop trading 22:00 → 02:00 was
     * having its queue wiped halfway through the night's service, and a shop
     * that shut at 8 PM carried a stale "now serving #14" for four more hours.
     *
     * The rule is simply: a shop that is not currently trading has no queue.
     * Its token counter reads 0, nobody is paused, and any booking left
     * standing on a shift that has already finished is expired. So whenever a
     * shop opens again, everything starts from zero — in both token mode and
     * time-slot mode.
     */
    public function handle(ShiftService $shifts)
    {
        $now = now();

        $tokensReset   = 0;
        $pausesCleared = 0;
        $expired       = 0;
        $shopsReset    = 0;

        // Every business date still holding a live booking, for every vendor,
        // in one query — this command runs each minute, so the per-vendor
        // lookup it replaces would have been 1 query per shop per minute.
        $datesByVendor = Booking::whereIn('status', ['pending', 'confirmed'])
            ->select('vendor_id', 'booking_date')
            ->distinct()
            ->get()
            ->groupBy('vendor_id')
            ->map(fn ($rows) => $rows->pluck('booking_date')->all());

        // Shops carrying queue state worth clearing. Without this the steady
        // state — every shop shut, nothing to do — would still fire two UPDATEs
        // per shop per minute for no rows.
        $dirtyVendorIds = Employee::query()
            ->where(function ($q) {
                $q->where('now_serving_token', '>', 0)->orWhere('is_paused', true);
            })
            ->distinct()
            ->pluck('vendor_id')
            ->flip();

        Vendor::query()->chunkById(100, function ($vendors) use (
            $shifts, $now, $datesByVendor, $dirtyVendorIds, &$tokensReset, &$pausesCleared, &$expired, &$shopsReset
        ) {
            foreach ($vendors as $vendor) {
                // Leftovers are expired for open and closed shops alike — an
                // open shop can still be carrying yesterday's no-shows.
                $expired += $this->expireFinishedShifts(
                    $vendor,
                    $datesByVendor->get($vendor->id, []),
                    $shifts,
                    $now
                );

                if (!$shifts->isShiftOver($vendor, $now)) {
                    continue; // Mid-shift (or inside the post-close grace): leave the queue alone.
                }

                $shopReset = false;

                if ($dirtyVendorIds->has($vendor->id)) {
                    // Token counters back to their default.
                    $dirtyTokens = Employee::where('vendor_id', $vendor->id)
                        ->where('now_serving_token', '>', 0)
                        ->update(['now_serving_token' => 0]);

                    if ($dirtyTokens) {
                        $tokensReset += $dirtyTokens;
                        $shopReset = true;
                    }

                    // A pause is a "not right now" for the shift it was set in;
                    // it must not silently carry into the next opening.
                    $dirtyPauses = Employee::where('vendor_id', $vendor->id)
                        ->where('is_paused', true)
                        ->update(['is_paused' => false]);

                    if ($dirtyPauses) {
                        $pausesCleared += $dirtyPauses;
                        $shopReset = true;
                    }
                }

                if ($vendor->bookings_paused) {
                    $vendor->updateQuietly(['bookings_paused' => false]);
                    $shopReset = true;
                }

                if ($shopReset) {
                    $shopsReset++;

                    // Counters back at zero and pauses cleared: any page still
                    // open on this shop is showing last night's queue until it
                    // hears about it.
                    app(BookingNotifier::class)->shopStatusChanged($vendor, 'reset');
                }
            }
        });

        if ($tokensReset || $expired || $pausesCleared) {
            // The listing caches a 60-second candidate set that carries the
            // live queue count with it; drop it so the reset shows immediately.
            Cache::forget('default_discovery_candidates');
        }

        $this->info("Queues reset for {$shopsReset} closed shop(s): {$tokensReset} token counter(s), {$pausesCleared} pause flag(s).");
        $this->info("Expired {$expired} booking(s) left over from finished shifts.");

        return self::SUCCESS;
    }

    /**
     * Expire bookings still sitting on a shift whose window has already closed.
     *
     * $dates are this vendor's business dates that still hold a live booking,
     * pre-fetched by the caller. Each is resolved through the vendor's own
     * hours, so an overnight sheet is only expired once its night has actually
     * finished — never at midnight, halfway through it.
     */
    private function expireFinishedShifts(Vendor $vendor, array $dates, ShiftService $shifts, $now): int
    {
        if (empty($dates)) {
            return 0;
        }

        $finished = collect($dates)
            ->map(fn ($date) => \Carbon\Carbon::parse($date)->toDateString())
            ->filter(function ($date) use ($vendor, $shifts, $now) {
                $end = $shifts->shiftEndFor($vendor, $date)
                    ->addMinutes(ShiftService::CLOSE_GRACE_MINUTES);

                return $now->gt($end);
            })
            ->values();

        if ($finished->isEmpty()) {
            return 0;
        }

        // Read the rows before expiring them: once the UPDATE lands there is no
        // way to tell which bookings were affected, and these customers are
        // owed a notification — their appointment ended without ever happening.
        $bookings = Booking::where('vendor_id', $vendor->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereIn('booking_date', $finished->all())
            ->with(['employee.vendor', 'vendor'])
            ->get();

        if ($bookings->isEmpty()) {
            return 0;
        }

        Booking::whereIn('id', $bookings->pluck('id'))->update(['status' => 'expired']);

        // Re-read so the broadcast payload carries 'expired', not the stale status.
        $bookings->each->refresh();

        app(BookingNotifier::class)->expired($bookings);

        return $bookings->count();
    }
}
