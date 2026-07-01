<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class VendorRegistrationController extends Controller
{
    public function create()
    {
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('price', 'asc')->get();
        $vendorCategories = \App\Models\VendorCategory::all();
        return view('auth.vendor-register', compact('plans', 'vendorCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_type' => 'required|exists:vendor_categories,slug',
            'business_name' => 'required|string|min:5|max:255',
            'owner_name' => 'required|string|min:5|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'mobile' => 'required|string|max:10|min:10|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'referral_code' => 'nullable|exists:vendors,referral_code',
        ]);

        $referrer = null;
        if ($request->referral_code) {
            $referrer = Vendor::where('referral_code', $request->referral_code)->first();
        }

        $plan = SubscriptionPlan::findOrFail($request->subscription_plan_id);
        $isFreePlan = ($plan->price == 0);

        $user = User::create([
            'name' => $request->owner_name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'role' => 'vendor',
            'password' => Hash::make($request->password),
            // User stays active so they can log in and finish setup; the vendor
            // record carries the public 'pending' approval state.
            'status' => 'active',
            'fcm_token' => session('fcm_token'),
        ]);

        // ALL vendors require admin approval before going live (pending).
        // Free plans still get a subscription window so they can reach their
        // dashboard; paid plans get their window after payment.
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'vendor_type' => $request->vendor_type,
            'business_name' => $request->business_name,
            'owner_name' => $request->owner_name,
            'contact_number' => $request->mobile,
            'subscription_plan_id' => $plan->id,
            'referred_by_id' => $referrer ? $referrer->id : null,
            'status' => 'pending',
            'subscription_expires_at' => $isFreePlan ? now()->addMonth() : null,
        ]);

        // Logic for awarding points: The user said "once refer purchase any plan".
        // In this demo, activation happens via admin or simulated payment.
        // I will add a logic to award points when the vendor status is changed to active if they were referred.
        // Since activation might happen later, I'll put a listener or check in Vendor model update?
        // Or for this demo, I'll award immediately if activated, but usually it's on subscription update.
        // Let's create a hook or add it where activation happens.

        Auth::login($user);

        // When OTP is disabled, skip the mobile verification gate entirely:
        // mark the mobile as verified and continue straight into the panel.
        if (!config('otp.enabled')) {
            $user->update(['mobile_verified_at' => now()]);

            return redirect('/vendor/dashboard')
                ->with('success', 'Registration successful!');
        }

        // Mobile OTP gate: a vendor must verify their phone before reaching the
        // panel. OTP delivery is handled by Firebase phone auth on the client;
        // the code is also recorded server-side for verification.
        $otp = rand(100000, 999999);
        \App\Models\Otp::create([
            'identifier' => $user->mobile,
            'otp'        => $otp,
            'type'       => 'verification',
            'expires_at' => now()->addMinutes(5),
        ]);

        return redirect()->route('otp.verify')
            ->with('info', "Verify your mobile to finish setting up. (Simulated OTP: {$otp})");
    }
}
