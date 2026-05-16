<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Employee;
use App\Models\Booking;
use App\Services\SlotGenerationService;
use App\Services\ThemeService;
use Illuminate\Http\Request;

class CustomerDiscoveryController extends Controller
{
public function index(Request $request)
{
    $search        = trim($request->search);
    $sort          = $request->sort;
    $filterType    = $request->type;
    $filterOpen    = $request->filter === 'open_now';
    $now           = now();
    $currentTime   = $now->format('H:i:s');

    /*
    |--------------------------------------------------------------------------
    | Base Query
    |--------------------------------------------------------------------------
    |
    | Rules:
    | - Vendor must be active
    | - Profile must be complete
    | - Vendor must be manually open
    | - Vendor must have global timings
    | - Vendor must have at least one valid employee
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
    | Category Filter
    |--------------------------------------------------------------------------
    */

    if (
        $filterType &&
        array_key_exists($filterType, ThemeService::getAllThemes())
    ) {
        $query->whereHas('category', function ($q) use ($filterType) {
            $q->where('slug', $filterType);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Search Filter
    |--------------------------------------------------------------------------
    */

    if ($search) {
        $query->where(function ($q) use ($search) {

            $q->where('business_name', 'LIKE', "%{$search}%")
                ->orWhere('owner_name', 'LIKE', "%{$search}%")
                ->orWhere('address', 'LIKE', "%{$search}%");

        });
    }

    /*
    |--------------------------------------------------------------------------
    | Open Now Filter
    |--------------------------------------------------------------------------
    |
    | Vendor global timing logic
    | Supports cross-midnight timing
    |
    */

    if ($filterOpen) {

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
    }

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

    $vendors = $query->paginate(12);

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

        $shiftDate = $now->toDateString();

        $vOpen = \Carbon\Carbon::parse("$shiftDate " . $vendor->global_opening_time);
        $vClose = \Carbon\Carbon::parse("$shiftDate " . $vendor->global_closing_time);

        if ($vClose->lt($vOpen)) {
            $vClose->addDay();
        }

        if ($now->lt($vOpen)) {
            $yDate = $now->copy()->subDay()->toDateString();
            $yOpen = \Carbon\Carbon::parse("$yDate " . $vendor->global_opening_time);
            $yClose = \Carbon\Carbon::parse("$yDate " . $vendor->global_closing_time);
            if ($yClose->lt($yOpen)) {
                $yClose->addDay();
            }
            if ($now->lte($yClose)) {
                $shiftDate = $yDate;
                $vOpen = $yOpen;
                $vClose = $yClose;
            }
        }

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

    /*
    |--------------------------------------------------------------------------
    | Optional:
    | Hide closed vendors after calculation
    |--------------------------------------------------------------------------
    |
    | Uncomment if needed
    |
    */

    // $vendors->setCollection(
    //     $vendors->getCollection()
    //         ->where('is_currently_open', true)
    //         ->values()
    // );

    $allThemes = ThemeService::getAllThemes();

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

            $shiftDate = $nowDt->toDateString();
            $empStart = \Carbon\Carbon::parse("$shiftDate " . $emp->working_start_time);
            $empEnd   = \Carbon\Carbon::parse("$shiftDate " . $emp->working_end_time);

            if ($empEnd->lt($empStart)) {
                $empEnd->addDay();
            }

            if ($nowDt->lt($empStart)) {
                $yDate = $nowDt->copy()->subDay()->toDateString();
                $yStart = \Carbon\Carbon::parse("$yDate " . $emp->working_start_time);
                $yEnd = \Carbon\Carbon::parse("$yDate " . $emp->working_end_time);
                if ($yEnd->lt($yStart)) {
                    $yEnd->addDay();
                }
                if ($nowDt->lte($yEnd)) {
                    $shiftDate = $yDate;
                    $empStart = $yStart;
                    $empEnd = $yEnd;
                }
            }

            // Bound by Vendor's Global Times if set
            if ($vendor->global_opening_time) {
                $vStart = \Carbon\Carbon::parse("$shiftDate " . $vendor->global_opening_time);
                $vEnd = \Carbon\Carbon::parse("$shiftDate " . $vendor->global_closing_time);
                if ($vEnd->lt($vStart)) {
                    $vEnd->addDay();
                }
                if ($empStart->lt($vStart)) $empStart = $vStart->copy();
                if ($empEnd->gt($vEnd)) $empEnd = $vEnd->copy();
            }

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
            $shiftDate = $now->toDateString();
            
            $empStart = \Carbon\Carbon::parse("$shiftDate " . $selectedEmployee->working_start_time);
            $empEnd = \Carbon\Carbon::parse("$shiftDate " . $selectedEmployee->working_end_time);

            if ($empEnd->lt($empStart)) {
                $empEnd->addDay();
            }

            if ($now->lt($empStart)) {
                $yDate = $now->copy()->subDay()->toDateString();
                $yStart = \Carbon\Carbon::parse("$yDate " . $selectedEmployee->working_start_time);
                $yEnd = \Carbon\Carbon::parse("$yDate " . $selectedEmployee->working_end_time);
                if ($yEnd->lt($yStart)) {
                    $yEnd->addDay();
                }
                if ($now->lte($yEnd)) {
                    $shiftDate = $yDate;
                    $empStart = $yStart;
                    $empEnd = $yEnd;
                }
            }

            // Global Vendor Constraints
            if ($vendor->global_opening_time) {
                $vStart = \Carbon\Carbon::parse("$shiftDate " . $vendor->global_opening_time);
                $vEnd = \Carbon\Carbon::parse("$shiftDate " . $vendor->global_closing_time);
                if ($vEnd->lt($vStart)) {
                    $vEnd->addDay();
                }
                if ($empStart->lt($vStart)) $empStart = $vStart->copy();
                if ($empEnd->gt($vEnd)) $empEnd = $vEnd->copy();
            }

            if ($vendor->appointment_mode === 'appointment') {
                $windowOpensAt = $empStart->copy()->subHours(2);
                $isOffline = $now->lt($windowOpensAt) || $now->gt($empEnd);
            } else {
                $isOffline = $now->lt($empStart) || $now->gt($empEnd);
            }
            $opensAt = (clone $empStart)->format('h:i A');

            if (!$isOffline) {
                $slots = $slotService->generateSlots($selectedEmployee, $shiftDate);
            }
        }

        // Resolve theme for this vendor's role
        $theme = ThemeService::getTheme($vendor->category?->slug ?? 'consultant');

        return view('customer.vendor-details', compact('vendor', 'selectedEmployee', 'slots', 'theme', 'isOffline', 'opensAt'));
    }

    public function getSlots(Vendor $vendor, Employee $employee, SlotGenerationService $slotService)
    {
        $now = \Carbon\Carbon::now();
        $shiftDate = $now->toDateString();
        
        $empStart = \Carbon\Carbon::parse("$shiftDate " . $employee->working_start_time);
        $empEnd = \Carbon\Carbon::parse("$shiftDate " . $employee->working_end_time);
        
        if ($empEnd->lt($empStart)) {
            $empEnd->addDay();
        }

        if ($now->lt($empStart)) {
            $yDate = $now->copy()->subDay()->toDateString();
            $yStart = \Carbon\Carbon::parse("$yDate " . $employee->working_start_time);
            $yEnd = \Carbon\Carbon::parse("$yDate " . $employee->working_end_time);
            if ($yEnd->lt($yStart)) {
                $yEnd->addDay();
            }
            if ($now->lte($yEnd)) {
                $shiftDate = $yDate;
                $empStart = $yStart;
                $empEnd = $yEnd;
            }
        }

        // Global Vendor Constraints
        if ($vendor->global_opening_time) {
            $vStart = \Carbon\Carbon::parse("$shiftDate " . $vendor->global_opening_time);
            $vEnd = \Carbon\Carbon::parse("$shiftDate " . $vendor->global_closing_time);
            if ($vEnd->lt($vStart)) {
                $vEnd->addDay();
            }
            if ($empStart->lt($vStart)) $empStart = $vStart->copy();
            if ($empEnd->gt($vEnd)) $empEnd = $vEnd->copy();
        }

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

        $slots = $slotService->generateSlots($employee, $shiftDate);

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
    }
}
