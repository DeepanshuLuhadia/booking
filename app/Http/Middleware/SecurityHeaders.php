<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds the browser-enforced security headers the app was missing (flagged by
 * external header/CSP scanners): clickjacking protection, MIME sniffing
 * protection, HSTS, a real Content-Security-Policy, and a locked-down
 * Permissions-Policy.
 *
 * The CSP allowlist below is not a starting-from-scratch strict policy — it
 * is built from what the app actually loads today (Alpine.js inline
 * expressions, Firebase Cloud Messaging, Google Sign-In, Razorpay checkout,
 * YouTube/Google Maps embeds, Google Fonts) so that turning this on does not
 * break any of those integrations. 'unsafe-inline'/'unsafe-eval' stay in
 * script-src because the views rely heavily on inline <script> blocks, inline
 * event handlers, and Alpine's runtime expression evaluation; removing them
 * would require a broader nonce/refactor pass across the Blade views.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://accounts.google.com https://checkout.razorpay.com https://www.gstatic.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: blob: https:",
            "connect-src 'self' https: wss:",
            // api.razorpay.com is where Checkout.js actually opens the payment
            // modal iframe; checkout.razorpay.com is only the script host.
            "frame-src 'self' https://www.youtube.com https://www.google.com https://maps.google.com https://accounts.google.com https://checkout.razorpay.com https://api.razorpay.com",
            "frame-ancestors 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self' https://checkout.razorpay.com",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(self), camera=(), microphone=(), payment=(self), usb=(), fullscreen=(self)'
        );

        // Only promise HTTPS-only access when the request actually arrived
        // over HTTPS, so local http:// development is never told to upgrade.
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
