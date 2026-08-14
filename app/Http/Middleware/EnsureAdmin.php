<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts the admin panel to accounts with the admin role.
 *
 * The /admin group previously ran on `auth` alone, which meant any signed-in
 * customer, vendor or staff member could open vendor management, settlements
 * and review moderation simply by typing the URL. The platform-wide booking
 * report added alongside this exposes every shop's customer names and phone
 * numbers, so the role check had to exist before that endpoint did.
 *
 * 404 rather than 403: a non-admin has no business learning that the panel is
 * there at all.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || !$user->isAdmin()) {
            abort(404);
        }

        return $next($request);
    }
}
