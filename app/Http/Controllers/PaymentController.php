<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        if (!$user || !$user->isVendor()) {
            return redirect('/');
        }

        $vendor = $user->vendor;
        if ($vendor->status === 'active') {
            return redirect()->route('vendor.dashboard');
        }

        $plan = $vendor->subscriptionPlan;

        $keyId = env('RAZORPAY_KEY_ID');
        $keySecret = env('RAZORPAY_KEY_SECRET');
        
        if (empty($keyId) || empty($keySecret)) {
             return view('auth.payment', [
                'vendor' => $vendor,
                'plan' => $plan,
                'error' => 'Razorpay API keys are not configured. Please add RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET to your .env file.',
                'demoMode' => true
            ]);
        }
        
        try {
            $api = new Api($keyId, $keySecret);

            $order = $api->order->create([
                'receipt' => 'rcpt_' . $vendor->id,
                'amount' => (int)($plan->price * 100),
                'currency' => 'INR'
            ]);

            return view('auth.payment', [
                'vendor' => $vendor,
                'plan' => $plan,
                'order' => $order,
                'keyId' => $keyId,
                'demoMode' => false
            ]);
        } catch (\Exception $e) {
            \Log::error('Razorpay Error: ' . $e->getMessage());
            return view('auth.payment', [
                'vendor' => $vendor,
                'plan' => $plan,
                'error' => 'Razorpay Gateway Error: ' . $e->getMessage(),
                'demoMode' => true
            ]);
        }
    }

    /**
     * Handle subscription plan upgrade / checkout for existing active vendors.
     */
    public function planCheckout(Request $request, \App\Models\SubscriptionPlan $plan)
    {
        $user = Auth::user();
        if (!$user || !$user->isVendor()) return redirect('/');

        $vendor = $user->vendor;
        $keyId = env('RAZORPAY_KEY_ID');
        $keySecret = env('RAZORPAY_KEY_SECRET');

        if (empty($keyId) || empty($keySecret)) {
            return view('vendor.subscription.payment', [
                'vendor' => $vendor,
                'plan' => $plan,
                'error' => 'Razorpay API keys are not configured.',
                'demoMode' => true,
            ]);
        }

        try {
            $api = new Api($keyId, $keySecret);
            $order = $api->order->create([
                'receipt'  => 'upgrade_' . $vendor->id . '_' . $plan->id,
                'amount'   => (int)($plan->price * 100),
                'currency' => 'INR',
            ]);

            return view('vendor.subscription.payment', [
                'vendor' => $vendor,
                'plan'   => $plan,
                'order'  => $order,
                'keyId'  => $keyId,
                'demoMode' => false,
            ]);
        } catch (\Exception $e) {
            \Log::error('Plan Upgrade Razorpay Error: ' . $e->getMessage());
            return view('vendor.subscription.payment', [
                'vendor'   => $vendor,
                'plan'     => $plan,
                'error'    => 'Payment gateway error: ' . $e->getMessage(),
                'demoMode' => true,
            ]);
        }
    }

    /**
     * Handle Razorpay callback for plan upgrade.
     */
    public function planCallback(Request $request)
    {
        \Log::info('Plan Upgrade Callback', $request->all());

        $user = Auth::user();
        if (!$user) return redirect()->route('login')->with('error', 'Session expired.');

        $vendor = $user->vendor;
        $planId = $request->input('plan_id');
        $plan = \App\Models\SubscriptionPlan::find($planId);

        if ($request->has('razorpay_payment_id') && $plan) {
            // Carry over remaining days only when upgrading FROM the Free Trial plan.
            // Paid plan purchases/upgrades always start fresh from now().
            $currentPlan = $vendor->subscriptionPlan;
            $isFromFreeTrial = $currentPlan && $currentPlan->price == 0
                && $vendor->subscription_expires_at && $vendor->subscription_expires_at->isFuture();

            $baseDate = $isFromFreeTrial ? $vendor->subscription_expires_at : Carbon::now();

            $vendor->update([
                'subscription_plan_id'    => $plan->id,
                'status'                  => 'active',
                'subscription_expires_at' => $baseDate->copy()->addYear(),
            ]);
            return redirect()->route('vendor.plans')->with('success', "Successfully upgraded to {$plan->name} plan!");
        }

        return redirect()->route('vendor.plans')->with('error', 'Payment failed or was cancelled.');
    }

    public function callback(Request $request)
    {
        \Log::info('Razorpay Callback Received', $request->all());

        $user = Auth::user();
        if (!$user) {
            \Log::error('Razorpay Callback: No authenticated user found.');
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $vendor = $user->vendor;
        if (!$vendor) {
            \Log::error('Razorpay Callback: User has no associated vendor record.');
            return redirect('/')->with('error', 'Invalid account type.');
        }

        if ($request->has('razorpay_payment_id')) {
            \Log::info('Razorpay Payment Success Data Received. Updating vendor status.', [
                'vendor_id' => $vendor->id,
                'payment_id' => $request->razorpay_payment_id
            ]);

            $plan = $vendor->subscriptionPlan;

            if ($plan && $plan->price > 0) {
                // Paid plan — always 1 year from now (fresh start)
                $newExpiry = Carbon::now()->addYear();
            } else {
                // Free Trial — always 1 month from now
                $newExpiry = Carbon::now()->addMonth();
            }

            // A brand-new vendor stays 'pending' until an admin approves them;
            // payment only establishes their subscription window. Existing
            // (already-approved) vendors renewing stay active.
            $updates = ['subscription_expires_at' => $newExpiry];
            if ($vendor->status !== 'pending') {
                $updates['status'] = 'active';
            }
            $vendor->update($updates);

            $msg = $vendor->status === 'pending'
                ? 'Payment successful! Your account is now awaiting admin approval before going live.'
                : 'Payment successful! Your account is now active.';

            return redirect()->route('vendor.dashboard')->with('success', $msg);
        }

        \Log::warning('Razorpay Callback hit without payment_id.', $request->all());
        return redirect()->route('payment.razorpay')->with('error', 'Payment failed or was cancelled. Please try again.');
    }
}
