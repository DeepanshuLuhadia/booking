<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Employees are staff, not customers — they have no business on the public
 * discovery/booking pages. If a signed-in employee lands on a guarded public
 * route, bounce them straight back to their panel so the employee experience
 * stays contained to /employee/*.
 */
class RedirectEmployeeToPanel
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isEmployee()) {
            return redirect()->route('employee.dashboard');
        }

        return $next($request);
    }
}
