<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\Vendor;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;

class PaymentService
{
    protected ?Api $api = null;

    public function __construct()
    {
        $key = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');

        if (filled($key) && filled($secret)) {
            $this->api = new Api($key, $secret);
        }
    }

    /**
     * Check if Razorpay API keys are configured.
     */
    public function isConfigured(): bool
    {
        $key = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');

        return filled($key) && filled($secret) && !str_contains((string) $key, 'YOUR_');
    }

    /**
     * One-off order creation (for non-subscription payments).
     */
    public function createOrder($amount, $receiptId, $metadata = [])
    {
        if (!$this->isConfigured() || !$this->api) {
            Log::warning('Razorpay: createOrder called without valid API keys.');
            return null;
        }

        try {
            $order = $this->api->order->create([
                'receipt'  => (string) $receiptId,
                'amount'   => (int) round($amount * 100), // amount in paise
                'currency' => 'INR',
                'notes'    => $metadata,
            ]);
            return $order;
        } catch (Exception $e) {
            Log::error('Razorpay Order Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify one-off payment signature.
     */
    public function verifyPayment($razorpayOrderId, $razorpayPaymentId, $razorpaySignature): bool
    {
        if (!$this->isConfigured() || !$this->api) {
            return false;
        }

        try {
            $attributes = [
                'razorpay_order_id'   => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature'  => $razorpaySignature,
            ];
            $this->api->utility->verifyPaymentSignature($attributes);
            return true;
        } catch (Exception $e) {
            Log::error('Razorpay Verification Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create Razorpay Subscription for a vendor on a given plan.
     */
    public function createSubscription(Vendor $vendor, SubscriptionPlan $plan)
    {
        if (!$this->isConfigured() || !$this->api) {
            Log::warning('Razorpay: createSubscription called but service is not configured.');
            return null;
        }

        try {
            // Ensure plan exists in Razorpay
            $razorpayPlanId = $this->ensureRazorpayPlanId($plan);

            if (!$razorpayPlanId) {
                Log::error("Razorpay: Could not get or create Razorpay plan for SubscriptionPlan #{$plan->id}");
                return null;
            }

            // Create Razorpay Subscription
            $period = strtolower($plan->billing_period ?? 'yearly');
            $totalCount = ($period === 'monthly') ? 120 : 10;

            $subscription = $this->api->subscription->create([
                'plan_id'         => $razorpayPlanId,
                'total_count'     => $totalCount,
                'quantity'        => 1,
                'customer_notify' => 1,
                'notes'           => [
                    'vendor_id'     => (string) $vendor->id,
                    'plan_id'       => (string) $plan->id,
                    'business_name' => (string) $vendor->business_name,
                ],
            ]);

            return $subscription;
        } catch (Exception $e) {
            Log::error('Razorpay createSubscription Error: ' . $e->getMessage(), [
                'vendor_id' => $vendor->id,
                'plan_id'   => $plan->id,
            ]);
            return null;
        }
    }

    /**
     * Cancel an active Razorpay Subscription.
     */
    public function cancelSubscription(Vendor $vendor, bool $atCycleEnd = true): bool
    {
        if (blank($vendor->razorpay_subscription_id)) {
            return false;
        }

        if (!$this->isConfigured() || !$this->api) {
            $vendor->update(['razorpay_subscription_status' => 'cancelled']);
            return true;
        }

        try {
            $sub = $this->api->subscription->fetch($vendor->razorpay_subscription_id);
            $sub->cancel(['cancel_at_cycle_end' => $atCycleEnd ? 1 : 0]);
            $vendor->update(['razorpay_subscription_status' => 'cancelled']);
            return true;
        } catch (Exception $e) {
            Log::error('Razorpay cancelSubscription Error: ' . $e->getMessage(), [
                'vendor_id' => $vendor->id,
                'subscription_id' => $vendor->razorpay_subscription_id,
            ]);
            if (str_contains(strtolower($e->getMessage()), 'cancelled')) {
                $vendor->update(['razorpay_subscription_status' => 'cancelled']);
                return true;
            }
            return false;
        }
    }

    /**
     * Verify subscription payment signature.
     */
    public function verifySubscriptionSignature(?string $subscriptionId, ?string $paymentId, ?string $signature): bool
    {
        if (blank($subscriptionId) || blank($paymentId) || blank($signature) || !$this->isConfigured() || !$this->api) {
            return false;
        }

        try {
            $attributes = [
                'razorpay_subscription_id' => $subscriptionId,
                'razorpay_payment_id'      => $paymentId,
                'razorpay_signature'       => $signature,
            ];
            $this->api->utility->verifySubscriptionSignature($attributes);
            return true;
        } catch (Exception $e) {
            Log::error('Razorpay verifySubscriptionSignature Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify webhook signature.
     */
    public function verifyWebhookSignature(string $rawBody, ?string $signature): bool
    {
        $secret = config('services.razorpay.webhook_secret');
        if (blank($secret) || blank($signature) || !$this->api) {
            return false;
        }

        try {
            $this->api->utility->verifyWebhookSignature($rawBody, $signature, $secret);
            return true;
        } catch (Exception $e) {
            Log::warning('Razorpay verifyWebhookSignature Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Apply subscription charge (idempotent), updating vendor dates and recording payment.
     */
    public function applySubscriptionCharge(Vendor $vendor, SubscriptionPlan $plan, ?string $paymentId, ?string $orderId = null, float $amount = 0.0): bool
    {
        if (blank($paymentId)) {
            Log::warning("Razorpay: applySubscriptionCharge called without paymentId for vendor #{$vendor->id}");
            return false;
        }

        $exists = Payment::where('razorpay_payment_id', $paymentId)->exists();
        if ($exists) {
            Log::info("Razorpay: Subscription payment {$paymentId} already processed for vendor #{$vendor->id}");
            return false;
        }

        $currentExpiry = $vendor->subscription_expires_at;
        $baseDate = ($currentExpiry && $currentExpiry->isFuture()) ? $currentExpiry : now();

        $period = strtolower($plan->billing_period ?? 'yearly');
        $newExpiry = ($period === 'monthly') ? $baseDate->copy()->addMonth() : $baseDate->copy()->addYear();

        $isPaid = $plan->price > 0;

        $vendor->update([
            'subscription_plan_id'         => $plan->id,
            'subscription_expires_at'      => $newExpiry,
            'razorpay_subscription_status' => 'active',
            'is_verified'                  => $isPaid ? true : $vendor->is_verified,
        ]);

        Payment::create([
            'vendor_id'           => $vendor->id,
            'customer_id'         => $vendor->user_id,
            'amount'              => $amount > 0 ? $amount : (float) $plan->price,
            'payment_type'        => 'subscription',
            'razorpay_order_id'   => $orderId,
            'razorpay_payment_id' => $paymentId,
            'status'              => 'success',
            'metadata'            => [
                'plan_id'   => $plan->id,
                'plan_name' => $plan->name,
            ],
        ]);

        return true;
    }

    /**
     * Activate a demo subscription (used when Razorpay keys are not configured or bypass clicked).
     */
    public function activateDemoSubscription(Vendor $vendor, SubscriptionPlan $plan): void
    {
        $newExpiry = now()->addYear();
        $isPaid = $plan->price > 0;

        $vendor->update([
            'subscription_plan_id'         => $plan->id,
            'subscription_expires_at'      => $newExpiry,
            'razorpay_subscription_status' => 'active',
            'is_verified'                  => $isPaid ? true : $vendor->is_verified,
        ]);

        Payment::create([
            'vendor_id'           => $vendor->id,
            'customer_id'         => $vendor->user_id,
            'amount'              => (float) $plan->price,
            'payment_type'        => 'subscription_demo',
            'razorpay_order_id'   => 'demo_order_' . time(),
            'razorpay_payment_id' => 'demo_pay_' . time(),
            'status'              => 'success',
            'metadata'            => [
                'plan_id'   => $plan->id,
                'plan_name' => $plan->name,
                'demo'      => true,
            ],
        ]);
    }

    /**
     * Update vendor's razorpay_subscription_status.
     */
    public function markSubscriptionStatus(Vendor $vendor, string $status): void
    {
        $vendor->update([
            'razorpay_subscription_status' => $status,
        ]);
    }

    /**
     * Grant free access until a specific date for vendors without active paid autopay.
     */
    public function grantFreeAccess(Vendor $vendor, Carbon $until): bool
    {
        if (filled($vendor->razorpay_subscription_id) && $vendor->razorpay_subscription_status === 'active') {
            return false;
        }

        $vendor->update([
            'subscription_expires_at' => $until,
        ]);

        return true;
    }

    /**
     * End free access immediately.
     */
    public function endFreeAccessNow(Vendor $vendor): void
    {
        $vendor->update([
            'subscription_expires_at' => now(),
        ]);
    }

    /**
     * Ensure a Razorpay Plan ID exists for a SubscriptionPlan model, creating one if missing.
     */
    protected function ensureRazorpayPlanId(SubscriptionPlan $plan): ?string
    {
        if (filled($plan->razorpay_plan_id)) {
            return $plan->razorpay_plan_id;
        }

        try {
            $period = strtolower($plan->billing_period ?? 'yearly');
            $periodUnit = match ($period) {
                'monthly' => 'monthly',
                'weekly'  => 'weekly',
                'daily'   => 'daily',
                default   => 'yearly',
            };

            $razorpayPlan = $this->api->plan->create([
                'period'   => $periodUnit,
                'interval' => 1,
                'item'     => [
                    'name'        => $plan->name,
                    'amount'      => (int) round($plan->price * 100), // paise
                    'currency'    => 'INR',
                    'description' => "{$plan->name} Plan Subscription",
                ],
            ]);

            $plan->update(['razorpay_plan_id' => $razorpayPlan->id]);

            return $razorpayPlan->id;
        } catch (Exception $e) {
            Log::error('Razorpay ensureRazorpayPlanId Error: ' . $e->getMessage(), ['plan_id' => $plan->id]);
            return null;
        }
    }
}
