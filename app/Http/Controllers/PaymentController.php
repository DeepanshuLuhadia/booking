<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\Vendor;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService)
    {
    }

    public function show()
    {
        $user = Auth::user();
        if (!$user || !$user->isVendor()) {
            return redirect('/');
        }

        $vendor = $user->vendor;
        // Only skip checkout when there's genuinely nothing to pay for — a
        // vendor whose window has already lapsed must always be able to
        // reach this page again to renew, whatever their approval status is.
        if ($vendor->hasValidSubscriptionWindow()) {
            return redirect()->route('vendor.dashboard');
        }

        $plan = $vendor->subscriptionPlan;

        // A lapsed Free Trial has nothing to "renew" — Razorpay can't create
        // a ₹0 subscription, so send them to actually pick a paid plan
        // instead of hitting a confusing gateway error here.
        if (!$plan || $plan->price == 0) {
            return redirect()->route('vendor.plans')->with('success', 'Your free trial has ended — choose a plan below to continue.');
        }

        if (!$this->paymentService->isConfigured()) {
            return view('auth.payment', [
                'vendor' => $vendor,
                'plan' => $plan,
                'error' => 'Razorpay API keys are not configured. Please add RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET to your .env file.',
                'demoMode' => true,
            ]);
        }

        $subscription = $this->paymentService->createSubscription($vendor, $plan);

        if (!$subscription) {
            return view('auth.payment', [
                'vendor' => $vendor,
                'plan' => $plan,
                'error' => 'Razorpay Gateway Error: could not start the subscription.',
                'demoMode' => true,
            ]);
        }

        $vendor->update([
            'razorpay_subscription_id' => $subscription->id,
            'razorpay_subscription_status' => $subscription->status,
        ]);

        return view('auth.payment', [
            'vendor' => $vendor,
            'plan' => $plan,
            'subscription' => $subscription,
            'keyId' => config('services.razorpay.key'),
            'demoMode' => false,
        ]);
    }

    /**
     * Handle subscription plan upgrade / checkout for existing active vendors.
     * A plan change means a new autopay mandate at the new price — the old
     * one (if any) is cancelled outright so the vendor is never billed twice.
     */
    public function planCheckout(Request $request, SubscriptionPlan $plan)
    {
        $user = Auth::user();
        if (!$user || !$user->isVendor()) return redirect('/');

        $vendor = $user->vendor;

        if (!$this->paymentService->isConfigured()) {
            return view('vendor.subscription.payment', [
                'vendor' => $vendor,
                'plan' => $plan,
                'error' => 'Razorpay API keys are not configured.',
                'demoMode' => true,
            ]);
        }

        if (filled($vendor->razorpay_subscription_id) && $vendor->razorpay_subscription_status !== 'cancelled') {
            $this->paymentService->cancelSubscription($vendor, atCycleEnd: false);
        }

        $subscription = $this->paymentService->createSubscription($vendor, $plan);

        if (!$subscription) {
            return view('vendor.subscription.payment', [
                'vendor' => $vendor,
                'plan' => $plan,
                'error' => 'Payment gateway error: could not start the subscription.',
                'demoMode' => true,
            ]);
        }

        $vendor->update([
            'razorpay_subscription_id' => $subscription->id,
            'razorpay_subscription_status' => $subscription->status,
        ]);

        return view('vendor.subscription.payment', [
            'vendor' => $vendor,
            'plan' => $plan,
            'subscription' => $subscription,
            'keyId' => config('services.razorpay.key'),
            'demoMode' => false,
        ]);
    }

    /**
     * Handle Razorpay callback for plan upgrade.
     */
    public function planCallback(Request $request)
    {
        try {
            Log::info('Plan Upgrade Callback', $request->except('razorpay_signature'));

            $user = Auth::user();
            if (!$user) return redirect()->route('login')->with('error', 'Session expired.');

            $vendor = $user->vendor;
            if (!$vendor) return redirect('/')->with('error', 'Invalid account type.');

            $plan = SubscriptionPlan::find($request->input('plan_id'));

            if (!$request->has('razorpay_payment_id') || !$plan) {
                return redirect()->route('vendor.plans')->with('error', 'Payment failed or was cancelled.');
            }

            // Demo mode (no gateway configured): there's no real mandate to
            // check, so keep the pre-existing "simulate" testing flow working.
            if (!$this->paymentService->isConfigured()) {
                $this->paymentService->activateDemoSubscription($vendor, $plan);
                return redirect()->route('vendor.plans')->with('success', "Successfully upgraded to {$plan->name} plan!");
            }

            $verified = $request->has('razorpay_subscription_id') && $this->paymentService->verifySubscriptionSignature(
                $request->input('razorpay_subscription_id'),
                $request->input('razorpay_payment_id'),
                $request->input('razorpay_signature')
            );

            if (!$verified) {
                Log::error('Plan Upgrade Callback: signature verification failed.', [
                    'vendor_id' => $vendor->id,
                    'razorpay_subscription_id' => $request->input('razorpay_subscription_id'),
                ]);
                return redirect()->route('vendor.plans')->with('error', 'We could not verify that payment. If money was deducted, please contact support.');
            }

            // Idempotent: if the webhook already applied this charge, this is a no-op.
            $this->paymentService->applySubscriptionCharge($vendor, $plan, $request->input('razorpay_payment_id'), null, (float) $plan->price);
            $this->paymentService->markSubscriptionStatus($vendor, 'active');

            return redirect()->route('vendor.plans')->with('success', "Successfully upgraded to {$plan->name} plan! Auto-renewal is now active.");
        } catch (\Throwable $e) {
            Log::error('Plan Upgrade Callback Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('vendor.plans')->with('error', 'An error occurred during payment processing. Please contact support if money was deducted.');
        }
    }

    public function callback(Request $request)
    {
        try {
            Log::info('Razorpay Callback Received', $request->except('razorpay_signature'));

            $user = Auth::user();
            if (!$user) {
                Log::error('Razorpay Callback: No authenticated user found.');
                return redirect()->route('login')->with('error', 'Session expired. Please login again.');
            }

            $vendor = $user->vendor;
            if (!$vendor) {
                Log::error('Razorpay Callback: User has no associated vendor record.');
                return redirect('/')->with('error', 'Invalid account type.');
            }

            if (!$request->has('razorpay_payment_id')) {
                Log::warning('Razorpay Callback hit without payment_id.', $request->all());
                return redirect()->route('payment.razorpay')->with('error', 'Payment failed or was cancelled. Please try again.');
            }

            $plan = $vendor->subscriptionPlan;

            // Demo mode (no gateway configured): there's no real mandate to
            // check, so keep the pre-existing "simulate" testing flow working.
            if (!$this->paymentService->isConfigured()) {
                $this->paymentService->activateDemoSubscription($vendor, $plan);
            } else {
                $verified = $request->has('razorpay_subscription_id') && $this->paymentService->verifySubscriptionSignature(
                    $request->input('razorpay_subscription_id'),
                    $request->input('razorpay_payment_id'),
                    $request->input('razorpay_signature')
                );

                if (!$verified) {
                    Log::error('Razorpay Callback: signature verification failed.', [
                        'vendor_id' => $vendor->id,
                        'razorpay_subscription_id' => $request->input('razorpay_subscription_id'),
                    ]);
                    return redirect()->route('payment.razorpay')->with('error', 'We could not verify that payment. If money was deducted, please contact support.');
                }

                // Idempotent: if the webhook already applied this charge, this is a no-op.
                $this->paymentService->applySubscriptionCharge($vendor, $plan, $request->input('razorpay_payment_id'), null, (float) $plan->price);
                $this->paymentService->markSubscriptionStatus($vendor, 'active');
            }

            $vendor->refresh();
            $msg = $vendor->status === 'pending'
                ? 'Payment successful! Auto-renewal is set up. Your account is now awaiting admin approval before going live.'
                : 'Payment successful! Auto-renewal is now active — your subscription will renew itself each year.';

            return redirect()->route('vendor.dashboard')->with('success', $msg);
        } catch (\Throwable $e) {
            Log::error('Razorpay Callback Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('vendor.plans')->with('error', 'An error occurred during payment callback verification.');
        }
    }

    /**
     * Lets a vendor turn autopay off. Access isn't cut immediately — the
     * mandate stops at the end of the period already paid for, matching
     * cancelSubscription()'s default.
     */
    public function cancelAutopay(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isVendor()) return redirect('/');

        $vendor = $user->vendor;

        if (blank($vendor->razorpay_subscription_id)) {
            return back()->with('error', 'No active auto-renewal to cancel.');
        }

        if ($this->paymentService->cancelSubscription($vendor, atCycleEnd: true)) {
            return back()->with('success', 'Auto-renewal cancelled. Your plan stays active until ' . $vendor->subscription_expires_at?->format('d M Y') . '.');
        }

        return back()->with('error', 'Could not cancel auto-renewal. Please contact support.');
    }
}
