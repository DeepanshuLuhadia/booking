<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectRoleAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isAdmin()) {
                return redirect('/admin/dashboard');
            } elseif ($user->isVendor()) {
                return redirect('/vendor/dashboard');
            } elseif ($user->isEmployee()) {
                return redirect('/employee/dashboard');
            }
            return redirect('/');
        }

        return $next($request);
    }
}
