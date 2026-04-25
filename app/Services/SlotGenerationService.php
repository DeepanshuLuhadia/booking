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
        
        // Base start/end on employee working times
        $startTime = Carbon::parse($employee->working_start_time);
        $endTime = Carbon::parse($employee->working_end_time);

        // Bound by Vendor's Global Times if set
        if ($vendor->global_opening_time) {
            $globalStart = Carbon::parse($vendor->global_opening_time);
            if ($startTime->lt($globalStart)) $startTime = $globalStart;
        }
        if ($vendor->global_closing_time) {
            $globalEnd = Carbon::parse($vendor->global_closing_time);
            if ($endTime->gt($globalEnd)) $endTime = $globalEnd;
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
        $startTimeDateTime = Carbon::parse("$date " . $startTime->format('H:i'));
        
        if (Carbon::today()->isSameDay($date)) {
            $endTimeLimit = $now->copy()->addHours(4);
        } else {
            $endTimeLimit = $startTimeDateTime->copy()->addHours(4);
        }

        while ($current->copy()->addMinutes($duration)->lte($endTime)) {
            $slotStart = $current->format('H:i');
            $slotEnd = $current->copy()->addMinutes($duration)->format('H:i');
            $slotDateTime = Carbon::parse("$date $slotStart");
            
            // Limit to 4 hours window if applicable
            if ($slotDateTime->isAfter($endTimeLimit)) {
                break;
            }
            
            $isPast = $slotDateTime->isPast();
            
            if (!$isPast) {
                $isBooked = in_array($slotStart, $bookedSlots);
                $isWithinAdvanceWindow = Carbon::parse("$date $slotStart")->subHours(2)->isPast();

                $slots[] = [
                    'start' => $slotStart,
                    'end' => $slotEnd,
                    'available' => !$isBooked && !$isWithinAdvanceWindow,
                    'is_booked' => $isBooked,
                    'requires_emergency' => !$isBooked && $isWithinAdvanceWindow
                ];
            }

            $current->addMinutes($duration);
        }

        return $slots;
    }
}
