<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Employee;
use App\Models\Booking;
use App\Services\SlotGenerationService;
use App\Services\ThemeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CustomerDiscoveryController extends Controller
{
public function index(Request $request)
{
    $search        = trim($request->search ?? '');
    $specialty     = trim($request->specialty ?? '');
    $location      = trim($request->location ?? '');
    $sort          = $request->sort;
    $filterType    = $request->type;
    $filterOpen    = $request->filter === 'open_now';
    $now           = now();
    $currentTime   = $now->format('H:i:s');
    $allThemes     = Cache::remember('all_themes', 3600, fn() => ThemeService::getAllThemes());

    /*
    |--------------------------------------------------------------------------
    | Base Query
    |--------------------------------------------------------------------------
    |
    | Rules:
    | - Vendor must be active (Status Approved)
    | - Profile must be complete (Minimum Data Required)
    |
    */

    $query = Vendor::query()
        ->where('status', 'active')
        ->where('is_profile_complete', true)
        ->where('is_open', true)
        ->whereNotNull('global_opening_time')
        ->whereNotNull('global_closing_time')
        ->with([
            'category',
            'employees' => function ($q) {
                $q->where('is_active', true)
                    ->whereNotNull('service_fee_override')
                    ->where('service_fee_override', '>', 0)
                    ->whereNotNull('working_start_time')
                    ->whereNotNull('working_end_time');
            }
        ])
        ->withMin(['employees as starting_fee' => function ($q) {
            $q->where('is_active', true)
                ->where('service_fee_override', '>', 0)
                ->whereNotNull('working_start_time')
                ->whereNotNull('working_end_time');
        }], 'service_fee_override')

        /*
        |--------------------------------------------------------------------------
        | Vendor MUST have at least one valid employee
        |--------------------------------------------------------------------------
        */
        ->whereHas('employees', function ($q) {
            $q->where('is_active', true)
                ->whereNotNull('service_fee_override')
                ->where('service_fee_override', '>', 0)
                ->whereNotNull('working_start_time')
                ->whereNotNull('working_end_time');
        });

    /*
    |--------------------------------------------------------------------------
    | Category Filter (Sidebar/Pills)
    |--------------------------------------------------------------------------
    |*/

    if ($filterType && array_key_exists($filterType, $allThemes)) {
        $query->whereHas('category', function ($q) use ($filterType) {
            $q->where('slug', $filterType);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Search Filter (Matrix Search)
    |--------------------------------------------------------------------------
    */

    if ($search || $specialty || $location) {
        $query->where(function ($q) use ($search, $specialty, $location) {
            if ($search) {
                $q->where(function($q2) use ($search) {
                    $q2->where('business_name', 'LIKE', "%{$search}%")
                       ->orWhere('owner_name', 'LIKE', "%{$search}%")
                       ->orWhere('address', 'LIKE', "%{$search}%");
                });
            }

            if ($specialty) {
                $q->where(function($q2) use ($specialty) {
                    $q2->where('vendor_type', 'LIKE', "%{$specialty}%")
                       ->orWhereHas('category', function($q3) use ($specialty) {
                           $q3->where('name', 'LIKE', "%{$specialty}%");
                       });
                });
            }

            if ($location) {
                $q->where('address', 'LIKE', "%{$location}%");
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Open Now Filter (Mandatory)
    |--------------------------------------------------------------------------
    |
    | Vendor global timing logic
    | Supports cross-midnight timing
    |
    */

    $query->where(function ($q) use ($currentTime) {

        /*
        |--------------------------------------------------------------------------
        | Normal Shift
        | Example: 09:00 -> 22:00
        |--------------------------------------------------------------------------
        */
        $q->where(function ($q2) use ($currentTime) {

            $q2->whereColumn(
                    'global_opening_time',
                    '<',
                    'global_closing_time'
                )
                ->where('global_opening_time', '<=', $currentTime)
                ->where('global_closing_time', '>=', $currentTime);

        })

        /*
        |--------------------------------------------------------------------------
        | Cross Midnight Shift
        | Example: 20:00 -> 03:00
        |--------------------------------------------------------------------------
        */
        ->orWhere(function ($q2) use ($currentTime) {

            $q2->whereColumn(
                    'global_opening_time',
                    '>=',
                    'global_closing_time'
                )
                ->where(function ($q3) use ($currentTime) {

                    $q3->where('global_opening_time', '<=', $currentTime)
                        ->orWhere(
                            'global_closing_time',
                            '>=',
                            $currentTime
                        );

                });

        });

    });

    /*
    |--------------------------------------------------------------------------
    | Sorting
    |--------------------------------------------------------------------------
    */

    if ($sort === 'newest') {

        $query->latest();

    } elseif ($sort === 'rating') {

        /*
        |--------------------------------------------------------------------------
        | Replace with actual rating column if exists
        |--------------------------------------------------------------------------
        */
        $query->orderByDesc('rating');

    } else {

        /*
        |--------------------------------------------------------------------------
        | Open vendors first
        |--------------------------------------------------------------------------
        */

        $query->orderByRaw(
            "
            (
                (
                    global_opening_time < global_closing_time
                    AND ? BETWEEN global_opening_time AND global_closing_time
                )
                OR
                (
                    global_opening_time >= global_closing_time
                    AND (
                        ? >= global_opening_time
                        OR
                        ? <= global_closing_time
                    )
                )
            ) DESC
            ",
            [$currentTime, $currentTime, $currentTime]
        )
        ->latest();
    }

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $vendors = $query->paginate(24);

    /*
    |--------------------------------------------------------------------------
    | Final Vendor Processing
    |--------------------------------------------------------------------------
    */

    $vendors->getCollection()->transform(function ($vendor) use ($now) {

        /*
        |--------------------------------------------------------------------------
        | Valid Employees (Already filtered in eager load)
        |--------------------------------------------------------------------------
        */

        $validEmployees = $vendor->employees;


        /*
        |--------------------------------------------------------------------------
        | Safety Check
        |--------------------------------------------------------------------------
        */

        if ($validEmployees->isEmpty()) {
            $vendor->is_currently_open = false;
            return $vendor;
        }

        /*
        |--------------------------------------------------------------------------
        | Starting Fee (Already pre-calculated in DB via withMin)
        |--------------------------------------------------------------------------
        */


        /*
        |--------------------------------------------------------------------------
        | Vendor Global Timings
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | Robust Date-Based Shift Processing
        |--------------------------------------------------------------------------
        */

        [$shiftDate, $vOpen, $vClose] = $this->resolveShift($now, $vendor->global_opening_time, $vendor->global_closing_time);

        $isVendorTimeOpen = $now->between($vOpen, $vClose);

        /*
        |--------------------------------------------------------------------------
        | Vendor timing closed
        |--------------------------------------------------------------------------
        */

        if (!$isVendorTimeOpen) {

            $vendor->is_currently_open = false;

            return $vendor;
        }

        /*
        |--------------------------------------------------------------------------
        | Employee Availability Check
        |--------------------------------------------------------------------------
        */

        $hasAvailableEmployee = false;

        foreach ($validEmployees as $employee) {
            
            $empStartDt = \Carbon\Carbon::parse("$shiftDate " . $employee->working_start_time);
            $empEndDt   = \Carbon\Carbon::parse("$shiftDate " . $employee->working_end_time);

            if ($empEndDt->lt($empStartDt)) {
                $empEndDt->addDay();
            }

            /*
            |--------------------------------------------------------------------------
            | Restrict Employee Time Within Vendor Time
            |--------------------------------------------------------------------------
            */

            if ($empStartDt->lt($vOpen)) {
                $empStartDt = $vOpen->copy();
            }

            if ($empEndDt->gt($vClose)) {
                $empEndDt = $vClose->copy();
            }

            /*
            |--------------------------------------------------------------------------
            | Appointment Mode
            |--------------------------------------------------------------------------
            |
            | Vendor appears open 2 hours before shift
            |
            */

            if ($vendor->appointment_mode === 'appointment') {

                $effectiveStart = $empStartDt
                    ->copy()
                    ->subHours(2);

            } else {

                $effectiveStart = $empStartDt;

            }

            $effectiveEnd = $empEndDt;

            $isEmployeeAvailable = $now->between($effectiveStart, $effectiveEnd);

            if ($isEmployeeAvailable) {

                $hasAvailableEmployee = true;
                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Final Open Status
        |--------------------------------------------------------------------------
        */

        $vendor->is_currently_open = $hasAvailableEmployee;

        return $vendor;
    });

    $vendors->setCollection(
        $vendors->getCollection()
            ->where('is_currently_open', true)
            ->values()
    );

    $allThemes = Cache::remember('all_themes', 3600, fn() => ThemeService::getAllThemes());

    return view(
        'customer.vendors',
        compact('vendors', 'allThemes')
    );
}

    public function show(Vendor $vendor, SlotGenerationService $slotService)
    {
        $vendor->load(['employees', 'category']);

        $nowDt = \Carbon\Carbon::now();
        foreach ($vendor->employees as $emp) {
            if (!$emp->is_active) {
                $emp->is_available = false;
                continue;
            }

            [$shiftDate, $empStart, $empEnd] = $this->resolveShift($nowDt, $emp->working_start_time, $emp->working_end_time);

            // Bound by Vendor's Global Times if set
            [$empStart, $empEnd] = $this->clampEmployeeToVendorWindow($shiftDate, $empStart, $empEnd, $vendor);

            if ($vendor->appointment_mode === 'appointment') {
                $effectiveStartDt = $empStart->copy()->subHours(2);
                $emp->is_available = !($nowDt->lt($effectiveStartDt) || $nowDt->gt($empEnd));
            } else {
                $emp->is_available = !($nowDt->lt($empStart) || $nowDt->gt($empEnd));
            }
        }

        $selectedEmployee = $vendor->employees()->where('is_active', true)->first();
        $slots = [];
        $isOffline = false;
        $opensAt = '';

        if ($selectedEmployee) {
            $now = \Carbon\Carbon::now();
            [$shiftDate, $empStart, $empEnd] = $this->resolveShift($now, $selectedEmployee->working_start_time, $selectedEmployee->working_end_time);

            // Global Vendor Constraints
            [$empStart, $empEnd] = $this->clampEmployeeToVendorWindow($shiftDate, $empStart, $empEnd, $vendor);

            if ($vendor->appointment_mode === 'appointment') {
                $windowOpensAt = $empStart->copy()->subHours(2);
                $isOffline = $now->lt($windowOpensAt) || $now->gt($empEnd);
            } else {
                $isOffline = $now->lt($empStart) || $now->gt($empEnd);
            }
            $opensAt = (clone $empStart)->format('h:i A');

            $isPaused = $selectedEmployee->is_paused;

            if (!$isOffline && !$isPaused) {
                $slots = $slotService->generateSlots($selectedEmployee, $shiftDate, $vendor);
            }
        }

        // Resolve theme for this vendor's role
        $theme = Cache::remember('all_themes', 3600, fn() => ThemeService::getAllThemes())[$vendor->category?->slug] ?? ThemeService::getTheme('consultant');

        $queueIndex = 0;
        $runningToken = 0;

        if ($selectedEmployee) {
            $queueIndex = Booking::where('employee_id', $selectedEmployee->id)
                ->where('booking_date', now()->toDateString())
                ->whereNotNull('token_number')
                ->max('token_number') ?? 0;
                
            $runningToken = Booking::where('employee_id', $selectedEmployee->id)
                ->where('booking_date', now()->toDateString())
                ->where('status', 'confirmed')
                ->min('token_number') ?? 0;
        }

        return view('customer.vendor-details', compact('vendor', 'selectedEmployee', 'slots', 'theme', 'isOffline', 'opensAt', 'queueIndex', 'runningToken', 'isPaused'));
    }

    public function getSlots(Vendor $vendor, Employee $employee, SlotGenerationService $slotService)
    {
        try {
            $now = \Carbon\Carbon::now();
            [$shiftDate, $empStart, $empEnd] = $this->resolveShift($now, $employee->working_start_time, $employee->working_end_time);

        // Global Vendor Constraints
        [$empStart, $empEnd] = $this->clampEmployeeToVendorWindow($shiftDate, $empStart, $empEnd, $vendor);

        // Visibility / Window Logic
        if ($vendor->appointment_mode === 'appointment') {
            $windowOpensAt = $empStart->copy()->subHours(2);
            $isOffline = $now->lt($windowOpensAt) || $now->gt($empEnd);
        } else {
            $isOffline = $now->lt($empStart) || $now->gt($empEnd);
        }

        if ($isOffline) {
            return response()->json([
                'offline'  => true,
                'opens_at' => (clone $empStart)->format('h:i A'),
            ]);
        }

        if ($employee->is_paused) {
            return response()->json([
                'offline'  => false,
                'paused'   => true,
                'slots'    => [],
            ]);
        }

        $slots = $slotService->generateSlots($employee, $shiftDate, $vendor);

        // Token System Metadata
        $queueIndex = Booking::where('employee_id', $employee->id)
            ->where('booking_date', now()->toDateString())
            ->whereNotNull('token_number')
            ->max('token_number');
            
        $runningToken = Booking::where('employee_id', $employee->id)
            ->where('booking_date', now()->toDateString())
            ->where('status', 'confirmed')
            ->min('token_number');

            return response()->json([
                'offline' => false,
                'slots'   => $slots,
                'queue_index' => $queueIndex ?? 0,
                'running_token' => $runningToken ?? 0
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('CustomerDiscoveryController@getSlots error: ' . $e->getMessage());
            return response()->json([
                'offline' => true,
                'slots'   => [],
                'queue_index' => 0,
                'running_token' => 0,
                'error'   => 'Could not fetch slots.'
            ], 500);
        }
    }

    private function resolveShift(\Carbon\Carbon $now, string $startTime, string $endTime): array
    {
        $shiftDate = $now->toDateString();
        $start = \Carbon\Carbon::parse("$shiftDate " . $startTime);
        $end = \Carbon\Carbon::parse("$shiftDate " . $endTime);

        if ($end->lt($start)) {
            $end->addDay();
        }

        if ($now->lt($start)) {
            $yDate = $now->copy()->subDay()->toDateString();
            $yStart = \Carbon\Carbon::parse("$yDate " . $startTime);
            $yEnd = \Carbon\Carbon::parse("$yDate " . $endTime);
            
            if ($yEnd->lt($yStart)) {
                $yEnd->addDay();
            }
            
            if ($now->lte($yEnd)) {
                $shiftDate = $yDate;
                $start = $yStart;
                $end = $yEnd;
            }
        }

        return [$shiftDate, $start, $end];
    }

    /**
     * Clamp an employee's start/end times within the vendor's global window.
     * Extracted from three identical duplicate blocks to a single source of truth.
     */
    private function clampEmployeeToVendorWindow(
        string $shiftDate,
        \Carbon\Carbon $empStart,
        \Carbon\Carbon $empEnd,
        Vendor $vendor
    ): array {
        if (!$vendor->global_opening_time) {
            return [$empStart, $empEnd];
        }

        $vStart = \Carbon\Carbon::parse("$shiftDate " . $vendor->global_opening_time);
        $vEnd   = \Carbon\Carbon::parse("$shiftDate " . $vendor->global_closing_time);

        if ($vEnd->lt($vStart)) {
            $vEnd->addDay();
        }

        if ($empStart->lt($vStart)) $empStart = $vStart->copy();
        if ($empEnd->gt($vEnd))    $empEnd   = $vEnd->copy();

        return [$empStart, $empEnd];
    }
}
