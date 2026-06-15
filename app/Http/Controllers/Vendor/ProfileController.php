<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $vendor = auth()->user()->vendor;
        return view('vendor.profile', compact('vendor'));
    }

    public function update(Request $request)
    {
        $vendor = auth()->user()->vendor;

        $request->validate([
            'business_name' => 'sometimes|required|string|max:255',
            'contact_number' => 'sometimes|required|string|max:20',
            'show_contact_number' => 'nullable|boolean',
            'address' => 'sometimes|required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'shop_photo' => 'nullable|image|max:2048',
            'upi_id' => 'nullable|string|max:255',
            'vendor_type' => 'nullable|in:doctor,barber,activity,training,consultant',
            'appointment_mode' => 'nullable|in:time_slot,token',
            'global_opening_time' => 'nullable|date_format:H:i',
            'global_closing_time' => 'nullable|date_format:H:i',
        ]);

        $data = $request->except(['shop_photo', 'token_amount', 'service_fee', 'emergency_fee', 'avg_consultation_time']);
        $data['show_contact_number'] = $request->has('show_contact_number') ? 1 : 0;
        
        if ($request->hasFile('shop_photo')) {
            if ($vendor->shop_photo) {
                Storage::disk('public')->delete($vendor->shop_photo);
            }
            $data['shop_photo'] = $request->file('shop_photo')->store('shops', 'public');
        }

        $vendor->update($data);

        return back()->with('success', 'Profile updated successfully!');
    }

    public function plans()
    {
        $vendor = auth()->user()->vendor;
        $plans = \App\Models\SubscriptionPlan::where('is_active', true)->where('price', '>', 0)->get();
        return view('vendor.subscription.plans', compact('vendor', 'plans'));
    }

    public function toggleStatus()
    {
        $vendor = auth()->user()->vendor;

        if (!$vendor->isProfileComplete()) {
            return back()->with('error', 'Please complete your global settings before activating the shop.');
        }

        $vendor->update(['is_open' => !$vendor->is_open]);
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'is_open' => $vendor->is_open]);
        }

        return back()->with('success', 'Shop status updated to ' . ($vendor->is_open ? 'OPEN' : 'CLOSED'));
    }
}
