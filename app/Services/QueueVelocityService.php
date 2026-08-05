<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Vendor;
use Carbon\Carbon;

class QueueVelocityService
{
    /** Pace is clamped into this range — a stray gap must not produce silly ETAs. */
    private const MIN_PACE_MINUTES = 5;
    private const MAX_PACE_MINUTES = 60;

    /**
     * How many people are genuinely still in front of this token.
     *
     * Counted, never derived from `token_number - now_serving_token`. That
     * subtraction treats every lower number as somebody still waiting, which is
     * wrong the moment anything leaves the queue: complete token #9 and the
     * holder of #10 was still told one person was ahead of them, because
     * now_serving_token records the last token *handled*, not one still to come.
     * Cancelled and skipped tokens were miscounted the same way.
     *
     * Counting live bookings below the token is the only definition that stays
     * true as the queue churns.
     */
    public function peopleAheadOf(Employee $employee, int $tokenNumber): int
    {
        if ($tokenNumber < 1) {
            return 0;
        }

        return Booking::where('employee_id', $employee->id)
            ->where('booking_date', app(ShiftService::class)->businessDate($employee->vendor))
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereNotNull('token_number')
            ->where('token_number', '<', $tokenNumber)
            ->count();
    }

    /**
     * The tokens still waiting for this specialist, in order.
     *
     * Broadcast to customer screens so each one can work out its own position
     * without the server having to compute a per-viewer answer. Bare integers —
     * nothing here identifies anybody.
     */
    public function waitingTokens(Employee $employee): array
    {
        return Booking::where('employee_id', $employee->id)
            ->where('booking_date', app(ShiftService::class)->businessDate($employee->vendor))
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereNotNull('token_number')
            ->orderBy('token_number')
            ->pluck('token_number')
            ->map(fn ($t) => (int) $t)
            ->all();
    }

    /**
     * What the counter is actually doing right now.
     *
     * `now_serving_token` is overloaded: pressing "next token" sets it to the
     * customer being called, but completing an appointment also sets it to the
     * one just finished. Read on its own it cannot tell those apart, which is
     * why a screen still read "Now Serving #9" after #9 had walked out.
     *
     * The booking behind that number settles it — still live means they are in
     * the chair; closed out means the counter is between customers, and the
     * useful thing to show a waiting customer is who is up next.
     *
     * Returns a label and a display string so every screen words it identically.
     */
    public function servingState(Employee $employee): array
    {
        $nowServing = (int) ($employee->now_serving_token ?? 0);

        $inChair = $nowServing > 0 && Booking::where('employee_id', $employee->id)
            ->where('booking_date', app(ShiftService::class)->businessDate($employee->vendor))
            ->where('token_number', $nowServing)
            ->whereIn('status', ['confirmed', 'pending'])
            ->exists();

        $waiting   = $this->waitingTokens($employee);
        $nextToken = $waiting[0] ?? null;

        return [
            'now_serving'     => $nowServing,
            'is_serving'      => $inChair,
            'next_token'      => $nextToken,
            'serving_label'   => $inChair ? 'Now Serving' : 'Up Next',
            'serving_display' => $inChair
                ? '#' . $nowServing
                : ($nextToken ? '#' . $nextToken : '—'),
        ];
    }

    /**
     * Calculate dynamic estimated wait time in minutes for a token position.
     */
    public function calculateEstimatedWait(Vendor $vendor, Employee $employee, int $tokenNumber): int
    {
        $peopleAhead = $this->peopleAheadOf($employee, $tokenNumber);

        if ($peopleAhead === 0) {
            return 0;
        }

        $basePace = $this->paceMinutes($vendor, $employee);

        // Peak hours multiplier (5 PM - 8 PM peak traffic factor: 1.2x)
        $currentHour = (int) Carbon::now()->format('H');
        $multiplier = ($currentHour >= 17 && $currentHour <= 20) ? 1.2 : 1.0;

        return (int) round($peopleAhead * $basePace * $multiplier);
    }

    /**
     * Minutes per customer, measured from how fast this specialist is actually
     * working today.
     *
     * Taken as the MEDIAN gap between consecutive completions, not the average
     * across the whole shift. The old measurement divided the span from the
     * first completion to the last by the number served, so a lunch break or a
     * quiet morning was spread across every customer and pushed the pace
     * straight into its 60-minute ceiling — which is how a queue of one came
     * back as a 72-minute wait. A median ignores those gaps instead of being
     * dominated by them.
     */
    private function paceMinutes(Vendor $vendor, Employee $employee): float
    {
        $fallback = (float) ($vendor->avg_consultation_time ?: 15);
        if ($fallback <= 0) {
            $fallback = 15.0;
        }

        $completed = Booking::where('employee_id', $employee->id)
            ->where('booking_date', app(ShiftService::class)->businessDate($vendor))
            ->where('status', 'completed')
            ->whereNotNull('updated_at')
            ->orderBy('updated_at')
            ->pluck('updated_at');

        if ($completed->count() < 2) {
            return $fallback;
        }

        $gaps = [];
        for ($i = 1; $i < $completed->count(); $i++) {
            $gap = $completed[$i - 1]->diffInMinutes($completed[$i]);
            if ($gap > 0) {
                $gaps[] = $gap;
            }
        }

        if (!$gaps) {
            return $fallback;
        }

        sort($gaps);
        $mid    = (int) floor(count($gaps) / 2);
        $median = count($gaps) % 2
            ? $gaps[$mid]
            : ($gaps[$mid - 1] + $gaps[$mid]) / 2;

        return max(self::MIN_PACE_MINUTES, min(self::MAX_PACE_MINUTES, (float) $median));
    }
}
