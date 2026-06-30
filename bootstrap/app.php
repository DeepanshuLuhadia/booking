<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->trustProxies(at: '*');

        // Exempt location_granted and notification cookies from encryption so JS-set cookies are readable server-side
        $middleware->encryptCookies(except: [
            'location_granted',
            'notif_consent_decided'
        ]);

        $middleware->alias([
            'subscription.active' => \App\Http\Middleware\EnsureSubscriptionActive::class,
            'redirect.role.auth' => \App\Http\Middleware\RedirectRoleAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Never leak stack traces / SQL to API clients. Validation and HTTP
        // exceptions keep their native JSON responses; everything else is
        // logged and returned as a generic 500.
        $exceptions->render(function (\Throwable $e, $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            if ($e instanceof \Illuminate\Validation\ValidationException
                || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                return null;
            }

            \Illuminate\Support\Facades\Log::error($e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'error'   => 'Something went wrong. Please try again.',
            ], 500);
        });
    })
    ->create();