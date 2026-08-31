<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Vendor;
use App\Models\Booking;
use App\Services\CustomerBookingService;
use App\Services\ShiftService;
use App\Services\SlotGenerationService;
use App\Services\ThemeService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class EmployeePublicBookingController extends Controller
{
    /** Radius limit in km for vendor discovery. */
    private const DISCOVERY_RADIUS_KM = 50;

    public function __construct(
        private ShiftService $shifts,
        private CustomerBookingService $customerBookings
    ) {
    }

    /**
     * Display the standalone employee booking page.
     * Accessible publicly via /employee/{employee:slug} or /employee/{id}
     */
    public function show($identifier, Request $request, SlotGenerationService $slotService)
    {
        // Resolve employee by slug or ID
        $employee = Employee::where('slug', $identifier)
            ->orWhere('id', $identifier)
            ->with(['vendor.category'])
            ->firstOrFail();

        $vendor = $employee->vendor;

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

        // Business date of the shift being worked — see ShiftService. Using the
        // calendar date here made a customer's live token vanish at midnight on
        // an overnight shift.
        $today = $this->shifts->businessDate($vendor, $now);

        /*
        | The customer's live booking at this *business*, not merely with this
        | specialist. BookingController allows one active booking per vendor per
        | day, so a token held with a colleague closes this page too — scoping
        | the lookup to one employee showed a booking button that the server was
        | always going to refuse. Identity resolution lives in
        | CustomerBookingService so this page, the vendor page and the refusal
        | itself cannot drift apart.
        */
        $activeBooking = $this->customerBookings->liveBookingFor($vendor, $request);

        // Queue figures resolved against the booking's *own* specialist, which
        // is not necessarily the one being viewed.
        $activeBookingInfo = $activeBooking
            ? $this->customerBookings->present($activeBooking)
            : null;

        // Calculate employee availability
        $employee->is_available = false;
        if ($employee->is_active && $employee->working_start_time && $employee->working_end_time) {
            [$shiftDate, $empStart, $empEnd] = $this->resolveShift(
                $now,
                $employee->working_start_time,
                $employee->working_end_time
            );

            [$empStart, $empEnd] = $this->clampEmployeeToVendorWindow(
                $shiftDate,
                $empStart,
                $empEnd,
                $vendor
            );

            if ($empStart->lt($empEnd)) {
                $employee->is_available = $now->gte($empStart) && $now->lt($empEnd);
            }
        }

        $slots = [];
        $isOffline = true;
        $opensAt = '';
        $isPaused = (bool) $employee->is_paused;
        $queueIndex = 0;
        $runningToken = 0;

        if ($employee) {
            [$shiftDate, $empStart, $empEnd] = $this->resolveShift(
                $now,
                $employee->working_start_time,
                $employee->working_end_time
            );

            [$empStart, $empEnd] = $this->clampEmployeeToVendorWindow(
                $shiftDate,
                $empStart,
                $empEnd,
                $vendor
            );

            $opensAt = $empStart->format('h:i A');

            if ($empStart->lt($empEnd)) {
                $isOffline = !($now->gte($empStart) && $now->lt($empEnd));

                if (!$isOffline && !$isPaused && !$isSubscriptionExpired) {
                    $slots = $slotService->generateSlots(
                        $employee,
                        $shiftDate,
                        $vendor
                    );
                }
            }

            // Keyed on the vendor's business date — the same value the booking
            // write path stamps on new rows — so the index counts exactly the
            // bookings the next token will be drawn from.
            $queueIndex = Booking::where('employee_id', $employee->id)
                ->where('booking_date', $today)
                ->whereNotNull('token_number')
                ->max('token_number') ?? 0;

            $runningToken = $employee->now_serving_token ?? 0;
        }

        $allThemes = Cache::remember(
            'all_themes',
            3600,
            fn() => ThemeService::getAllThemes()
        );

        $theme = $allThemes[$vendor->category?->slug]
            ?? ThemeService::getTheme('consultant');

        /*
        | `service_fee`, not `base_service_fee` — there is no such column, so
        | this read was always null and the page quoted ₹0 to every customer of
        | a shop that had not set a per-employee override.
        |
        | Cosmetic until direct UPI payment landed; now load-bearing. In
        | full-payment mode the booking button quotes this figure and the server
        | charges the real service fee, so a null here would show "PAY ₹0 &
        | BOOK" and then ask for ₹500 on the next screen.
        |
        | Matches BookingController, which prices bookings the same way.
        */
        $servicePrice = $employee->service_fee_override > 0
            ? $employee->service_fee_override
            : $vendor->service_fee;

        return view('customer.employee-show', compact(
            'employee',
            'vendor',
            'activeBooking',
            'activeBookingInfo',
            'slots',
            'theme',
            'isOffline',
            'opensAt',
            'queueIndex',
            'runningToken',
            'isPaused',
            'isSubscriptionExpired',
            'servicePrice'
        ));
    }

    /**
     * Both helpers delegate to ShiftService — the single definition shared with
     * the discovery listing, the booking write path and the queue reset.
     *
     * Typed against the base \Carbon\Carbon rather than the Illuminate subclass
     * imported above: the service hands back base instances, and a parent is
     * not an instance of its child.
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

    /**
     * Cast a coordinate to a usable float, or null.
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
}
