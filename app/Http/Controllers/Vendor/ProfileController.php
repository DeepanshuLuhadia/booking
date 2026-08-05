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

        /*
        | Coordinates are mandatory: the customer listing ranks nearest-first
        | from the visitor's GPS against these, so a shop without them shows no
        | distance and sinks below every located competitor.
        |
        | A literal 0 is rejected as well as a missing value. The listing treats
        | |coord| < 0.00001 as "unset" (see CustomerDiscoveryController::
        | coordinate), so accepting a zero here would let a vendor save a figure
        | the ranking then silently ignores — the worst of both worlds.
        */
        $rejectNullIsland = function ($attribute, $value, $fail) {
            if (is_numeric($value) && abs((float) $value) < 0.00001) {
                $fail('The ' . $attribute . ' looks unset. Use "Use My Location" or enter the real coordinate.');
            }
        };

        $request->validate([
            'business_name' => 'sometimes|required|string|min:5|max:255',
            'owner_name' => 'sometimes|required|string|min:5|max:255',
            'contact_number' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                'unique:users,mobile,' . $vendor->user_id,
                'unique:vendors,contact_number,' . $vendor->id,
            ],
            'show_contact_number' => 'nullable|boolean',
            'address' => 'sometimes|required|string',
            'latitude'  => ['required', 'numeric', 'between:-90,90', $rejectNullIsland],
            'longitude' => ['required', 'numeric', 'between:-180,180', $rejectNullIsland],
            'shop_photo' => 'nullable|image|max:2048',
            'upi_id' => 'nullable|string|max:255',
            'vendor_type' => 'nullable|in:doctor,barber,activity,training,consultant',
            'appointment_mode' => 'nullable|in:time_slot,token',
            'global_opening_time' => 'nullable|date_format:H:i',
            'global_closing_time' => 'nullable|date_format:H:i',
        ], [
            'latitude.required'  => 'Shop location is required — tap "Use My Location" to fill the coordinates.',
            'longitude.required' => 'Shop location is required — tap "Use My Location" to fill the coordinates.',
            'latitude.between'   => 'Latitude must be between -90 and 90.',
            'longitude.between'  => 'Longitude must be between -180 and 180.',
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

        // Sync updates to User record
        $userUpdates = [];
        if ($request->filled('owner_name')) {
            $userUpdates['name'] = $request->owner_name;
        }
        if ($request->filled('contact_number')) {
            $userUpdates['mobile'] = $request->contact_number;
        }
        if (!empty($userUpdates)) {
            $vendor->user->update($userUpdates);
        }

        return back()->with('success', 'Profile updated successfully!');
    }

    public function plans()
    {
        $vendor = auth()->user()->vendor;
        $plans = \App\Models\SubscriptionPlan::where('is_active', true)->where('price', '>', 0)->get();
        return view('vendor.subscription.plans', compact('vendor', 'plans'));
    }

    public function toggleStatus(Request $request)
    {
        $request->validate(['type' => 'required|in:open,pause,close']);
        $vendor = auth()->user()->vendor;

        if (!$vendor->isProfileComplete()) {
            return back()->with('error', 'Please complete your global settings before activating the shop.');
        }

        match ($request->type) {
            'open'  => $vendor->update(['is_open' => true,  'bookings_paused' => false]),
            'pause' => $vendor->update(['bookings_paused' => true]),
            'close' => $vendor->update(['is_open' => false, 'bookings_paused' => false]),
        };

        // Every customer sitting on this shop's page follows the change live, and
        // closing pushes to anyone still holding a token — otherwise they wait
        // for a turn that is no longer coming.
        app(\App\Services\BookingNotifier::class)->shopStatusChanged($vendor, $request->type);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        $messages = ['open' => 'Shop is now Open.', 'pause' => 'Bookings paused.', 'close' => 'Shop closed for today.'];
        return back()->with('success', $messages[$request->type]);
    }
}
