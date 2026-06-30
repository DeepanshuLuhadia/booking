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
            if (is_null($user->mobile_verified_at)) {
                return redirect()->route('otp.verify');
            }

            $vendor = $user->vendor;

            // Gate on a valid subscription window, not approval status, so a
            // 'pending' vendor can still set up while awaiting admin approval.
            if (!$vendor || !$vendor->hasValidSubscriptionWindow()) {
                if (!$request->is('payment*') && !$request->is('vendor/plans*')) {
                    return redirect()->route('payment.razorpay');
                }
            }
        }

        return $next($request);
    }
}
