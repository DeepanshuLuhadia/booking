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
    $rawSearch  = trim($request->search ?? '');
    $search     = $rawSearch;
    $categorySlug = $request->category;
    $specialty  = trim($request->specialty ?? '');
    $location   = trim($request->location ?? '');
    $sort       = $request->sort;
    $filterType = $request->type;
    $now        = now(); // Single source of truth for time execution

    // Natural Language AI Search Intent Parsing
    if (filled($rawSearch)) {
        $intentParser = new \App\Services\SearchIntentParserService();
        $parsedIntent = $intentParser->parse($rawSearch);
        if ($parsedIntent['inferred_category'] && empty($categorySlug)) {
            $categorySlug = $parsedIntent['inferred_category'];
        }
        if ($parsedIntent['clean_keyword']) {
            $search = $parsedIntent['clean_keyword'];
        }
    }

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

    // Cache default discovery query candidate list for 60s when no specific user search is active
    if (!$isSearch && empty($categorySlug) && empty($filterType) && empty($sort)) {
        $candidates = Cache::remember('default_discovery_candidates', 60, fn() => $query->get());
    } else {
        $candidates = $query->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Real-Time Open Status
    |--------------------------------------------------------------------------
    */
    $candidates->transform(function ($vendor) use ($now) {
        // Gate 1: Vendor/shop global hours must include $now.
        // Gate 2: At least one non-paused employee must be working $now within that window.
        // Both gates must pass for the vendor to appear in the default listing.
        $vendor->is_bookable_now = false;

        if ($vendor->employees->isEmpty()) {
            return $vendor;
        }

        // Resolve vendor shift — handles overnight hours (e.g. 22:00 → 02:00)
        [$shiftDate, $vOpen, $vClose] = $this->resolveShift(
            $now,
            $vendor->global_opening_time,
            $vendor->global_closing_time
        );

        // GATE 1: Vendor shop itself must be open right now
        if (!$now->between($vOpen, $vClose)) {
            return $vendor; // Shop is closed → never show in listing
        }

        // GATE 2: Check if any employee is available right now
        foreach ($vendor->employees as $employee) {
            // Paused employees are not accepting bookings — skip
            if ($employee->is_paused) {
                continue;
            }

            // Resolve employee's own shift anchored to the vendor's shift day
            $empStartDt = $vOpen->copy()->setTimeFromTimeString($employee->working_start_time);
            $empEndDt   = $vOpen->copy()->setTimeFromTimeString($employee->working_end_time);

            // Handle cross-midnight employee shifts (e.g. 22:00 → 01:00)
            if ($empEndDt->lte($empStartDt)) {
                $empEndDt->addDay();
            }

            // Align next-day employees symmetrically with the vendor's overnight window
            if ($vClose->isNextDay($vOpen) && $empStartDt->lt($vOpen)) {
                $empStartDt->addDay();
                $empEndDt->addDay();
            }

            // Clamp employee window strictly inside the vendor's open window
            $empStartDt = $empStartDt->max($vOpen);
            $empEndDt   = $empEndDt->min($vClose);

            // Skip if the clamping produced zero or negative overlap
            if ($empStartDt->gte($empEndDt)) {
                continue;
            }

            // Employee is currently serving within their shift window
            if ($now->gte($empStartDt) && $now->lt($empEndDt)) {
                $vendor->is_bookable_now = true;
                break; // One available employee is enough to show the vendor
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
        $candidates = $candidates->where('is_bookable_now', true)->values();
    }

    $page = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
    $perPage = 24;
    $results = $candidates->slice(($page - 1) * $perPage, $perPage)->values();

    $vendors = new \Illuminate\Pagination\LengthAwarePaginator(
        $results,
        $candidates->count(),
        $perPage,
        $page,
        ['path' => $request->url(), 'query' => $request->query()]
    );

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
    // Default view shows only the latest 5 reviews. Filtering by a star rating
    // fetches another 5 on demand from the reviewsList() endpoint.
    $reviews = $vendor->reviews()
        ->latest()
        ->take(5)
        ->get()
        ->map(fn ($r) => $this->reviewToArray($r))
        ->values();

    $reviewsCount  = $vendor->reviews()->count();
    $averageRating = round((float) $vendor->reviews()->avg('rating'), 1);

    // Per-star counts for the breakdown bars — independent of the 5 shown, so the
    // bars stay accurate even though only a handful of reviews are loaded.
    $counts = $vendor->reviews()
        ->selectRaw('rating, COUNT(*) as c')
        ->groupBy('rating')
        ->pluck('c', 'rating');
    $ratingCounts = collect([5, 4, 3, 2, 1])
        ->mapWithKeys(fn ($n) => [$n => (int) ($counts[$n] ?? 0)]);

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
        'averageRating',
        'ratingCounts'
    ));
}

    /**
     * JSON endpoint: latest 5 reviews for a vendor, optionally filtered to a
     * single star rating. Powers the rating-filter clicks on the profile page.
     */
    public function reviewsList(Vendor $vendor, Request $request)
    {
        $rating = (int) $request->query('rating', 0);

        $query = $vendor->reviews()->latest();
        if ($rating >= 1 && $rating <= 5) {
            $query->where('rating', $rating);
        }

        return response()->json([
            'reviews' => $query->take(5)->get()
                ->map(fn ($r) => $this->reviewToArray($r))
                ->values(),
        ]);
    }

    /**
     * Shape a Review model into the array consumed by the profile page's Alpine
     * review component. Shared by show() and reviewsList().
     */
    private function reviewToArray($r): array
    {
        return [
            'name'          => $r->reviewer_name,
            'rating'        => $r->rating,
            'comment'       => $r->comment,
            'verified'      => $r->is_verified,
            'images'        => collect($r->images ?? [])->map(fn ($p) => asset('storage/' . $p))->all(),
            'created_human' => $r->created_at->diffForHumans(),
        ];
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

        $currentHour = (int) now()->format('H');
        $peakHourActive = ($currentHour >= 17 && $currentHour <= 20);

        // Queue progress as a percentage of total tokens issued today (0–100%)
        $progressPercent = ($queueIndex > 0)
            ? (int) round(($nowServing / $queueIndex) * 100)
            : 0;

        // Dynamic smart ETA using QueueVelocityService
        $approxWait = 0;
        if ($employee && $request->filled('my_token')) {
            $myToken = (int) $request->my_token;
            $queueVelocityService = new \App\Services\QueueVelocityService();
            $approxWait = $queueVelocityService->calculateEstimatedWait($vendor, $employee, $myToken);
        }

        return response()->json([
            'now_serving'           => $nowServing,
            'queue_index'           => $queueIndex,
            'is_open'               => $vendor->is_open,
            'bookings_paused'       => $vendor->bookings_paused,
            'queue_progress_percent' => $progressPercent,
            'approx_wait_min'       => $approxWait,
            'peak_hour_active'      => $peakHourActive,
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

        // Cache time-slot results for 30 seconds per employee per day.
        // Token mode is intentionally excluded: queue index changes every few seconds.
        if ($vendor->appointment_mode === 'time_slot') {
            $cacheKey = "slots:{$employee->id}:{$shiftDate}";
            $slots = \Illuminate\Support\Facades\Cache::remember($cacheKey, 30, fn() => $slotService->generateSlots($employee, $shiftDate, $vendor));
        } else {
            $slots = $slotService->generateSlots($employee, $shiftDate, $vendor);
        }

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
