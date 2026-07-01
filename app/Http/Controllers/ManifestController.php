<?php

namespace App\Http\Controllers;

class ManifestController extends Controller
{
    /**
     * The PWA manifest, served dynamically so icon URLs are absolute and honour
     * the current scheme/host. Branded as the project ("Book Appointment") with
     * our app icon, so every install looks the same regardless of entry page.
     */
    public function site()
    {
        $manifest = [
            'id'               => '/',
            'name'             => config('app.name', 'Book Appointment'),
            'short_name'       => 'Booking',
            'description'      => 'Book appointments, grab your token, and get live turn alerts.',
            'start_url'        => url('/'),
            'scope'            => url('/'),
            'display'          => 'standalone',
            'orientation'      => 'portrait',
            'background_color' => '#0a0f2c',
            'theme_color'      => '#0a0f2c',
            'icons'            => $this->icons(),
        ];

        return response()->json($manifest, 200, [
            'Content-Type'  => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=3600',
        ], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Our branded app icons. Absolute URLs so they resolve correctly regardless
     * of the manifest's own path (e.g. /vendors/{slug}/manifest.webmanifest).
     */
    private function icons(): array
    {
        return [
            ['src' => url('/images/pwa/icon-192.png'),          'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => url('/images/pwa/icon-512.png'),          'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => url('/images/pwa/icon-maskable-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
        ];
    }
}
