<?php

namespace App\Http\Controllers;

/**
 * Streams the vendor onboarding walkthrough clips straight from local
 * storage — no YouTube embed, no public disk. Public and unauthenticated:
 * these play on the registration page before an account exists.
 *
 * A small whitelist rather than an arbitrary filename parameter, so this
 * route can never be used to read anything else out of storage/app.
 */
class VendorSetupVideoController extends Controller
{
    private const CLIPS = [
        'part1' => 'Part1-Register-Your-Business.mp4',
    ];

    public function show(string $clip)
    {
        abort_unless(isset(self::CLIPS[$clip]), 404);

        $path = storage_path('app/vendor-setup-video/' . self::CLIPS[$clip]);
        abort_unless(is_file($path), 404);

        // BinaryFileResponse (what response()->file() returns) honours HTTP
        // Range requests on its own, which is what lets the <video> element
        // seek instead of re-downloading from the start every time.
        return response()->file($path, [
            'Content-Type' => 'video/mp4',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
