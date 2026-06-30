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
    if ($isSearch || filled($request->state)) {
        $state = trim($request->state ?? '');
        $query->where(function ($q) use ($search, $specialty, $location, $state) {
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
            } elseif ($state) {
                $q->where('address', 'LIKE', "%{$state}%");
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
    } else {
        // Order: open vendors first, then by newest
        $query->orderByDesc('is_open')->latest();
    }

    $vendors = $query->paginate(24);

    /*
    |--------------------------------------------------------------------------
    | Calculate Real-Time Open Status
    |--------------------------------------------------------------------------
    */
    $vendors->getCollection()->transform(function ($vendor) use ($now) {
        // NOTE: use a dedicated key (not `is_currently_open`, which is a model
        // accessor that only reflects shop hours and would shadow this value).
        // is_bookable_now = shop open AND >=1 active employee working right now.
        $vendor->is_bookable_now = false;

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
            // A paused employee is not accepting bookings — skip them.
            if ($employee->is_paused) {
                continue;
            }

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
                $vendor->is_bookable_now = true;
                break;
            }
        }

        return $vendor;
    });

    /*
    |--------------------------------------------------------------------------
    | Hide Closed Shops
    |--------------------------------------------------------------------------
    | Closed shops are only revealed when a customer explicitly searches by
    | name. In every other case — default listing, specialty/location/category
    | filters — only shops bookable right now are shown.
    */
    $revealClosed = filled($search);

    if (!$revealClosed) {
        $vendors->setCollection(
            $vendors->getCollection()
                ->where('is_bookable_now', true)
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
    | Reorder Specialists By Live Bookability
    |--------------------------------------------------------------------------
    | Specialists who can take a booking right now (available + not paused)
    | float to the top; available-but-paused next; everyone unavailable sinks
    | to the bottom. This makes the on-load ordering match real availability
    | and stays stable across page refreshes.
    */
    $bookableRank = fn ($emp) => ($emp->is_available ? 2 : 0) + ($emp->is_paused ? 0 : 1);

    $vendor->setRelation(
        'employees',
        $vendor->employees->sortByDesc($bookableRank)->values()
    );

    /*
    |--------------------------------------------------------------------------
    | Selected Employee (prefer a specialist who is bookable right now)
    |--------------------------------------------------------------------------
    | Default to the first specialist who can actually take a booking now so
    | Step 2 doesn't open on an "out of appointment" employee. Fall back to any
    | available, then any active one (defensive — prevents 500 null crashes).
    */
    $isActiveSpecialist = function ($emp) {
        return $emp->is_active
            && !is_null($emp->working_start_time)
            && !is_null($emp->working_end_time);
    };

    $selectedEmployee = $vendor->employees->first(fn ($emp) => $emp->is_available && !$emp->is_paused)
        ?? $vendor->employees->first(fn ($emp) => $emp->is_available)
        ?? $vendor->employees->first($isActiveSpecialist);

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
        $queueIndex = Booking::where('employee_id', $selectedEmployee->id)
            ->where('booking_date', $today)
            ->whereNotNull('token_number')
            ->max('token_number') ?? 0;

        // "Now serving" is the employee's live counter, advanced as they serve.
        $runningToken = $selectedEmployee->now_serving_token ?? 0;
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

    /*
    |--------------------------------------------------------------------------
    | Reviews & Ratings
    |--------------------------------------------------------------------------
    | Reported reviews stay publicly visible (a report only flags them for
    | admin moderation); they vanish only when an admin deletes them.
    */
    $reviews = $vendor->reviews()
        ->latest()
        ->take(50)
        ->get()
        ->map(fn ($r) => [
            'name'          => $r->reviewer_name,
            'rating'        => $r->rating,
            'comment'       => $r->comment,
            'verified'      => $r->is_verified,
            'images'        => collect($r->images ?? [])->map(fn ($p) => asset('storage/' . $p))->all(),
            'created_human' => $r->created_at->diffForHumans(),
        ])
        ->values();

    $reviewsCount  = $vendor->reviews()->count();
    $averageRating = round((float) $vendor->reviews()->avg('rating'), 1);

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
        'isSubscriptionExpired',
        'reviews',
        'reviewsCount',
        'averageRating'
    ));
}

    public function queueStatus(Vendor $vendor, Request $request)
    {
        // Queue is per-employee. Resolve the employee from the request, falling
        // back to the vendor's first active employee.
        $employee = null;
        if ($request->filled('employee_id')) {
            $employee = Employee::where('vendor_id', $vendor->id)
                ->where('id', $request->employee_id)
                ->first();
        }
        $employee ??= $vendor->employees()->where('is_active', true)->first();

        $nowServing = $employee->now_serving_token ?? 0;

        $queueIndex = $employee
            ? (Booking::where('employee_id', $employee->id)
                ->where('booking_date', now()->toDateString())
                ->whereNotNull('token_number')
                ->max('token_number') ?? 0)
            : 0;

        return response()->json([
            'now_serving' => $nowServing,
            'queue_index' => $queueIndex,
            'is_open' => $vendor->is_open,
            'bookings_paused' => $vendor->bookings_paused,
        ]);
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
        if ($vendor->appointment_mode === 'time_slot') {
            // Time-slot mode: let customers see slots from 2 hours before opening.
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

        // Token System Metadata (per-employee queue)
        $queueIndex = Booking::where('employee_id', $employee->id)
            ->where('booking_date', now()->toDateString())
            ->whereNotNull('token_number')
            ->max('token_number');

        $runningToken = $employee->now_serving_token ?? 0;

            return response()->json([
                'offline' => false,
                'slots'   => $slots,
                'queue_index' => $queueIndex ?? 0,
                'running_token' => $runningToken
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
