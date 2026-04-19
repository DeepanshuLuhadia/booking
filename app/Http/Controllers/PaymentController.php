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

        // Use fake keys if not set for demo purposes or ask user
        $keyId = env('RAZORPAY_KEY_ID');
        $keySecret = env('RAZORPAY_KEY_SECRET');
        
        if (empty($keyId) || empty($keySecret)) {
             return view('auth.payment', [
                'vendor' => $vendor,
                'plan' => $plan,
                'error' => 'Razorpay API keys are not configured. Please add RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET to your .env file.'
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

            $vendor->update([
                'status' => 'active',
                'subscription_expires_at' => Carbon::now()->addMonth(),
            ]);

            return redirect()->route('vendor.dashboard')->with('success', 'Payment successful! Your account is now active.');
        }

        \Log::warning('Razorpay Callback hit without payment_id.', $request->all());
        return redirect()->route('payment.razorpay')->with('error', 'Payment failed or was cancelled. Please try again.');
    }
}
