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
        // Mandatory filter: Profile must be complete
        $query = Vendor::where('status', 'active')
            ->where('is_profile_complete', true)
            ->with(['employees' => function($q) {
                $q->where('is_active', true)->where(function($q2) {
                    $q2->where('service_fee_override', '>', 0)
                       ->orWhereNull('service_fee_override');
                });
            }, 'category']);

        // Exclude vendors where ALL employees have 0 fee
        $query->whereHas('employees', function($q) {
            $q->where('is_active', true)->where(function($q2) {
                $q2->where('service_fee_override', '>', 0)
                   ->orWhereNull('service_fee_override');
            });
        });

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
        
        $vendors->getCollection()->transform(function($v) use ($now) {
            $activeEmployeesWithFee = $v->employees->where('is_active', true)->filter(function($e) {
                return is_null($e->service_fee_override) || $e->service_fee_override > 0;
            });
            
            // Calculate starting fee
            $v->starting_fee = $activeEmployeesWithFee->min('service_fee_override') ?? $v->service_fee;

            if (!$v->is_open || $v->status !== 'active' || $activeEmployeesWithFee->isEmpty()) {
                $v->is_currently_open = false;
                return $v;
            }

            $nowDt = \Carbon\Carbon::now();
            $hasAvailableEmployee = false;

            foreach ($activeEmployeesWithFee as $emp) {
                $empStart = \Carbon\Carbon::parse($emp->working_start_time);
                $empEnd   = \Carbon\Carbon::parse($emp->working_end_time);

                // Apply Global Vendor Constraints
                if ($v->global_opening_time) {
                    $vOpen = \Carbon\Carbon::parse($v->global_opening_time);
                    if ($empStart->lt($vOpen)) $empStart = $vOpen;
                }
                if ($v->global_closing_time) {
                    $vClose = \Carbon\Carbon::parse($v->global_closing_time);
                    if ($empEnd->gt($vClose)) $empEnd = $vClose;
                }

                $startDt = $nowDt->copy()->setTimeFrom($empStart);
                $endDt   = $nowDt->copy()->setTimeFrom($empEnd);

                // Requirement 3: 2-hour rule for appointments
                if ($v->appointment_mode === 'appointment') {
                    $effectiveStartDt = $startDt->copy()->subHours(2);
                    $effectiveEndDt = $endDt;
                } else {
                    $effectiveStartDt = $startDt;
                    $effectiveEndDt = $endDt;
                }
                
                // Handle cases where shift starts but hasn't ended (e.g. cross midnight)
                if ($effectiveStartDt->gt($effectiveEndDt)) {
                    if ($nowDt->gte($effectiveStartDt) || $nowDt->lte($effectiveEndDt)) {
                        $hasAvailableEmployee = true;
                        break;
                    }
                } else {
                    if ($nowDt->between($effectiveStartDt, $effectiveEndDt)) {
                        $hasAvailableEmployee = true;
                        break;
                    }
                }
            }

            $v->is_currently_open = $hasAvailableEmployee;
            return $v;
        });

        $allThemes = ThemeService::getAllThemes();

        return view('customer.vendors', compact('vendors', 'allThemes'));
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

            $empStart = \Carbon\Carbon::parse($emp->working_start_time);
            $empEnd   = \Carbon\Carbon::parse($emp->working_end_time);

            // Bound by Vendor's Global Times if set
            if ($vendor->global_opening_time) {
                $vStart = \Carbon\Carbon::parse($vendor->global_opening_time);
                if ($empStart->lt($vStart)) $empStart = $vStart;
            }
            if ($vendor->global_closing_time) {
                $vEnd = \Carbon\Carbon::parse($vendor->global_closing_time);
                if ($empEnd->gt($vEnd)) $empEnd = $vEnd;
            }

            $startDt = $nowDt->copy()->setTimeFrom($empStart);
            $endDt   = $nowDt->copy()->setTimeFrom($empEnd);

            if ($vendor->appointment_mode === 'appointment') {
                $effectiveStartDt = $startDt->copy()->subHours(2);
                $effectiveEndDt = $endDt;
            } else {
                $effectiveStartDt = $startDt;
                $effectiveEndDt = $endDt;
            }

            if ($effectiveStartDt->gt($effectiveEndDt)) {
                $emp->is_available = ($nowDt->gte($effectiveStartDt) || $nowDt->lte($effectiveEndDt));
            } else {
                $emp->is_available = $nowDt->between($effectiveStartDt, $effectiveEndDt);
            }
        }

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

            if ($vendor->appointment_mode === 'appointment') {
                $windowOpensAt = $opensAtToday->copy()->subHours(2);
                $isOffline = $now->lt($windowOpensAt) || $now->gt($closesAtToday);
            } else {
                $isOffline = $now->lt($opensAtToday) || $now->gt($closesAtToday);
            }
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

        // Visibility / Window Logic
        if ($vendor->appointment_mode === 'appointment') {
            $windowOpensAt = $opensAtToday->copy()->subHours(2);
            $isOffline = $now->lt($windowOpensAt) || $now->gt($closesAtToday);
        } else {
            $isOffline = $now->lt($opensAtToday) || $now->gt($closesAtToday);
        }

        if ($isOffline) {
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
            'queue_index' => $queueIndex ?? 0,
            'running_token' => $runningToken ?? 0
        ]);
    }
}
