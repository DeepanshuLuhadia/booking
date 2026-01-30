<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::with('user', 'subscriptionPlan')->latest()->get();
        return view('admin.vendors.index', compact('vendors'));
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
}
