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
        // url('/') yields no trailing slash. Both fields are resolved as URLs
        // against the manifest, so the slash is what keeps the scope rooted at
        // the site rather than at a sibling of it.
        $root = rtrim(url('/'), '/') . '/';

        $manifest = [
            'id'               => '/',
            'name'             => config('app.name', 'Book Appointment'),
            'short_name'       => config('brand.short_name', 'Booking'),
            'description'      => 'Book appointments, grab your token, and get live turn alerts.',
            'start_url'        => $root,
            'scope'            => $root,
            'display'          => 'standalone',
            'orientation'      => 'portrait',
            'background_color' => '#0a0f2c',
            'theme_color'      => '#0a0f2c',
            'icons'            => $this->icons(),

            /*
            | Lets an already-installed visitor be recognised from an ordinary
            | browser tab, via navigator.getInstalledRelatedApps() — see the
            | Install App button, which hides itself on that signal. Chrome
            | never re-offers an install for an app it has already installed,
            | so without this those visitors get a button that can only ever
            | fall through to the written steps.
            |
            | `prefer_related_applications` MUST stay false: true would tell
            | the browser to promote a native app instead, and the site would
            | stop being installable at all.
            */
            'prefer_related_applications' => false,
            'related_applications'        => [
                ['platform' => 'webapp', 'url' => route('manifest.site')],
            ],
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
