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
            'business_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'mobile' => 'required|string|max:15|unique:users',
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
            'status' => $isFreePlan ? 'active' : 'inactive',
        ]);

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'vendor_type' => $request->vendor_type,
            'business_name' => $request->business_name,
            'owner_name' => $request->owner_name,
            'contact_number' => $request->mobile,
            'subscription_plan_id' => $plan->id,
            'referred_by_id' => $referrer ? $referrer->id : null,
            'status' => $isFreePlan ? 'active' : 'inactive',
            'subscription_expires_at' => $isFreePlan ? now()->addMonth() : null,
        ]);

        // Logic for awarding points: The user said "once refer purchase any plan".
        // In this demo, activation happens via admin or simulated payment.
        // I will add a logic to award points when the vendor status is changed to active if they were referred.
        // Since activation might happen later, I'll put a listener or check in Vendor model update?
        // Or for this demo, I'll award immediately if activated, but usually it's on subscription update.
        // Let's create a hook or add it where activation happens.

        Auth::login($user);
        
        if ($isFreePlan) {
            return redirect()->route('vendor.dashboard')->with('success', 'Your account has been registered with the Free Trial plan!');
        }
        
        return redirect()->route('payment.razorpay');
    }
}
