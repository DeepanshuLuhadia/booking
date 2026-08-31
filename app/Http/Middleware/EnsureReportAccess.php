<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the vendor booking-reports section on the shop's plan.
 *
 * Reports are for free-trial shops and Premium subscribers; Basic and Standard
 * are sent to the plans page. The rule itself lives on the model
 * (Vendor::hasReportAccess) so the nav, the controller and this middleware
 * cannot drift apart — hiding the link is presentation, this is the gate.
 */
class EnsureReportAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $vendor = auth()->user()?->vendor;

        if (!$vendor || !$vendor->hasReportAccess()) {
            return redirect()
                ->route('vendor.plans')
                ->with('error', 'Booking reports are available on the Premium plan. Upgrade to export your appointment history.');
        }

        return $next($request);
    }
}
