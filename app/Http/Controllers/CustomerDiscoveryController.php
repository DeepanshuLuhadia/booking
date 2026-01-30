<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Employee;
use App\Services\SlotGenerationService;
use Illuminate\Http\Request;

class CustomerDiscoveryController extends Controller
{
    public function index(Request $request)
    {
        $lat = $request->lat;
        $lng = $request->lng;
        $search = $request->search;
        $sort = $request->sort;

        $query = Vendor::where('status', 'active')->where('is_open', true);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('business_name', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%")
                  ->orWhere('owner_name', 'LIKE', "%{$search}%");
            });
        }

        // Handle Sorting
        if ($sort === 'newest') {
            $query->latest();
        } elseif ($sort === 'rating') {
            // Since we don't have a ratings table yet, we'll use ID or random for demo consistency
            $query->orderBy('id', 'desc');
        }

        $vendors = $query->get();

        if ($sort === 'distance' && $lat && $lng) {
            $vendors = $vendors->sortBy(function($vendor) use ($lat, $lng) {
                return sqrt(pow($vendor->latitude - $lat, 2) + pow($vendor->longitude - $lng, 2));
            });
        }

        return view('customer.vendors', compact('vendors'));
    }

    public function show(Vendor $vendor, SlotGenerationService $slotService)
    {
        $vendor->load('employees');
        
        // Default to first employee if exists, otherwise empty slots
        $selectedEmployee = $vendor->employees()->where('is_active', true)->first();
        $slots = $selectedEmployee ? $slotService->generateSlots($selectedEmployee) : [];

        return view('customer.vendor-details', compact('vendor', 'selectedEmployee', 'slots'));
    }

    public function getSlots(Vendor $vendor, Employee $employee, SlotGenerationService $slotService)
    {
        $slots = $slotService->generateSlots($employee);
        return response()->json($slots);
    }
}
