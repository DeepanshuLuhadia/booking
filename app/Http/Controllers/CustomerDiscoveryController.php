<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Employee;
use App\Models\Booking;
use App\Services\CustomerBookingService;
use App\Services\ShiftService;
use App\Services\SlotGenerationService;
use App\Services\ThemeService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class CustomerDiscoveryController extends Controller
{
    /** How many cards the landing page's "Recommended Professionals" grid shows. */
    private const RECOMMENDED_LIMIT = 8;

    /** Batch size for the category page's infinite scroll. */
    private const CATEGORY_PAGE_SIZE = 10;

    /** Batch size for the landing page's infinite scroll (search / filter results). */
    private const LISTING_PAGE_SIZE = 12;

    /**
     * Shortest keyword the search-as-you-type dropdown will act on.
     *
     * Anything shorter matches most of the catalogue, so the panel would be
     * noise and every keystroke a full candidate build.
     */
    public const SUGGEST_MIN_CHARS = 3;

    /** How many businesses the search dropdown previews before deferring to the results page. */
    private const SUGGEST_LIMIT = 6;

    /** How many matching professionals the search dropdown lists below the businesses. */
    private const SUGGEST_EMPLOYEE_LIMIT = 4;

    /** How many matching areas the search dropdown lists below the businesses. */
    private const SUGGEST_LOCATION_LIMIT = 4;

    /** Radius limit in km for vendor discovery on listing pages. */
    private const DISCOVERY_RADIUS_KM = 50;

    /**
     * Reserved category slug meaning "every category".
     *
     * Gives the full catalogue a home of its own — same header, same pills,
     * same infinite scroll as any single category — so "View All Professionals"
     * lands somewhere that belongs to the category section rather than on a
     * paged variant of the landing page.
     */
    public const ALL_CATEGORIES_SLUG = 'all';

    public function __construct(
        private ShiftService $shifts,
        private CustomerBookingService $customerBookings
    ) {
    }

public function index(Request $request)
{
    $terms      = $this->searchTerms($request);
    $isSearch   = $terms['is_search'];
    $sort       = $request->sort;
    $filterType = $request->type;

    $allThemes = Cache::remember(
        'all_themes',
        3600,
        fn() => ThemeService::getAllThemes()
    );

    $candidates = $this->listingCandidates($request);

    /*
    |--------------------------------------------------------------------------
    | Recommended Shortlist
    |--------------------------------------------------------------------------
    | The default landing grid is a shortlist, not a catalogue: eight cards,
    | ranked nearest-first once the visitor has shared a location and by
    | rating otherwise (see rankCandidates). Browsing the full set is what the
    | category pages are for, so there is no pager on this path. A search or
    | filter is a different intent — those stream in batches as the customer
    | scrolls, the same way the category pages do.
    */
    $isShortlist = !$isSearch && empty($filterType) && empty($sort)
        && $request->query('view') !== 'all';

    $totalVendors = $candidates->count();

    if ($isShortlist) {
        $vendors = $candidates->take(self::RECOMMENDED_LIMIT)->values();
        $hasMore = false;
    } else {
        $vendors = $candidates->take(self::LISTING_PAGE_SIZE)->values();
        $hasMore = $totalVendors > self::LISTING_PAGE_SIZE;
    }

    // The feed replays the page's own query string, so a search or filter
    // narrows every batch the scroll pulls and not just the first.
    $feedEndpoint = route('discover.vendors', $request->except(['page']));

    // Drives the section subtitle, so the ordering is never a silent surprise.
    $rankedByDistance = $this->hasCustomerLocation($request);
    $locationLabel    = $this->customerLocationLabel($request);

    /*
    |--------------------------------------------------------------------------
    | Hero Stat Counters
    |--------------------------------------------------------------------------
    | Previously computed inline in the Blade — four uncached aggregates on
    | every render, with the star rating hardcoded to 4.9. They are platform
    | totals that shift slowly, so a five-minute cache is plenty.
    */
    $stats = $this->heroStats();

    return view('customer.vendors', compact(
        'vendors',
        'allThemes',
        'stats',
        'rankedByDistance',
        'locationLabel',
        'isShortlist',
        'totalVendors',
        'hasMore',
        'feedEndpoint'
    ));
}

/**
 * Infinite-scroll feed for the landing listing: the next batch of cards for
 * whatever search / filter / sort the page itself was rendered with.
 *
 * Shares listingCandidates() with index() so both slice one ordering — a
 * batch can never disagree with the first screenful about what comes next.
 */
public function vendorsFeed(Request $request)
{
    $allThemes = Cache::remember(
        'all_themes',
        3600,
        fn() => ThemeService::getAllThemes()
    );

    $page       = max(1, (int) $request->query('page', 1));
    $candidates = $this->listingCandidates($request);

    $batch = $candidates
        ->slice(($page - 1) * self::LISTING_PAGE_SIZE, self::LISTING_PAGE_SIZE)
        ->values();

    return response()->json([
        'html'      => $batch->isEmpty() ? '' : view('customer.partials.vendor-cards', [
            'vendors'   => $batch,
            'allThemes' => $allThemes,
        ])->render(),
        'has_more'  => $candidates->count() > $page * self::LISTING_PAGE_SIZE,
        'next_page' => $page + 1,
    ]);
}

/**
 * Search-as-you-type suggestions for the listing and category search bars.
 *
 * Returns the first few matching businesses as pre-rendered mini cards, plus
 * the true match count so the panel can hand the rest over to the results
 * page. Scope follows the page the customer is standing on: the category
 * pages pass their own slug and stay inside it, the landing page passes none
 * and searches the whole catalogue.
 *
 * Built from listingCandidates() — the same list the form submission itself
 * would produce — so the preview can never disagree with the page it opens.
 */
public function suggestions(Request $request)
{
    $keyword = trim((string) $request->query('q', ''));

    if (mb_strlen($keyword) < self::SUGGEST_MIN_CHARS) {
        return response()->json(['html' => '', 'total' => 0, 'shown' => 0]);
    }

    // The catalogue slug is "every category", i.e. no filter at all.
    $type = trim((string) $request->query('type', ''));
    $type = $type === self::ALL_CATEGORIES_SLUG ? '' : $type;

    // listingCandidates() reads the request, so the typed keyword is put
    // where a submitted form would have left it.
    $request->merge(['search' => $keyword, 'type' => $type ?: null]);

    $allThemes = Cache::remember(
        'all_themes',
        3600,
        fn() => ThemeService::getAllThemes()
    );

    $matches = $this->listingCandidates($request);
    $shown   = $matches->take(self::SUGGEST_LIMIT)->values();

    // The dropdown is not only businesses: professionals matched by name link
    // straight to their booking page, and matching areas re-run the search
    // scoped to that address. Both respect the page's category scope.
    $employees = $this->suggestEmployees($keyword, $type);
    $locations = $this->suggestLocations($keyword, $type);

    return response()->json([
        'total' => $matches->count(),
        'shown' => $shown->count(),
        'html'  => view('customer.partials.search-suggestions', [
            'vendors'   => $shown,
            'employees' => $employees,
            'locations' => $locations,
            'allThemes' => $allThemes,
            'total'     => $matches->count(),
            'keyword'   => $keyword,
        ])->render(),
    ]);
}

/**
 * Bookable professionals whose name matches the typed keyword, for the
 * search dropdown. Same bookability bar as the listing itself (active, has a
 * fee, has working hours, at a live vendor) so no suggestion leads to an
 * unbookable page.
 */
private function suggestEmployees(string $keyword, string $type): \Illuminate\Support\Collection
{
    return Employee::query()
        ->where('name', 'LIKE', "%{$keyword}%")
        ->where('is_active', true)
        ->where('service_fee_override', '>', 0)
        ->whereNotNull('working_start_time')
        ->whereNotNull('working_end_time')
        ->whereHas('vendor', $this->suggestVendorConstraint($type))
        ->with(['vendor' => function ($q) {
            $q->select('id', 'business_name', 'address', 'slug');
        }])
        ->orderBy('name')
        ->limit(self::SUGGEST_EMPLOYEE_LIMIT)
        ->get();
}

/**
 * Areas matching the typed keyword, with how many live businesses each
 * holds. Addresses are free text, so the "area" is the comma-separated
 * segment of the address the keyword landed in ("35, Surana Street, Indore,
 * MP" typed as "indore" → "Indore") — which collapses every street in a city
 * into one row instead of listing each address separately. Picking one
 * re-submits the search with that segment as the keyword, which the LIKE
 * filter then narrows on.
 */
private function suggestLocations(string $keyword, string $type): \Illuminate\Support\Collection
{
    $addresses = Vendor::query()
        ->tap($this->suggestVendorConstraint($type))
        ->where('address', 'LIKE', "%{$keyword}%")
        ->pluck('address');

    $needle = mb_strtolower($keyword);
    $areas  = [];

    foreach ($addresses as $address) {
        $segment = collect(explode(',', (string) $address))
            ->map(fn ($part) => trim($part))
            ->first(fn ($part) => $part !== '' && str_contains(mb_strtolower($part), $needle));

        // A keyword spanning two segments matches the address but no single
        // segment; the full-address vendor cards already cover that case.
        if ($segment === null) {
            continue;
        }

        $key = mb_strtolower($segment);
        $areas[$key] ??= ['address' => $segment, 'vendor_count' => 0];
        $areas[$key]['vendor_count']++;
    }

    return collect($areas)
        ->sortByDesc('vendor_count')
        ->take(self::SUGGEST_LOCATION_LIMIT)
        ->values()
        ->map(fn ($area) => (object) $area);
}

/**
 * The "this vendor is live on the platform" bar shared by the employee and
 * area suggestions — mirrors discoverCandidates() so the dropdown never
 * offers what the listing would refuse to show. Category scope rides along
 * so the category pages' suggestions stay inside their own category.
 */
private function suggestVendorConstraint(string $type): \Closure
{
    return function ($q) use ($type) {
        $q->where('status', 'active')
            ->where('is_profile_complete', true)
            ->whereNotNull('global_opening_time')
            ->whereNotNull('global_closing_time')
            ->where(function ($sub) {
                $sub->whereNull('subscription_expires_at')
                    ->orWhere('subscription_expires_at', '>=', now());
            });

        if (filled($type)) {
            $q->whereHas('category', function ($cat) use ($type) {
                $cat->where('slug', $type);
            });
        }
    };
}

/**
 * Candidate list behind the landing listing, filters and all.
 *
 * Lifted out of index() so the scroll feed builds its batches from exactly
 * the same list rather than a second, subtly different query.
 */
private function listingCandidates(Request $request): \Illuminate\Support\Collection
{
    $terms      = $this->searchTerms($request);
    $sort       = $request->sort;
    $filterType = $request->type;

    $categorySlug = $request->category ?: $terms['inferred_category'];

    return $this->discoverCandidates($request, [
        'search'    => $terms['search'],
        'specialty' => $terms['specialty'],
        'location'  => $terms['location'],
        'state'     => $terms['state'],
        'type'      => $filterType,
        'sort'      => $sort,
        'cache'     => !$terms['is_search'] && empty($categorySlug) && empty($filterType) && empty($sort),
    ]);
}

/**
 * Category detail page: every vendor in one category, streamed in batches of
 * ten by the infinite scroll rather than paged.
 *
 * Searching from here stays here — the form posts back to this route and the
 * terms are applied on top of the category filter, so the customer never gets
 * bounced to the global listing mid-browse.
 */
public function category(Request $request, string $slug)
{
    $allThemes = Cache::remember(
        'all_themes',
        3600,
        fn() => ThemeService::getAllThemes()
    );

    // "all" is the catalogue view — every category at once — so it skips the
    // per-category lookup entirely rather than pretending to be a category.
    $isAllCategories = $slug === self::ALL_CATEGORIES_SLUG;

    $category = $isAllCategories
        ? null
        : \App\Models\VendorCategory::where('slug', $slug)->first();

    // A slug is legitimate if either the theme matrix or the categories table
    // knows it — the two are maintained separately.
    if (!$isAllCategories && !$category && !isset($allThemes[$slug])) {
        abort(404);
    }

    /*
    | The category dropdown submits with the form, so it is the one control
    | that can legitimately move the customer off this page: a different
    | category hops to that category page, "All Categories" widens to the
    | catalogue. Either way the search terms travel along.
    */
    if ($request->has('type')) {
        $requestedType = trim((string) $request->query('type')) ?: self::ALL_CATEGORIES_SLUG;
        $carried       = $request->except(['type', 'page', 'slug']);

        if ($requestedType !== $slug) {
            return redirect()->route('category.show', ['slug' => $requestedType] + $carried);
        }
    }

    $theme      = $isAllCategories ? [] : ($allThemes[$slug] ?? ThemeService::getTheme('consultant'));
    $terms      = $this->searchTerms($request);
    $candidates = $this->categoryCandidates($request, $slug, $terms);

    $total   = $candidates->count();
    $vendors = $candidates->take(self::CATEGORY_PAGE_SIZE)->values();

    return view('customer.category', [
        'vendors'          => $vendors,
        'allThemes'        => $allThemes,
        'theme'            => $theme,
        'categorySlug'     => $slug,
        'isAllCategories'  => $isAllCategories,
        'categoryLabel'    => $isAllCategories ? 'All' : ($theme['label'] ?? ($category->name ?? ucfirst($slug))),
        'categoryEmoji'    => $isAllCategories ? '⭐' : ($theme['emoji'] ?? '✨'),
        'totalVendors'     => $total,
        'hasMore'          => $total > self::CATEGORY_PAGE_SIZE,
        'perPage'          => self::CATEGORY_PAGE_SIZE,
        'rankedByDistance' => $this->hasCustomerLocation($request),
        'locationLabel'    => $this->customerLocationLabel($request),
        'isSearch'         => $terms['is_search'],
        'searchTerm'       => $terms['search'] ?: $terms['specialty'] ?: $terms['location'],
    ]);
}

/**
 * Infinite-scroll feed for the category page: the next batch of cards,
 * pre-rendered so the markup stays in one place (the vendor-card partial).
 *
 * The page's own query string is replayed here, so a search narrows every
 * batch and not just the first.
 */
public function categoryVendors(Request $request, string $slug)
{
    $allThemes = Cache::remember(
        'all_themes',
        3600,
        fn() => ThemeService::getAllThemes()
    );

    $page = max(1, (int) $request->query('page', 1));

    $candidates = $this->categoryCandidates($request, $slug, $this->searchTerms($request));

    $batch = $candidates
        ->slice(($page - 1) * self::CATEGORY_PAGE_SIZE, self::CATEGORY_PAGE_SIZE)
        ->values();

    return response()->json([
        'html'      => $batch->isEmpty() ? '' : view('customer.partials.vendor-cards', [
            'vendors'   => $batch,
            'allThemes' => $allThemes,
        ])->render(),
        'has_more'  => $candidates->count() > $page * self::CATEGORY_PAGE_SIZE,
        'next_page' => $page + 1,
    ]);
}

/**
 * The customer's own live booking at this vendor, if they have one.
 *
 * Identity resolution and the live-booking window both live in
 * CustomerBookingService now, so this page, the single-employee page, the
 * "My Bookings" page and BookingController's refusal all agree on what counts
 * as live. They used to each carry their own copy, and the narrower windows
 * hid bookings that were still being counted against the customer.
 */
private function activeBookingFor(Vendor $vendor, Request $request): ?Booking
{
    return $this->customerBookings->liveBookingFor($vendor, $request);
}

/**
 * Normalise the search inputs shared by the listing and the category page.
 *
 * The raw keyword goes through the intent parser first, so "cheap haircut
 * near me" narrows to the keyword the SQL LIKE can actually use; the category
 * it infers is returned separately for the caller to apply or ignore.
 */
private function searchTerms(Request $request): array
{
    $rawSearch = trim($request->search ?? '');
    $search    = $rawSearch;
    $inferred  = null;

    if (filled($rawSearch)) {
        $parsed = (new \App\Services\SearchIntentParserService())->parse($rawSearch);

        $inferred = $parsed['inferred_category'] ?: null;

        if ($parsed['clean_keyword']) {
            $search = $parsed['clean_keyword'];
        }
    }

    $specialty = trim($request->specialty ?? '');
    $location  = trim($request->location ?? '');

    return [
        'search'            => $search,
        'specialty'         => $specialty,
        'location'          => $location,
        'state'             => trim($request->state ?? ''),
        'inferred_category' => $inferred,
        'is_search'         => filled($search) || filled($specialty) || filled($location),
    ];
}

/**
 * Candidate list for one category, with any search terms layered on top.
 *
 * Shared by the page and its infinite-scroll feed so both slice the same
 * ordering. Only the unsearched list is cached — a per-keyword cache would
 * churn the store for no gain.
 */
private function categoryCandidates(Request $request, string $slug, array $terms): \Illuminate\Support\Collection
{
    return $this->discoverCandidates($request, [
        'search'    => $terms['search'],
        'specialty' => $terms['specialty'],
        'location'  => $terms['location'],
        'state'     => $terms['state'],
        // The catalogue view carries no category filter at all.
        'type'      => $slug === self::ALL_CATEGORIES_SLUG ? null : $slug,
        'cache'     => !$terms['is_search'],
    ]);
}

/**
 * Platform totals behind the hero counters.
 */
private function heroStats(): array
{
    return Cache::remember('discovery_hero_stats', 300, function () {
        return [
            'clients'      => (int) Booking::distinct('customer_phone')->count('customer_phone'),
            'cities'       => (int) Vendor::distinct('address')->count('address'),
            'appointments' => (int) Booking::count(),
            'reviews'      => (int) \App\Models\VendorReview::count(),
            'rating'       => round((float) \App\Models\VendorReview::avg('rating'), 1),
        ];
    });
}

/**
 * True when the visitor has usable coordinates on file, which is what turns
 * the listing from rating-ranked into distance-ranked.
 */
private function hasCustomerLocation(Request $request): bool
{
    return $this->coordinate($request->cookie('user_lat')) !== null
        && $this->coordinate($request->cookie('user_lng')) !== null;
}

/**
 * The place name to show the visitor: their suburb where GPS resolved one,
 * otherwise the widest thing we know. Null when no location has been shared.
 *
 * Only used for wording — the ranking itself runs off the coordinates.
 */
private function customerLocationLabel(Request $request): ?string
{
    foreach (['user_suburb', 'user_city', 'user_state'] as $cookie) {
        $value = trim((string) $request->cookie($cookie));

        if ($value !== '') {
            // OSM misspells some suburbs; correct on display only.
            return \App\Services\PlaceNameService::correct($value);
        }
    }

    return null;
}

/**
 * Build the ranked vendor list shared by the listing and the category pages.
 *
 * Accepts the already-normalised filters ('search', 'specialty', 'location',
 * 'state', 'type', 'sort') plus a 'cache' flag for the filter-free variants,
 * whose candidate set is identical for every visitor.
 */
private function discoverCandidates(Request $request, array $filters = []): \Illuminate\Support\Collection
{
    $search     = $filters['search']    ?? '';
    $specialty  = $filters['specialty'] ?? '';
    $location   = $filters['location']  ?? '';
    $state      = $filters['state']     ?? '';
    $filterType = $filters['type']      ?? null;
    $sort       = $filters['sort']      ?? null;

    $isSearch = filled($search) || filled($specialty) || filled($location);
    $now      = now(); // Single source of truth for time execution

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
        // Real star ratings for the listing cards. Aggregated in the same
        // query rather than through the average_rating accessor, which would
        // fire one AVG per card.
        ->withAvg('reviews as avg_rating', 'rating')
        ->withCount('reviews as reviews_count')
        // Backs the "Live Queue" indicator with an actual number: bookings
        // still standing on a live sheet. Two business dates are in play at
        // once for shops trading past midnight, and comparing slot_end_time
        // against the clock cannot tell a 00:15 slot tonight from one that
        // already passed this morning — so the queue is defined by status
        // instead. Leftovers from a finished shift are moved to 'expired' by
        // booking:reset-daily, which is what keeps this honest.
        ->withCount(['bookings as live_queue_count' => function ($q) {
            $q->where('status', 'confirmed')
                ->whereIn('booking_date', $this->shifts->liveBusinessDates());
        }])
        ->whereHas('employees', $activeEmployeeConstraint);

    /*
    |--------------------------------------------------------------------------
    | Category Filter
    |--------------------------------------------------------------------------
    | A slug is honoured when the theme matrix knows it or the categories table
    | does — the category pages are keyed on the table, which may carry slugs
    | the theme matrix has no entry for.
    */
    if (filled($filterType) && (
        array_key_exists($filterType, $allThemes)
        || \App\Models\VendorCategory::where('slug', $filterType)->exists()
    )) {
        $query->whereHas('category', function ($q) use ($filterType) {
            $q->where('slug', $filterType);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Search Filters
    |--------------------------------------------------------------------------
    */
    if ($isSearch || filled($state)) {
        $query->where(function ($q) use ($search, $specialty, $location, $state, $activeEmployeeConstraint) {
            if ($search) {
                $q->where(function ($sub) use ($search, $activeEmployeeConstraint) {
                    $sub->where('business_name', 'LIKE', "%{$search}%")
                        ->orWhere('owner_name', 'LIKE', "%{$search}%")
                        ->orWhere('address', 'LIKE', "%{$search}%")
                        // A customer often knows the professional rather than
                        // the shop, so a bookable employee's name surfaces
                        // their business too.
                        ->orWhereHas('employees', function ($emp) use ($search, $activeEmployeeConstraint) {
                            $activeEmployeeConstraint($emp);
                            $emp->where('name', 'LIKE', "%{$search}%");
                        });
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

    // Cache the candidate list for 60s whenever it is filter-free — the same
    // rows for every visitor. Keyed by category so each category page gets its
    // own bucket instead of sharing the unfiltered one.
    if ($filters['cache'] ?? false) {
        $cacheKey = 'default_discovery_candidates' . (filled($filterType) ? ':' . $filterType : '');
        $candidates = Cache::remember($cacheKey, 60, fn() => $query->get());
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

        // GATE 1: Vendor shop itself must be open right now and manually active/open
        if (!$vendor->is_open || $vendor->status !== 'active' || !$now->between($vOpen, $vClose)) {
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

    /*
    |--------------------------------------------------------------------------
    | Distance From The Customer
    |--------------------------------------------------------------------------
    | The consent modal in the layout stores the browser's coordinates in the
    | user_lat / user_lng cookies (plaintext — see the encryptCookies except
    | list in bootstrap/app.php). Both sides are optional: the customer may
    | have picked a state/city manually, which writes empty coordinates, and a
    | vendor may not have geocoded their shop. Either gap leaves distance_km
    | null and the card simply omits the chip rather than inventing a figure.
    |
    | Computed here, after the candidate cache is read, because the cached list
    | is shared across every visitor while the distance is per-customer.
    */
    $userLat = $this->coordinate($request->cookie('user_lat'));
    $userLng = $this->coordinate($request->cookie('user_lng'));

    $candidates->each(function ($vendor) use ($userLat, $userLng) {
        $vendor->distance_km = $this->distanceKm(
            $userLat,
            $userLng,
            $this->coordinate($vendor->latitude),
            $this->coordinate($vendor->longitude)
        );
    });

    // Filter vendors beyond the 50 km radius when user has shared location
    if ($userLat !== null && $userLng !== null) {
        $candidates = $candidates->filter(function ($vendor) {
            return $vendor->distance_km === null || $vendor->distance_km <= self::DISCOVERY_RADIUS_KM;
        });
    }

    // An explicit sort is the customer's choice — leave the SQL ordering alone.
    if ($sort === 'newest') {
        return $candidates->values();
    }

    return $this->rankCandidates($candidates, $userLat !== null && $userLng !== null);
}

/**
 * Rank the candidate list the way the listing presents it.
 *
 * Nearest first once the visitor has shared coordinates, best-rated first
 * otherwise, with bookable-now shops always ahead of closed ones (which only
 * appear at all on a name search). Applied as successive stable sorts, so
 * each earlier pass survives as the tie-breaker for the next.
 */
private function rankCandidates(\Illuminate\Support\Collection $candidates, bool $byDistance): \Illuminate\Support\Collection
{
    $ranked = $candidates
        ->sortByDesc(fn ($vendor) => (int) ($vendor->reviews_count ?? 0))
        ->sortByDesc(fn ($vendor) => (float) ($vendor->avg_rating ?? 0));

    if ($byDistance) {
        // Vendors with no coordinates on file sink below every located one
        // rather than being treated as if they were on the customer's doorstep.
        $ranked = $ranked->sortBy(fn ($vendor) => $vendor->distance_km ?? INF);
    }

    return $ranked
        ->sortByDesc(fn ($vendor) => (bool) ($vendor->is_bookable_now ?? false))
        ->values();
}

/**
 * Cast a coordinate to a usable float, or null.
 *
 * Guards the manual-location path, where the consent modal writes empty
 * strings into user_lat / user_lng, as well as vendors with no geocoding.
 * "0" is rejected deliberately: a stored zero here is an unset column, not
 * a shop in the Gulf of Guinea.
 */
private function coordinate($value): ?float
{
    if ($value === null || $value === '' || !is_numeric($value)) {
        return null;
    }

    $float = (float) $value;

    return abs($float) < 0.00001 ? null : $float;
}

/**
 * Great-circle distance in kilometres, or null when either point is unknown.
 *
 * Haversine, computed in PHP rather than SQL: the candidate list is already
 * materialised into a collection (and cached across visitors), so there is no
 * query to push this into without re-running it per request.
 */
private function distanceKm(?float $lat1, ?float $lng1, ?float $lat2, ?float $lng2): ?float
{
    if ($lat1 === null || $lng1 === null || $lat2 === null || $lng2 === null) {
        return null;
    }

    $earthRadius = 6371;

    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);

    $a = sin($dLat / 2) ** 2
        + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

    return round($earthRadius * 2 * asin(min(1.0, sqrt($a))), 2);
}

    public function show(Vendor $vendor, SlotGenerationService $slotService, Request $request)
{
    $vendor->load(['employees', 'category']);

    // Calculate distance from current location if available
    $userLat = $this->coordinate($request->cookie('user_lat'));
    $userLng = $this->coordinate($request->cookie('user_lng'));

    $vendor->distance_km = $this->distanceKm(
        $userLat,
        $userLng,
        $this->coordinate($vendor->latitude),
        $this->coordinate($vendor->longitude)
    );

    // Check if vendor is beyond 50 km radius - always return JSON response if AJAX request
    $isAjaxRequest = $request->expectsJson() || $request->has('_check_distance');

    if ($userLat !== null && $userLng !== null && $vendor->distance_km !== null && $vendor->distance_km > self::DISCOVERY_RADIUS_KM) {
        return response()->json([
            'distance_warning' => true,
            'vendor_id' => $vendor->id,
            'vendor_name' => $vendor->business_name,
            'distance_km' => $vendor->distance_km,
            'message' => "This vendor is {$vendor->distance_km} km away from your current location. Are you sure you want to continue?"
        ]);
    }

    // For AJAX requests with no distance warning, return minimal JSON
    if ($isAjaxRequest) {
        return response()->json([
            'distance_warning' => false,
            'vendor_id' => $vendor->id,
            'proceed' => true
        ]);
    }

    $isSubscriptionExpired = !$vendor->isSubscriptionActive();

    $now = Carbon::now();

    // The shift on the books right now — see ShiftService. Every queue and
    // token lookup below keys on this, never on the calendar date, so a shop
    // trading past midnight keeps one continuous sheet.
    $today = $this->shifts->businessDate($vendor, $now);

    /*
    |--------------------------------------------------------------------------
    | Returning Customer's Live Booking
    |--------------------------------------------------------------------------
    | Mirrors the single-employee booking page: a customer who already holds a
    | token here sees it — with their queue position — instead of a booking
    | button they are not allowed to press. BookingController enforces one
    | active booking per vendor per day, so this lookup is scoped to the
    | vendor, not to one employee: a token with any specialist here closes
    | booking for the whole shop, and the UI now says so up front rather than
    | letting the customer fill in the form and collect a 422.
    */
    $activeBooking = $this->activeBookingFor($vendor, $request);
    $activeBookingNowServing = $activeBooking
        ? (int) ($activeBooking->employee?->now_serving_token ?? 0)
        : 0;

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
    $opensAt = $vendor->global_opening_time ? \Carbon\Carbon::parse($vendor->global_opening_time)->format('h:i A') : '';
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

        // Populate opening time from selected employee window
        $opensAt = $empStart->format('h:i A');
        $isPaused = (bool) $selectedEmployee->is_paused;

        if (!$vendor->is_open || $vendor->status !== 'active') {
            $isOffline = true;
        }
        if ($vendor->bookings_paused) {
            $isPaused = true;
        }

        if ($empStart->lt($empEnd)) {
            if (!$vendor->is_open || $vendor->status !== 'active') {
                $isOffline = true;
            } else {
                $isOffline = !($now->gte($empStart) && $now->lt($empEnd));
            }

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
        'ratingCounts',
        'activeBooking',
        'activeBookingNowServing'
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
                ->where('booking_date', $this->shifts->businessDate($vendor))
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

        if (!$vendor->is_open || $vendor->status !== 'active') {
            return response()->json([
                'offline'  => true,
                'opens_at' => $vendor->global_opening_time ? \Carbon\Carbon::parse($vendor->global_opening_time)->format('h:i A') : 'Tomorrow',
                'slots'    => [],
                'queue_index' => 0,
                'running_token' => 0
            ]);
        }

        if ($vendor->bookings_paused) {
            return response()->json([
                'offline'  => false,
                'paused'   => true,
                'slots'    => [],
                'queue_index' => 0,
                'running_token' => 0
            ]);
        }

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

        // Token System Metadata (per-employee queue). Keyed on the vendor's
        // business date — the same value BookingController stamps on new
        // bookings — so the index shown always counts the rows the next token
        // will actually be drawn from. The calendar date used before drifted
        // off the shift the moment the clock passed midnight.
        $queueIndex = Booking::where('employee_id', $employee->id)
            ->where('booking_date', $this->shifts->businessDate($vendor))
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

    /**
     * Shift resolution and vendor-window clamping now live in ShiftService, so
     * the listing, the booking pages and the nightly reset all agree on which
     * trading day it is.
     */
    private function resolveShift(\Carbon\Carbon $now, string $startTime, string $endTime): array
    {
        return $this->shifts->resolveShift($now, $startTime, $endTime);
    }

    private function clampEmployeeToVendorWindow(
        string $shiftDate,
        \Carbon\Carbon $empStart,
        \Carbon\Carbon $empEnd,
        Vendor $vendor
    ): array {
        return $this->shifts->clampToVendorWindow($shiftDate, $empStart, $empEnd, $vendor);
    }
}
