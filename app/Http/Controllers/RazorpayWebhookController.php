<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\Vendor;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Server-to-server Razorpay webhook — the reliable half of vendor
 * subscription auto-renewal (UPI Autopay / e-mandate). Configure in
 * Razorpay Dashboard -> Webhooks:
 *   URL:    <APP_URL>/api/webhooks/razorpay
 *   Secret: RAZORPAY_WEBHOOK_SECRET (see .env.example)
 *   Events: subscription.charged, subscription.activated,
 *           subscription.pending, subscription.halted,
 *           subscription.cancelled, subscription.completed
 *
 * subscription.charged is the one that actually matters for renewal — it
 * fires every time Razorpay successfully auto-charges the vendor for the
 * next period, with no action needed from them or from us beyond recording
 * it. The others just track the mandate's health so a failing/cancelled
 * autopay is visible (razorpay_subscription_status) rather than silent;
 * access itself is still governed entirely by subscription_expires_at.
 */
class RazorpayWebhookController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected NotificationService $notificationService,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $rawBody = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');

        if (!$this->paymentService->verifyWebhookSignature($rawBody, $signature)) {
            Log::warning('Razorpay webhook: signature verification failed.', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['status' => 'invalid signature'], 400);
        }

        $data = json_decode($rawBody, true);
        $event = $data['event'] ?? null;

        try {
            match ($event) {
                'subscription.charged' => $this->handleCharged($data),
                'subscription.activated' => $this->handleStatus($data, 'active'),
                'subscription.authenticated' => $this->handleStatus($data, 'authenticated'),
                'subscription.pending' => $this->handlePending($data),
                'subscription.halted' => $this->handleHalted($data),
                'subscription.cancelled' => $this->handleStatus($data, 'cancelled'),
                'subscription.completed' => $this->handleStatus($data, 'completed'),
                default => Log::info('Razorpay webhook: ignoring event.', ['event' => $event]),
            };
        } catch (\Throwable $e) {
            // Unexpected failure on our side — return 5xx so Razorpay retries
            // the delivery instead of treating it as permanently handled.
            Log::error('Razorpay webhook: unhandled exception while processing event.', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['status' => 'error'], 500);
        }

        return response()->json(['status' => 'ok']);
    }

    protected function findVendor(array $data): ?Vendor
    {
        $subId = $data['payload']['subscription']['entity']['id'] ?? null;
        if (!$subId) {
            Log::warning('Razorpay webhook: event missing subscription id.', ['data' => $data]);
            return null;
        }

        $vendor = Vendor::where('razorpay_subscription_id', $subId)->first();
        if (!$vendor) {
            Log::warning('Razorpay webhook: no vendor tracked for this subscription id.', ['razorpay_subscription_id' => $subId]);
        }

        return $vendor;
    }

    protected function handleCharged(array $data): void
    {
        $vendor = $this->findVendor($data);
        if (!$vendor) {
            return;
        }

        $subEntity = $data['payload']['subscription']['entity'] ?? [];
        $payEntity = $data['payload']['payment']['entity'] ?? null;

        if (!$payEntity) {
            Log::warning('Razorpay webhook: subscription.charged missing payment entity.', ['vendor_id' => $vendor->id]);
            return;
        }

        $planId = $subEntity['notes']['plan_id'] ?? $vendor->subscription_plan_id;
        $plan = SubscriptionPlan::find($planId);
        if (!$plan) {
            Log::error('Razorpay webhook: subscription.charged but no plan could be resolved.', ['vendor_id' => $vendor->id]);
            return;
        }

        // Idempotent inside applySubscriptionCharge() on razorpay_payment_id —
        // safe even if this event, or the browser callback for the same
        // charge, is delivered more than once.
        $applied = $this->paymentService->applySubscriptionCharge(
            $vendor,
            $plan,
            $payEntity['id'],
            $payEntity['order_id'] ?? null,
            ($payEntity['amount'] ?? 0) / 100
        );

        $this->paymentService->markSubscriptionStatus($vendor, 'active');

        if ($applied) {
            $this->notificationService->sendWebPush(
                $vendor->user,
                'Subscription renewed',
                "Your {$plan->name} plan auto-renewed for another year — valid until {$vendor->subscription_expires_at?->format('d M Y')}.",
            );
        }
    }

    protected function handleStatus(array $data, string $status): void
    {
        $vendor = $this->findVendor($data);
        if (!$vendor) {
            return;
        }

        $this->paymentService->markSubscriptionStatus($vendor, $status);
    }

    /** A charge attempt failed but Razorpay will retry automatically — informational, not a cutoff. */
    protected function handlePending(array $data): void
    {
        $vendor = $this->findVendor($data);
        if (!$vendor) {
            return;
        }

        $this->paymentService->markSubscriptionStatus($vendor, 'pending');
        Log::warning('Razorpay webhook: subscription payment pending (retry in progress).', ['vendor_id' => $vendor->id]);
    }

    /** Razorpay exhausted its retries — autopay has stopped; the vendor needs to act. */
    protected function handleHalted(array $data): void
    {
        $vendor = $this->findVendor($data);
        if (!$vendor) {
            return;
        }

        $this->paymentService->markSubscriptionStatus($vendor, 'halted');
        Log::warning('Razorpay webhook: subscription halted, autopay has stopped.', ['vendor_id' => $vendor->id]);

        $this->notificationService->sendWebPush(
            $vendor->user,
            'Auto-renewal stopped',
            'We could not renew your subscription after several attempts. Please update your payment method before your plan expires.',
        );

        $this->notificationService->notifyAdmins(
            'Vendor autopay halted',
            "{$vendor->business_name}'s auto-renewal has stopped after repeated failed charges.",
            ['vendor_id' => $vendor->id]
        );
    }
}
