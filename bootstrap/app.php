<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->trustProxies(at: '*');

        // Exempt location and notification cookies from encryption so JS-set
        // cookies are readable server-side. The whole user_* set is written by
        // the location consent modal in the layout with document.cookie, so it
        // is plaintext by definition — leaving it encrypted here means Laravel
        // fails to decrypt it and silently hands back null.
        $middleware->encryptCookies(except: [
            'location_granted',
            'notif_consent_decided',
            'user_lat',
            'user_lng',
            'user_state',
            'user_city',
            'user_suburb',
        ]);

        $middleware->alias([
            'subscription.active' => \App\Http\Middleware\EnsureSubscriptionActive::class,
            'redirect.role.auth' => \App\Http\Middleware\RedirectRoleAuthenticated::class,
            'employee.panel.only' => \App\Http\Middleware\RedirectEmployeeToPanel::class,
            'ensure.vendor.active' => \App\Http\Middleware\EnsureVendorActive::class,
            'admin.only' => \App\Http\Middleware\EnsureAdmin::class,
            'reports.access' => \App\Http\Middleware\EnsureReportAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
        | An upload larger than the SERVER's post_max_size.
        |
        | PHP discards the whole request body in this case — no fields, no file,
        | not even the CSRF token — so without this the customer meets a "page
        | expired" or a bare 413 for a photo they did attach, and has no idea
        | the size was the problem. Worst of all it happens on the payment proof
        | screen, i.e. after they have already sent money.
        |
        | Sent back to the same form as an ordinary validation error, naming the
        | limit and the way out.
        */
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            $limit = ini_get('upload_max_filesize') ?: '2M';

            $message = "That file is too large for the server to accept (limit: {$limit}). "
                . 'Please attach a smaller image, or submit just the transaction ID instead.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => $message], 413);
            }

            return back()->withInput($request->except('payment_screenshot'))
                ->withErrors(['payment_screenshot' => $message]);
        });

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