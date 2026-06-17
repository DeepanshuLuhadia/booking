<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Employee;
use App\Models\Booking;
use App\Services\SlotGenerationService;
use App\Services\ThemeService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class CustomerDiscoveryController extends Controller
{
public function index(Request $request)
{
    $search     = trim($request->search ?? '');
    $specialty  = trim($request->specialty ?? '');
    $location   = trim($request->location ?? '');
    $sort       = $request->sort;
    $filterType = $request->type;
    $now        = now(); // Single source of truth for time execution

    $isSearch = filled($search) || filled($specialty) || filled($location);

    $allThemes = Cache::remember(
        'all_themes',
        3600,
        fn() => ThemeService::getAllThemes()
    );

    // Reusable employee database constraint
    $activeEmployeeConstraint = function ($q) {
        $q->where('is_active', true)
            ->where('service_fee_override', '>', 0)
            ->whereNotNull('working_start_time')
            ->whereNotNull('working_end_time');
    };

    $query = Vendor::query()
        ->where('status', 'active')
        ->where('is_profile_complete', true)
        ->where('is_open', true)
        ->whereNotNull('global_opening_time')
        ->whereNotNull('global_closing_time')
        ->where(function ($q) use ($now) {
            $q->whereNull('subscription_expires_at')
                ->orWhere('subscription_expires_at', '>=', $now);
        })
        ->with([
            'category',
            'employees' => $activeEmployeeConstraint,
        ])
        ->withMin(
            ['employees as starting_fee' => $activeEmployeeConstraint],
            'service_fee_override'
        )
        ->whereHas('employees', $activeEmployeeConstraint);

    /*
    |--------------------------------------------------------------------------
    | Category Filter
    |--------------------------------------------------------------------------
    */
    if ($filterType && array_key_exists($filterType, $allThemes)) {
        $query->whereHas('category', function ($q) use ($filterType) {
            $q->where('slug', $filterType);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Search Filters
    |--------------------------------------------------------------------------
    */
    if ($isSearch) {
        $query->where(function ($q) use ($search, $specialty, $location) {
            if ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('business_name', 'LIKE', "%{$search}%")
                        ->orWhere('owner_name', 'LIKE', "%{$search}%")
                        ->orWhere('address', 'LIKE', "%{$search}%");
                });
            }

            if ($specialty) {
                $q->where(function ($sub) use ($specialty) {
                    $sub->where('vendor_type', 'LIKE', "%{$specialty}%")
                        ->orWhereHas('category', function ($cat) use ($specialty) {
                            $cat->where('name', 'LIKE', "%{$specialty}%");
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
    | Sorting
    |--------------------------------------------------------------------------
    */
    if ($sort === 'newest') {
        $query->latest();
    } elseif ($sort === 'rating') {
        $query->orderByDesc('rating');
    } else {
        $query->latest();
    }

    $vendors = $query->paginate(24);

    /*
    |--------------------------------------------------------------------------
    | Calculate Real-Time Open Status
    |--------------------------------------------------------------------------
    */
    $vendors->getCollection()->transform(function ($vendor) use ($now) {
        $vendor->is_currently_open = false;

        if ($vendor->employees->isEmpty()) {
            return $vendor;
        }

        // Resolve vendor shift (supports overnight shifts smoothly)
        [$shiftDate, $vOpen, $vClose] = $this->resolveShift(
            $now,
            $vendor->global_opening_time,
            $vendor->global_closing_time
        );

        // Vendor storefront itself is currently closed right now
        if (!$now->between($vOpen, $vClose)) {
            return $vendor;
        }

        foreach ($vendor->employees as $employee) {
            // Anchor base times explicitly to the generated vendor shift start day
            $empStartDt = $vOpen->copy()->setTimeFromTimeString($employee->working_start_time);
            $empEndDt   = $vOpen->copy()->setTimeFromTimeString($employee->working_end_time);

            /*
            |--------------------------------------------------------------------------
            | Employee Cross-Midnight Lookahead (With Defensive lte Guard)
            |--------------------------------------------------------------------------
            */
            if ($empEndDt->lte($empStartDt)) {
                $empEndDt->addDay();
            }

            /*
            |--------------------------------------------------------------------------
            | Next-Day Symmetrical Alignment (Symmetrical Parallel Approach)
            |--------------------------------------------------------------------------
            */
            if ($vClose->isNextDay($vOpen) && $empStartDt->lt($vOpen)) {
                $empStartDt->addDay();
                $empEndDt->addDay();
            }

            // Cap employee working bounds strictly inside global vendor operation frames
            $empStartDt = $empStartDt->max($vOpen);
            $empEndDt   = $empEndDt->min($vClose);

            // Cancel check if formatting results in zero overlap scenario
            if ($empStartDt->gt($empEndDt)) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Strict Boundary Live System Match Check
            |--------------------------------------------------------------------------
            */
            if ($now->gte($empStartDt) && $now->lt($empEndDt)) {
                $vendor->is_currently_open = true;
                break;
            }
        }

        return $vendor;
    });

    /*
    |--------------------------------------------------------------------------
    | Default Listing
    |--------------------------------------------------------------------------
    */
    if (!$isSearch) {
        $vendors->setCollection(
            $vendors->getCollection()
                ->where('is_currently_open', true)
                ->values()
        );
    }

    return view('customer.vendors', compact('vendors', 'allThemes'));
}

    public function show(Vendor $vendor, SlotGenerationService $slotService)
{
    $vendor->load(['employees', 'category']);

    $isSubscriptionExpired = !$vendor->isSubscriptionActive();

    $now = Carbon::now();
    $today = $now->toDateString();

    /*
    |--------------------------------------------------------------------------
    | Resolve Employee Availability
    |--------------------------------------------------------------------------
    */
    foreach ($vendor->employees as $emp) {
        $emp->is_available = false;

        if (
            !$emp->is_active ||
            !$emp->working_start_time ||
            !$emp->working_end_time
        ) {
            continue;
        }

        [$shiftDate, $empStart, $empEnd] = $this->resolveShift(
            $now,
            $emp->working_start_time,
            $emp->working_end_time
        );

        [$empStart, $empEnd] = $this->clampEmployeeToVendorWindow(
            $shiftDate,
            $empStart,
            $empEnd,
            $vendor
        );

        // Ignore invalid or zero-length windows
        if ($empStart->gte($empEnd)) {
            continue;
        }

        $emp->is_available = $now->gte($empStart) && $now->lt($empEnd);
    }

    /*
    |--------------------------------------------------------------------------
    | Selected Employee (Defensive filtering to prevent 500 null crashes)
    |--------------------------------------------------------------------------
    */
    $selectedEmployee = $vendor->employees->first(function ($emp) {
        return $emp->is_active 
            && !is_null($emp->working_start_time) 
            && !is_null($emp->working_end_time);
    });

    $slots = [];
    $isOffline = true;
    $opensAt = '';
    $isPaused = false;
    $queueIndex = 0;
    $runningToken = 0;

    if ($selectedEmployee) {
        [$shiftDate, $empStart, $empEnd] = $this->resolveShift(
            $now,
            $selectedEmployee->working_start_time,
            $selectedEmployee->working_end_time
        );

        [$empStart, $empEnd] = $this->clampEmployeeToVendorWindow(
            $shiftDate,
            $empStart,
            $empEnd,
            $vendor
        );

        // Populate a fallback timestamp format before checking length bounds
        $opensAt = $empStart->format('h:i A');
        $isPaused = (bool) $selectedEmployee->is_paused;

        if ($empStart->lt($empEnd)) {
            $isOffline = !($now->gte($empStart) && $now->lt($empEnd));

            /*
            |--------------------------------------------------------------------------
            | Generate Slots
            |--------------------------------------------------------------------------
            */
            if (!$isOffline && !$isPaused) {
                $slots = $slotService->generateSlots(
                    $selectedEmployee,
                    $shiftDate,
                    $vendor
                );
            }
        } else {
            // Force offline state if employee hours are compressed out of bounds
            $isOffline = true;
        }

        /*
        |--------------------------------------------------------------------------
        | Queue Statistics
        |--------------------------------------------------------------------------
        */
        $bookingStats = Booking::where('employee_id', $selectedEmployee->id)
            ->where('booking_date', $today)
            ->selectRaw("
                MAX(token_number) as queue_index,
                MIN(CASE WHEN status = 'confirmed' THEN token_number END) as running_token
            ")
            ->first();

        $queueIndex = $bookingStats->queue_index ?? 0;
        $runningToken = $bookingStats->running_token ?? 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Theme Selection Matrix
    |--------------------------------------------------------------------------
    */
    $allThemes = Cache::remember(
        'all_themes',
        3600,
        fn() => ThemeService::getAllThemes()
    );

    $theme = $allThemes[$vendor->category?->slug]
        ?? ThemeService::getTheme('consultant');

    return view('customer.vendor-details', compact(
        'vendor',
        'selectedEmployee',
        'slots',
        'theme',
        'isOffline',
        'opensAt',
        'queueIndex',
        'runningToken',
        'isPaused',
        'isSubscriptionExpired'
    ));
}

    public function getSlots(Vendor $vendor, Employee $employee, SlotGenerationService $slotService)
    {
        if (!$vendor->isSubscriptionActive()) {
            return response()->json([
                'offline' => true,
                'error' => 'Subscription expired. Booking is disabled.',
                'slots' => [],
                'queue_index' => 0,
                'running_token' => 0
            ]);
        }

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
