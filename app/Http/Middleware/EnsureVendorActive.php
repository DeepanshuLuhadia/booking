<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->isEmployee()) {
            $employee = $user->employee;
            $vendor = $employee?->vendor;

            if (!$vendor || $vendor->status !== 'active') {
                return redirect()->route('vendor.approval.pending');
            }
        }

        return $next($request);
    }
}
