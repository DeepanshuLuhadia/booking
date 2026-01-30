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
            $vendor = $user->vendor;
            
            if (!$vendor || $vendor->status !== 'active') {
                if (!$request->is('payment*')) {
                    return redirect()->route('payment.razorpay');
                }
            }
        }

        return $next($request);
    }
}
