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
        $search     = $request->search;
        $sort       = $request->sort;
        $filterType = $request->type;   // role filter
        $filterOpen = $request->filter === 'open_now';
        $now = now()->format('H:i:s');

        // All active vendors (closed ones shown greyed-out)
        $query = Vendor::where('status', 'active');

        // Role filter
        if ($filterType && array_key_exists($filterType, ThemeService::getAllThemes())) {
            $query->whereHas('category', function($q) use ($filterType) {
                $q->where('slug', $filterType);
            });
        }

        // Open-now filter
        if ($filterOpen) {
            $query->where('is_open', true)
                  ->where(function($q) use ($now) {
                      $q->where(function($q2) use ($now) {
                          $q2->whereColumn('global_opening_time', '<', 'global_closing_time')
                             ->where('global_opening_time', '<=', $now)
                             ->where('global_closing_time', '>=', $now);
                      })->orWhere(function($q2) use ($now) {
                          $q2->whereColumn('global_opening_time', '>=', 'global_closing_time')
                             ->where(function($q3) use ($now) {
                                 $q3->where('global_opening_time', '<=', $now)
                                    ->orWhere('global_closing_time', '>=', $now);
                             });
                      })->orWhereNull('global_opening_time');
                  });
        }

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'LIKE', "%{$search}%")
                  ->orWhere('address',       'LIKE', "%{$search}%")
                  ->orWhere('owner_name',    'LIKE', "%{$search}%");
            });
        }

        // Sorting
        if ($sort === 'newest') {
            $query->latest();
        } elseif ($sort === 'rating') {
            $query->orderBy('id', 'desc');
        } else {
            // Sort by dynamically open first using global times
            $query->orderByRaw(
                "(is_open = 1 AND 
                  (
                    (global_opening_time < global_closing_time AND ? BETWEEN global_opening_time AND global_closing_time)
                    OR 
                    (global_opening_time >= global_closing_time AND (? >= global_opening_time OR ? <= global_closing_time))
                    OR
                    (global_opening_time IS NULL)
                  )
                ) DESC", [$now, $now, $now]
            )->latest();
        }

        $vendors = $query->paginate(12);
        
        // Dynamically compute 'is_currently_open' for blade view using global times
        $vendors->getCollection()->transform(function($v) use ($now) {
            if (!$v->is_open || $v->status !== 'active') {
                $v->is_currently_open = false;
                return $v;
            }
            if ($v->global_opening_time && $v->global_closing_time) {
                $open = $v->global_opening_time;
                $close = $v->global_closing_time;
                if ($open < $close) {
                    $v->is_currently_open = ($now >= $open && $now <= $close);
                } else {
                    $v->is_currently_open = ($now >= $open || $now <= $close);
                }
            } else {
                $v->is_currently_open = true;
            }
            return $v;
        });

        $allThemes = ThemeService::getAllThemes();

        return view('customer.vendors', compact('vendors', 'allThemes'));
    }

    public function show(Vendor $vendor, SlotGenerationService $slotService)
    {
        $vendor->load(['employees', 'category']);

        $selectedEmployee = $vendor->employees()->where('is_active', true)->first();
        $slots = [];
        $isOffline = false;
        $opensAt = '';

        if ($selectedEmployee) {
            $now = \Carbon\Carbon::now();
            $employeeOpensAt = \Carbon\Carbon::parse($selectedEmployee->working_start_time);
            $employeeClosesAt = \Carbon\Carbon::parse($selectedEmployee->working_end_time);

            // Global Vendor Constraints
            if ($vendor->global_opening_time) {
                $vStart = \Carbon\Carbon::parse($vendor->global_opening_time);
                if ($employeeOpensAt->lt($vStart)) $employeeOpensAt = $vStart;
            }
            if ($vendor->global_closing_time) {
                $vEnd = \Carbon\Carbon::parse($vendor->global_closing_time);
                if ($employeeClosesAt->gt($vEnd)) $employeeClosesAt = $vEnd;
            }

            $opensAtToday = $now->copy()->setTimeFrom($employeeOpensAt);
            $closesAtToday = $now->copy()->setTimeFrom($employeeClosesAt);
            $windowOpensAt = $opensAtToday->copy()->subHours(2);

            $isOffline = $now->lt($windowOpensAt) || $now->gt($closesAtToday);
            $opensAt = $employeeOpensAt->format('h:i A');

            if (!$isOffline) {
                $slots = $slotService->generateSlots($selectedEmployee);
            }
        }

        // Resolve theme for this vendor's role
        $theme = ThemeService::getTheme($vendor->category?->slug ?? 'consultant');

        return view('customer.vendor-details', compact('vendor', 'selectedEmployee', 'slots', 'theme', 'isOffline', 'opensAt'));
    }

    public function getSlots(Vendor $vendor, Employee $employee, SlotGenerationService $slotService)
    {
        $now        = \Carbon\Carbon::now();
        $opensAt    = \Carbon\Carbon::parse($employee->working_start_time);
        $closesAt   = \Carbon\Carbon::parse($employee->working_end_time);

        // Global Vendor Constraints
        if ($vendor->global_opening_time) {
            $vStart = \Carbon\Carbon::parse($vendor->global_opening_time);
            if ($opensAt->lt($vStart)) $opensAt = $vStart;
        }
        if ($vendor->global_closing_time) {
            $vEnd = \Carbon\Carbon::parse($vendor->global_closing_time);
            if ($closesAt->gt($vEnd)) $closesAt = $vEnd;
        }

        // Normalise: if the comparison is cross-midnight, use today's full datetime
        $opensAtToday  = $now->copy()->setTimeFrom($opensAt);
        $closesAtToday = $now->copy()->setTimeFrom($closesAt);

        // Window opens 2 hours before the employee's working_start_time
        $windowOpensAt = $opensAtToday->copy()->subHours(2);

        // We're "offline" if:
        //   - current time is before the 2-hour pre-open window, AND
        //   - the shop has not yet closed for the day (i.e. we haven't passed closing)
        $shopNotYetOpenWindow = $now->lt($windowOpensAt);
        $shopAlreadyClosed    = $now->gt($closesAtToday);

        if ($shopNotYetOpenWindow || $shopAlreadyClosed) {
            return response()->json([
                'offline'  => true,
                'opens_at' => $opensAt->format('h:i A'),
            ]);
        }

        $slots = $slotService->generateSlots($employee);

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
            'queue_index' => $queueIndex,
            'running_token' => $runningToken ?? 0
        ]);
    }
}
