<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');

        $vendors = Vendor::with('user', 'subscriptionPlan')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();

        $pendingCount = Vendor::where('status', 'pending')->count();

        return view('admin.vendors.index', compact('vendors', 'status', 'pendingCount'));
    }

    public function show(Vendor $vendor)
    {
        $vendor->load(['user', 'subscriptionPlan', 'employees', 'settlements' => function($query) {
            $query->latest()->limit(5);
        }]);
        return view('admin.vendors.show', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $vendor->update($request->only('status'));
        return back()->with('success', 'Vendor status updated');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return back()->with('success', 'Vendor deleted');
    }

    /**
     * Approve a pending vendor and take it live.
     * The verified badge is granted only to paid (premium) plan holders.
     */
    public function approve(Vendor $vendor)
    {
        $isPaidPlan = $vendor->subscriptionPlan && $vendor->subscriptionPlan->price > 0;

        $vendor->update([
            'status'      => 'active',
            'is_verified' => $isPaidPlan,
        ]);
        $vendor->user?->update(['status' => 'active']);

        return back()->with('success', "Vendor '{$vendor->business_name}' approved.");
    }

    public function reject(Vendor $vendor)
    {
        $vendor->update(['status' => 'rejected', 'is_open' => false]);
        $vendor->user?->update(['status' => 'inactive']);

        return back()->with('success', "Vendor '{$vendor->business_name}' rejected.");
    }

    public function suspend(Vendor $vendor)
    {
        $vendor->update(['status' => 'suspended', 'is_open' => false]);

        return back()->with('success', "Vendor '{$vendor->business_name}' suspended.");
    }

    public function reinstate(Vendor $vendor)
    {
        // Re-verify only if they hold a paid plan.
        $isPaidPlan = $vendor->subscriptionPlan && $vendor->subscriptionPlan->price > 0;

        $vendor->update(['status' => 'active', 'is_verified' => $isPaidPlan]);
        $vendor->user?->update(['status' => 'active']);

        return back()->with('success', "Vendor '{$vendor->business_name}' reinstated.");
    }
}
