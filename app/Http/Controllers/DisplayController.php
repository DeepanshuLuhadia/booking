<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Vendor;
use App\Services\QueueVelocityService;
use App\Services\ShiftService;
use App\Services\ThemeService;

/**
 * Public, unauthenticated "screen display" pages — meant to be left open on a
 * shop's own TV or monitor so a walk-in guest can read the queue without
 * asking anyone. One for the whole roster (a slider) and one for a single
 * specialist, plus the JSON each polls/refreshes from.
 *
 * Carries the same information as the public queue channels (QueueUpdated,
 * ShopStatusChanged) and nothing more — no customer name or phone ever
 * belongs on a screen anybody walking past can read.
 */
class DisplayController extends Controller
{
    public function __construct(
        private ShiftService $shifts,
        private QueueVelocityService $velocity
    ) {
    }

    public function vendor(Vendor $vendor)
    {
        $theme = ThemeService::getTheme($vendor->category?->slug ?? $vendor->vendor_type ?? 'consultant');
        $employees = $this->displayableEmployees($vendor);

        return view('customer.vendor-display', [
            'vendor'    => $vendor,
            'theme'     => $theme,
            'panels'    => $employees->map(fn ($employee) => $this->panelFor($vendor, $employee))->values(),
        ]);
    }

    public function vendorData(Vendor $vendor)
    {
        $employees = $this->displayableEmployees($vendor);

        return response()->json([
            'vendor'    => $this->vendorState($vendor),
            'employees' => $employees->map(fn ($employee) => $this->panelFor($vendor, $employee))->values(),
        ]);
    }

    public function employee($identifier)
    {
        $employee = $this->resolveEmployee($identifier);
        $vendor   = $employee->vendor;
        $theme    = ThemeService::getTheme($vendor->category?->slug ?? $vendor->vendor_type ?? 'consultant');

        return view('customer.employee-display', [
            'vendor'   => $vendor,
            'employee' => $employee,
            'theme'    => $theme,
            'panel'    => $this->panelFor($vendor, $employee),
        ]);
    }

    public function employeeData($identifier)
    {
        $employee = $this->resolveEmployee($identifier);

        return response()->json([
            'vendor'   => $this->vendorState($employee->vendor),
            'employee' => $this->panelFor($employee->vendor, $employee),
        ]);
    }

    private function resolveEmployee($identifier): Employee
    {
        return Employee::where('slug', $identifier)
            ->orWhere('id', $identifier)
            ->with(['vendor.category'])
            ->firstOrFail();
    }

    private function displayableEmployees(Vendor $vendor)
    {
        return $vendor->employees()->where('is_active', true)->orderBy('name')->get();
    }

    /**
     * Everything one specialist's display tile needs, computed fresh from the
     * live queue — never a customer name or phone, since this is read by
     * whoever is standing in front of the screen.
     */
    private function panelFor(Vendor $vendor, Employee $employee): array
    {
        $mode = $vendor->appointment_mode ?? 'time_slot';

        $serving = $this->velocity->servingState($employee);
        $waiting = $this->velocity->waitingTokens($employee);

        $nextAppointment = null;
        if (in_array($mode, ['time_slot', 'hybrid'], true)) {
            $upcoming = Booking::where('employee_id', $employee->id)
                ->whereIn('status', ['confirmed', 'pending'])
                ->whereIn('booking_date', $this->shifts->liveBusinessDates())
                ->whereNotNull('slot_start_time')
                ->get()
                ->map(fn ($booking) => $booking->appointment_at)
                ->filter(fn ($at) => $at && $at->isFuture())
                ->sort()
                ->first();

            if ($upcoming) {
                $nextAppointment = [
                    'time'       => $upcoming->format('h:i A'),
                    'date_label' => $upcoming->isToday() ? 'Today' : $upcoming->format('M d'),
                ];
            }
        }

        return [
            'id'               => $employee->id,
            'name'             => $employee->name,
            'photo_url'        => $employee->photo ? asset('storage/' . $employee->photo) : null,
            'is_active'        => (bool) $employee->is_active,
            'is_paused'        => (bool) $employee->is_paused,
            'mode'             => $mode,
            'now_serving'      => $serving['now_serving'],
            'is_serving'       => $serving['is_serving'],
            'serving_label'    => $serving['serving_label'],
            'serving_display'  => $serving['serving_display'],
            'waiting_count'    => count($waiting),
            'next_appointment' => $nextAppointment,
        ];
    }

    private function vendorState(Vendor $vendor): array
    {
        return [
            'id'              => $vendor->id,
            'business_name'   => $vendor->business_name,
            'is_open'         => (bool) $vendor->is_currently_open,
            'bookings_paused' => (bool) $vendor->bookings_paused,
        ];
    }
}
