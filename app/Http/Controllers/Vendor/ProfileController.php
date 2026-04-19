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
            'address' => 'sometimes|required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'token_booking_enabled' => 'nullable',
            'token_amount' => 'nullable|numeric|min:0',
            'shop_photo' => 'nullable|image|max:2048',
            'upi_id' => 'nullable|string|max:255',
            'service_fee' => 'nullable|numeric|min:0',
            'emergency_fee' => 'nullable|numeric|min:0',
            'vendor_type' => 'nullable|in:doctor,barber,activity,training,consultant',
            'appointment_mode' => 'nullable|in:time_slot,token',
            'avg_consultation_time' => 'nullable|integer|min:1',
        ]);

        $data = $request->except('shop_photo');
        $data['token_booking_enabled'] = $request->has('token_booking_enabled');
        
        if ($request->hasFile('shop_photo')) {
            if ($vendor->shop_photo) {
                Storage::disk('public')->delete($vendor->shop_photo);
            }
            $data['shop_photo'] = $request->file('shop_photo')->store('shops', 'public');
        }

        $vendor->update($data);

        return back()->with('success', 'Profile updated successfully!');
    }

    public function toggleStatus()
    {
        $vendor = auth()->user()->vendor;
        $vendor->update(['is_open' => !$vendor->is_open]);
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'is_open' => $vendor->is_open]);
        }

        return back()->with('success', 'Shop status updated to ' . ($vendor->is_open ? 'OPEN' : 'CLOSED'));
    }
}
