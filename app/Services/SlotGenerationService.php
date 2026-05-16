<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Booking;
use Carbon\Carbon;

class SlotGenerationService
{
    /**
     * Generate available slots for an employee for a specific date (default: today).
     */
    public function generateSlots(Employee $employee, $date = null)
    {
        $date = $date ?: Carbon::today()->toDateString();
        $vendor = $employee->vendor;
        
        // Base start/end on employee working times, using the target date
        $startTime = Carbon::parse("$date " . $employee->working_start_time);
        $endTime = Carbon::parse("$date " . $employee->working_end_time);

        if ($endTime->lt($startTime)) {
            $endTime->addDay();
        }

        // Bound by Vendor's Global Times if set
        if ($vendor->global_opening_time) {
            $globalStart = Carbon::parse("$date " . $vendor->global_opening_time);
            $globalEnd = Carbon::parse("$date " . $vendor->global_closing_time);

            if ($globalEnd->lt($globalStart)) {
                $globalEnd->addDay();
            }

            if ($startTime->lt($globalStart)) $startTime = $globalStart->copy();
            if ($endTime->gt($globalEnd)) $endTime = $globalEnd->copy();
        }

        $duration = $employee->slot_duration;

        $slots = [];
        $current = $startTime->copy();

        // Get existing bookings for this employee on this date
        $bookedSlots = Booking::where('employee_id', $employee->id)
            ->where('booking_date', $date)
            ->where('status', 'confirmed')
            ->pluck('slot_start_time')
            ->map(fn($time) => Carbon::parse($time)->format('H:i'))
            ->toArray();

        $now = Carbon::now();
        $startTimeDateTime = $startTime->copy();
        
        if (Carbon::today()->isSameDay($date)) {
            $endTimeLimit = $now->copy()->addHours(4);
        } else {
            $endTimeLimit = $startTimeDateTime->copy()->addHours(4);
        }

        while ($current->copy()->addMinutes($duration)->lte($endTime)) {
            $slotStart = $current->format('H:i');
            $slotEnd = $current->copy()->addMinutes($duration)->format('H:i');
            
            // Current handles exact datetime, maintaining accuracy across midnight
            $slotDateTime = $current->copy();
            
            // Limit to 4 hours window if applicable
            if ($slotDateTime->isAfter($endTimeLimit)) {
                break;
            }
            
            $isPast = $slotDateTime->isPast();
            
            if (!$isPast) {
                $isBooked = in_array($slotStart, $bookedSlots);

                $slots[] = [
                    'start' => $slotStart,
                    'end' => $slotEnd,
                    'available' => !$isBooked,
                    'is_booked' => $isBooked,
                ];
            }

            $current->addMinutes($duration);
        }

        // Priority/Premium slot logic (unified concept)
        // If employee configured premium_bookings_count > 0, use that.
        // Otherwise default to 2 hours worth of slots.
        $configuredCount = $employee->premium_bookings_count;
        $maxPremium = ($configuredCount && $configuredCount > 0)
            ? $configuredCount
            : (int) floor(120 / $duration);

        $premiumCount = 0;
        foreach ($slots as &$slot) {
            if ($slot['available'] && $premiumCount < $maxPremium) {
                $slot['is_premium'] = true;
                $slot['premium_fee_amount'] = (float) ($employee->premium_fee ?? 0);
                $premiumCount++;
            } else {
                $slot['is_premium'] = false;
                $slot['premium_fee_amount'] = 0;
            }
        }
        unset($slot);

        return $slots;
    }
}
