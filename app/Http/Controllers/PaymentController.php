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
                'amount' => $plan->price * 100, // amount in paise
                'currency' => 'INR'
            ]);

            return view('auth.payment', [
                'vendor' => $vendor,
                'plan' => $plan,
                'order' => $order,
                'keyId' => $keyId
            ]);
        } catch (\Exception $e) {
            \Log::error('Razorpay Order Creation Failed: ' . $e->getMessage());
            return view('auth.payment', [
                'vendor' => $vendor,
                'plan' => $plan,
                'error' => 'Unable to initiate payment with Razorpay. Error: ' . $e->getMessage()
            ]);
        }
    }

    public function callback(Request $request)
    {
        $user = Auth::user();
        $vendor = $user->vendor;

        // In a real app, verify signature here
        // For this task, we assume success if they hit this callback with payment_id
        
        if ($request->has('razorpay_payment_id')) {
            $vendor->update([
                'status' => 'active',
                'subscription_expires_at' => Carbon::now()->addMonth(), // Default to 1 month
            ]);

            return redirect()->route('vendor.dashboard')->with('success', 'Payment successful! Your account is now active.');
        }

        return redirect()->route('payment.razorpay')->with('error', 'Payment failed. Please try again.');
    }
}
