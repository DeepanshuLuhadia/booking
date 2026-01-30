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
        $startTime = Carbon::parse($employee->working_start_time);
        $endTime = Carbon::parse($employee->working_end_time);
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

        while ($current->copy()->addMinutes($duration)->lte($endTime)) {
            $slotStart = $current->format('H:i');
            $slotEnd = $current->copy()->addMinutes($duration)->format('H:i');
            
            $isPast = Carbon::parse("$date $slotStart")->isPast();
            
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
