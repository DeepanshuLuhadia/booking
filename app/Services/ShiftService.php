<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Vendor;
use Carbon\Carbon;

/**
 * One place that answers "which trading day is it, and is the shop open?".
 *
 * Every queue, slot and booking in the system is grouped by a *business date*
 * rather than a calendar date. The two only agree for shops that close before
 * midnight: a shop trading 22:00 → 02:00 is still working its Monday shift at
 * 00:30 on Tuesday, and its tokens, slots and bookings all belong to Monday.
 *
 * The resolution logic used to be copy-pasted (with drift) across two
 * controllers while the write paths — booking creation, the daily reset, the
 * dashboards — used Carbon::today() instead, which is what made overnight
 * shifts reset their token counter at midnight and file after-midnight
 * appointments under the previous day.
 */
class ShiftService
{
    /**
     * Minutes a shift keeps its queue alive after closing time, so the last
     * customer in the chair isn't expired out from under the vendor.
     */
    public const CLOSE_GRACE_MINUTES = 10;

    /**
     * Resolve the shift window that $now falls in for a start/end time pair.
     *
     * Returns [shiftDate, start, end] where shiftDate is the calendar date the
     * shift *started* on. When $now sits before the start time, yesterday's
     * window is tried first — that is what keeps an overnight shift whole.
     */
    public function resolveShift(Carbon $now, string $startTime, string $endTime): array
    {
        $shiftDate = $now->toDateString();
        $start     = Carbon::parse("$shiftDate $startTime");
        $end       = Carbon::parse("$shiftDate $endTime");

        if ($end->lte($start)) {
            $end->addDay();
        }

        if ($now->lt($start)) {
            $yDate  = $now->copy()->subDay()->toDateString();
            $yStart = Carbon::parse("$yDate $startTime");
            $yEnd   = Carbon::parse("$yDate $endTime");

            if ($yEnd->lte($yStart)) {
                $yEnd->addDay();
            }

            if ($now->lte($yEnd)) {
                $shiftDate = $yDate;
                $start     = $yStart;
                $end       = $yEnd;
            }
        }

        return [$shiftDate, $start, $end];
    }

    /**
     * The vendor's current (or next upcoming) shift as [date, open, close].
     * Null when the vendor has no operating hours on file.
     */
    public function vendorShift(Vendor $vendor, ?Carbon $now = null): ?array
    {
        if (!$vendor->global_opening_time || !$vendor->global_closing_time) {
            return null;
        }

        return $this->resolveShift(
            $now ? $now->copy() : Carbon::now(),
            $vendor->global_opening_time,
            $vendor->global_closing_time
        );
    }

    /**
     * The date bookings, tokens and slots are filed under right now.
     *
     * This is the value that belongs in `bookings.booking_date` — never
     * Carbon::today(), which splits an overnight shift in half at midnight.
     */
    public function businessDate(?Vendor $vendor, ?Carbon $now = null): string
    {
        $now = $now ? $now->copy() : Carbon::now();

        if (!$vendor) {
            return $now->toDateString();
        }

        $shift = $this->vendorShift($vendor, $now);

        return $shift ? $shift[0] : $now->toDateString();
    }

    /**
     * Business dates that may still hold a live booking — today plus the
     * overnight shift that started yesterday. Used by the queries that cannot
     * resolve a single vendor's shift (cross-vendor listings).
     */
    public function liveBusinessDates(?Carbon $now = null): array
    {
        $now = $now ? $now->copy() : Carbon::now();

        return [
            $now->copy()->subDay()->toDateString(),
            $now->toDateString(),
        ];
    }

    /**
     * When the shift that started on $date finishes. Vendors with no hours on
     * file fall back to the end of that calendar day.
     */
    public function shiftEndFor(Vendor $vendor, string $date): Carbon
    {
        if (!$vendor->global_opening_time || !$vendor->global_closing_time) {
            return Carbon::parse($date)->endOfDay();
        }

        $start = Carbon::parse("$date " . $vendor->global_opening_time);
        $end   = Carbon::parse("$date " . $vendor->global_closing_time);

        if ($end->lte($start)) {
            $end->addDay();
        }

        return $end;
    }

    /**
     * Is the vendor inside its operating window right now? Purely time-based —
     * it says nothing about the vendor's own open/paused intent flags.
     */
    public function isWithinOperatingHours(Vendor $vendor, ?Carbon $now = null): bool
    {
        $now   = $now ? $now->copy() : Carbon::now();
        $shift = $this->vendorShift($vendor, $now);

        if (!$shift) {
            return false;
        }

        [, $open, $close] = $shift;

        return $now->gte($open) && $now->lte($close);
    }

    /**
     * Has the shift finished, including the post-close grace period? Drives the
     * queue reset, so a shop that has just shut still shows its last token for
     * a few minutes rather than blanking mid-service.
     */
    public function isShiftOver(Vendor $vendor, ?Carbon $now = null): bool
    {
        $now   = $now ? $now->copy() : Carbon::now();
        $shift = $this->vendorShift($vendor, $now);

        if (!$shift) {
            return true; // No hours configured: nothing is trading.
        }

        [, $open, $close] = $shift;

        return $now->lt($open)
            || $now->gte($close->copy()->addMinutes(self::CLOSE_GRACE_MINUTES));
    }

    /**
     * An employee's working window for the shift containing $now, clamped to
     * the vendor's own opening hours. Returns [shiftDate, start, end], or null
     * when the employee has no hours or the clamp leaves no usable window.
     */
    public function employeeShift(Employee $employee, ?Vendor $vendor = null, ?Carbon $now = null): ?array
    {
        if (!$employee->working_start_time || !$employee->working_end_time) {
            return null;
        }

        $now    = $now ? $now->copy() : Carbon::now();
        $vendor = $vendor ?? $employee->vendor;

        [$shiftDate, $start, $end] = $this->resolveShift(
            $now,
            $employee->working_start_time,
            $employee->working_end_time
        );

        if ($vendor) {
            [$start, $end] = $this->clampToVendorWindow($shiftDate, $start, $end, $vendor);
        }

        return $start->lt($end) ? [$shiftDate, $start, $end] : null;
    }

    /**
     * Clamp an employee's start/end times inside the vendor's global window.
     */
    public function clampToVendorWindow(string $shiftDate, Carbon $empStart, Carbon $empEnd, Vendor $vendor): array
    {
        if (!$vendor->global_opening_time || !$vendor->global_closing_time) {
            return [$empStart, $empEnd];
        }

        $vStart = Carbon::parse("$shiftDate " . $vendor->global_opening_time);
        $vEnd   = Carbon::parse("$shiftDate " . $vendor->global_closing_time);

        if ($vEnd->lte($vStart)) {
            $vEnd->addDay();
        }

        if ($empStart->lt($vStart)) {
            $empStart = $vStart->copy();
        }

        if ($empEnd->gt($vEnd)) {
            $empEnd = $vEnd->copy();
        }

        return [$empStart, $empEnd];
    }
}
