<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Vendor;
use Carbon\Carbon;

class QueueVelocityService
{
    /**
     * Calculate dynamic estimated wait time in minutes for a token position.
     */
    public function calculateEstimatedWait(Vendor $vendor, Employee $employee, int $tokenNumber): int
    {
        $nowServing = $employee->now_serving_token ?? 0;
        $peopleAhead = max(0, $tokenNumber - $nowServing);
        if ($peopleAhead === 0) {
            return 0;
        }

        // Base average consultation time from vendor config (default: 15 mins)
        $basePace = (float) ($vendor->avg_consultation_time ?? 15);
        if ($basePace <= 0) {
            $basePace = 15;
        }

        // Calculate today's real velocity if at least 2 tokens were completed today
        $completedToday = Booking::where('employee_id', $employee->id)
            ->where('booking_date', Carbon::today()->toDateString())
            ->where('status', 'completed')
            ->whereNotNull('updated_at')
            ->orderBy('updated_at', 'asc')
            ->get();

        if ($completedToday->count() >= 2) {
            $firstTime = $completedToday->first()->updated_at;
            $lastTime  = $completedToday->last()->updated_at;
            $diffMins  = $firstTime->diffInMinutes($lastTime);
            if ($diffMins > 0) {
                $realPace = $diffMins / ($completedToday->count() - 1);
                // Clamp real pace within sane boundaries (5 mins to 60 mins)
                $basePace = max(5, min(60, $realPace));
            }
        }

        // Peak hours multiplier (5 PM - 8 PM peak traffic factor: 1.2x)
        $currentHour = (int) Carbon::now()->format('H');
        $multiplier = ($currentHour >= 17 && $currentHour <= 20) ? 1.2 : 1.0;

        return (int) round($peopleAhead * $basePace * $multiplier);
    }
}
