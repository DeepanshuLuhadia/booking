<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->isVendor()) {
            // Mobile must be verified (OTP gate) before using the vendor panel.
            // Skipped entirely when OTP verification is disabled.
            if (config('otp.enabled') && is_null($user->mobile_verified_at)) {
                return redirect()->route('otp.verify');
            }

            $vendor = $user->vendor;

            // A vendor cannot reach the panel until an admin has approved them (status 'active').
            // Pending, rejected, and suspended vendors are held on the status screen instead.
            if (!$vendor || $vendor->status !== 'active') {
                return redirect()->route('vendor.approval.pending');
            }

            // Gate on a valid subscription window, not approval status, so a
            // 'pending' vendor can still set up while awaiting admin approval.
            if (!$vendor->hasValidSubscriptionWindow()) {
                if (!$request->is('payment*') && !$request->is('vendor/plans*')) {
                    return redirect()->route('payment.razorpay');
                }
            }

            /*
            | Approval is the beginning of setup, not the end of it.
            |
            | The moment an admin approves a shop it is held on the settings
            | screen until the details that make it usable are actually filled
            | in — the same fields Vendor::isProfileComplete() checks, which are
            | also the ones the public listing, the slot generator and the
            | booking flow all read. Without this a freshly approved vendor
            | lands on a dashboard whose every number is zero and whose shop
            | can never appear to a customer, with nothing saying why.
            |
            | It applies to every vendor however they registered — through the
            | form or through Google — because a Google sign-up supplies even
            | less: a name, an address and a phone number, and nothing else.
            |
            | The settings screen itself, the plan/payment screens and the
            | open/pause/close switch stay reachable, or the redirect would have
            | nowhere to land and an expired shop could never pay to renew.
            */
            if (!$vendor->isProfileComplete() && !$this->isSetupRoute($request)) {
                return redirect()->route('vendor.profile.edit');
            }
        }

        return $next($request);
    }

    /**
     * Routes a vendor may still reach while their business details are
     * incomplete — everything the redirect target itself needs, plus the ways
     * out that must never be blocked (paying for a plan, opening the shop).
     */
    private function isSetupRoute(Request $request): bool
    {
        return $request->routeIs('vendor.profile.*')
            || $request->routeIs('vendor.plans')
            || $request->routeIs('vendor.plan.*')
            || $request->routeIs('vendor.status.toggle')
            || $request->is('payment*');
    }
}
