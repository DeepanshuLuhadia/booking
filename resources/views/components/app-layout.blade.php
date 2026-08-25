@props([
    'vendorTheme' => null,
    'pageTitle'   => null,
    'panelType'   => null,
    // 'full' is the landing-page footer; 'minimal' keeps the copyright bar only,
    // for pages that end on their content (e.g. the category listing).
    'footerMode'  => 'full',
])
@php
    $theme     = $vendorTheme ?? \App\Services\ThemeService::getTheme('consultant');
    $bodyClass = 'theme-' . $theme['key'];
    $isDark    = $theme['is_dark'] ?? false;
    /*
    | Pages with no vendor theme of their own (landing, listing, about,
    | contact) run the brand orange in the header rather than the default
    | consultant blue, so the logo and the location pin match the search
    | button and the site reads as one palette. A vendor page keeps its own
    | category colour.
    */
    $defaultBrand = $vendorTheme === null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Title is the app name only (no vendor/page name). The home-screen shortcut
         name falls back to this title on non-installable browsers, so it must not
         contain the vendor name. --}}
    <title>{{ config('app.name', 'Book Appointment') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- PWA: manifest + icons so the site can be installed to the home screen.
         Required for iOS web push (only works once installed) and for the
         "Add to Home Screen" prompt shown after booking. The installed app is
         branded as the project ("Book Appointment") with our app icon,
         regardless of which vendor page it was installed from. -->
    <link rel="manifest" href="{{ route('manifest.site') }}">
    <meta name="theme-color" content="#0a0f2c">
    {{-- Multiple icon links so every path (native install, favicon, Android
         shortcut, iOS home screen) picks up our app icon rather than a default. --}}
    <link rel="icon" href="/favicon.ico?v=2" sizes="any">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/pwa/icon-192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/pwa/icon-512.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/pwa/apple-touch-icon.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Book Appointment') }}">

    <!-- Capture the install prompt as early as possible (must run before the
         browser fires `beforeinstallprompt`) and expose a standalone check.
         Used by the Add-to-Home-Screen modal further down. -->
    <script>
        window.__deferredInstallPrompt = null;
        window.__isStandalone = function () {
            return (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches)
                || window.navigator.standalone === true;
        };
        window.addEventListener('beforeinstallprompt', function (e) {
            e.preventDefault();
            window.__deferredInstallPrompt = e;
            window.dispatchEvent(new Event('installprompt-available'));
        });
        window.addEventListener('appinstalled', function () {
            window.__deferredInstallPrompt = null;
        });

        // Register the service worker on every load (independent of notifications) so the
        // browser considers the site installable and can fire `beforeinstallprompt`.
        // Uses the same URL the FCM code reuses later, so it dedupes to one registration.
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/firebase-messaging-sw.js?v=7').catch(function () {});
            });
        }
    </script>

    <!-- Firebase SDK (Version 8) — loaded with defer so they don't block first paint.
         The init script below also waits for DOMContentLoaded, preserving order. -->
    <script defer src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
    <script defer src="https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js"></script>


    <!--
        Realtime bootstrap gate.

        The Vite bundle below is emitted as <script type="module">, which is
        DEFERRED: it executes only after the document finishes parsing. Any inline
        <script> further down the page therefore runs BEFORE window.Echo exists —
        a listener registered there silently does nothing, which is exactly how
        the dashboards ended up never receiving a booking without a refresh.

        Pages must register realtime listeners through this helper rather than at
        parse time. Defined here, ahead of the bundle, so it is available to every
        inline script on the page regardless of load order.
    -->
    <script>
        window.whenRealtimeReady = function (callback) {
            var start = function () {
                // No Echo means the bundle failed or Reverb is unreachable. Pages
                // fall back to polling, so this is a degradation, not a break.
                if (window.Echo) { callback(window.Echo); }
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', start, { once: true });
            } else {
                start();
            }
        };
    </script>

    <!-- Styles + Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        {!! \App\Services\ThemeService::getCssVars($theme) !!}
        .theme-nav { background-color: var(--theme-nav-bg) !important; color: var(--theme-nav-text) !important; }
        .nav-link { color: var(--theme-nav-text) !important; }
        /* Desktop nav menu: hidden by default, flex on lg+ */
        .nav-desktop-menu {
            display: none;
        }
        /* Mobile hamburger: visible by default, hidden on lg+ */
        .nav-mobile-toggle {
            display: flex;
        }
        /* ── Brand orange (same pair as the search button on the search bar) ──
           Applied to the header logo and the location pin on pages running the
           default theme, so those two stop rendering in consultant blue. */
        .brand-default {
            --brand-accent: #ff6d00;
            --brand-accent-2: #ffab40;
        }
        .brand-default .nav-brand-mark {
            background: linear-gradient(135deg, var(--brand-accent), var(--brand-accent-2)) !important;
        }
        .brand-default .nav-brand-word {
            background: linear-gradient(135deg, var(--brand-accent), var(--brand-accent-2)) !important;
            -webkit-background-clip: text !important;
            background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            color: transparent !important;
        }
        .brand-default .nav-mobile-nearme,
        .brand-default .nav-mobile-nearme-chip,
        .brand-default .nav-mobile-nearme-label {
            color: var(--brand-accent-2);
        }
        .brand-default .nav-mobile-nearme.is-empty {
            border-color: var(--brand-accent-2);
            background: rgba(255, 109, 0, .10);
            box-shadow: 0 0 0 3px rgba(255, 109, 0, .10);
        }

        /* Mobile "Near Me": bare pin icon, no border/background, next to hamburger */
        .nav-mobile-nearme {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            padding: 0;
            border: none;
            background: transparent;
            color: var(--theme-primary, #ff8c42);
            cursor: pointer;
            transition: transform .2s ease, opacity .2s ease;
        }
        .nav-mobile-nearme:active {
            transform: scale(.92);
        }
        .nav-mobile-nearme.is-locating {
            opacity: .6;
        }
        /* No location chosen yet — highlight the pin with a rounded theme border */
        .nav-mobile-nearme.is-empty {
            border: 1.5px solid var(--theme-primary, #ff8c42);
            border-radius: 9999px;
            background: rgba(255, 140, 66, .10);
            box-shadow: 0 0 0 3px rgba(255, 140, 66, .10);
        }
        /* Location chosen — the pin is replaced by the (truncated) place name */
        .nav-mobile-nearme.has-location {
            width: auto;
            min-width: 40px;
            padding: 0 8px;
        }
        .nav-mobile-nearme-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: var(--theme-primary, #ff8c42);
        }
        .nav-mobile-nearme-chip svg {
            flex-shrink: 0;
        }
        .nav-mobile-nearme-label {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .02em;
            white-space: nowrap;
            color: var(--theme-primary, #ff8c42);
        }
        @media (min-width: 1024px) {
            .nav-desktop-menu {
                display: flex !important;
            }
            .nav-mobile-toggle {
                display: none !important;
            }
            .nav-mobile-nearme {
                display: none !important;
            }
        }

        /* Prevent mobile browsers from auto-inflating body text. */
        html { -webkit-text-size-adjust: 100%; text-size-adjust: 100%; }

        /* ══════════════════════════════════════════════════════════════
           GLOBAL MOBILE TYPE SCALE (≤600px, front website only).
           Loaded after the compiled Tailwind CSS, so these cap the large
           display utilities on phones without touching desktop/tablet
           (Tailwind `md:` variants live in min-width:768px and never overlap).
           This keeps headings, stat numbers, modal titles and popups
           proportionate on mobile across every page using this layout.
           ══════════════════════════════════════════════════════════════ */
        @media (max-width: 600px) {
            .text-2xl { font-size: 1.25rem !important;  line-height: 1.3 !important; }
            .text-3xl { font-size: 1.375rem !important; line-height: 1.25 !important; }
            .text-4xl { font-size: 1.625rem !important; line-height: 1.2 !important; }
            .text-5xl { font-size: 1.875rem !important; line-height: 1.15 !important; }
            .text-6xl { font-size: 2.125rem !important; line-height: 1.1 !important; }
            .text-7xl { font-size: 2.5rem !important;   line-height: 1.05 !important; }

            /* Buttons/tap-targets that are fixed-tall with no mobile downshift. */
            .theme-btn.h-24 { height: 4rem !important; }
        }

        /* Vendor-detail mobile hero (inline-styled, shows by default) is hidden on
           desktop/tablet, where the original Tailwind hero renders instead. */
        @media (min-width: 768px) {
            .vd-mobile-hero { display: none !important; }
        }

        /* Vendor profile — clickable rating-breakdown rows (filter reviews by star).
           Scoped classes so they don't depend on utilities absent from the prebuilt
           Tailwind bundle. */
        .vd-rating-row {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 5px 8px;
            margin: 0 -8px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            cursor: pointer;
            text-align: left;
            transition: background .2s ease;
        }
        .vd-rating-row:hover { background: rgba(255, 255, 255, 0.05); }
        .vd-rating-row.active { background: rgba(255, 255, 255, 0.10); }
        .vd-rating-row:disabled { cursor: default; opacity: .4; }

        /* ══════════════════════════════════════════════════════════════
           FOOTER — curved top edge, matching the arc under the hero.
           Desktop/tablet only (≥601px); phones keep the flat footer they
           already had. Written as plain CSS because the prebuilt Tailwind
           bundle does not contain the arbitrary `bg-[#0a0f2c]` utility, so
           the footer background has to be declared explicitly here.
           ══════════════════════════════════════════════════════════════ */
        .site-footer-curved {
            position: relative;
            background: #070b20;
            border-top: 1px solid rgba(255, 255, 255, 0.07) !important;
            overflow: hidden;
        }

        /* Phones: shallower arc and a tighter pull-up, so the curve stays a
           curve instead of eating the top of the footer content. */
        @media (max-width: 600px) {
            .site-footer-curved {
                border-radius: 50% 50% 0 0 / 46px 46px 0 0;
                margin-top: -28px;
            }

            .site-footer-curved::before {
                content: "";
                position: absolute;
                top: -420px;
                left: 50%;
                transform: translateX(-50%);
                width: 820px;
                height: 820px;
                z-index: 0;
                pointer-events: none;
                background:
                    repeating-radial-gradient(circle at 50% 50%, transparent 0 34px, rgba(255, 140, 66, .16) 34px 35px),
                    repeating-conic-gradient(from 0deg at 50% 50%, transparent 0 5deg, rgba(255, 140, 66, .11) 5deg 5.3deg);
                -webkit-mask-image: radial-gradient(circle at 50% 50%, transparent 0 30%, #000 46%, transparent 74%);
                mask-image: radial-gradient(circle at 50% 50%, transparent 0 30%, #000 46%, transparent 74%);
            }

            .site-footer-curved > * {
                position: relative;
                z-index: 1;
            }
        }

        @media (min-width: 601px) {
            .site-footer-curved {
                border-radius: 50% 50% 0 0 / 130px 130px 0 0;
                margin-top: -70px;
            }

            /* Curved wireframe mesh echoing the CTA section above it. */
            .site-footer-curved::before {
                content: "";
                position: absolute;
                /* Centre pushed well above the footer so only the wide outer arcs
                   fall behind the content — the dense convergence at the middle
                   of a polar grid would read as noise under the text. */
                top: -880px;
                left: 50%;
                transform: translateX(-50%);
                width: 1700px;
                height: 1700px;
                z-index: 0;
                pointer-events: none;
                background:
                    repeating-radial-gradient(circle at 50% 50%, transparent 0 62px, rgba(255, 140, 66, .16) 62px 63px),
                    repeating-conic-gradient(from 0deg at 50% 50%, transparent 0 5deg, rgba(255, 140, 66, .11) 5deg 5.3deg);
                -webkit-mask-image: radial-gradient(circle at 50% 50%, transparent 0 30%, #000 46%, transparent 74%);
                mask-image: radial-gradient(circle at 50% 50%, transparent 0 30%, #000 46%, transparent 74%);
            }

            /* Keep the real footer content above the decorative mesh. */
            .site-footer-curved > * {
                position: relative;
                z-index: 1;
            }
        }

        /* Vendor profile — reviews list: vertical stack on desktop, swipe slider on
           mobile (one review per view, snap-scrolling). */
        .vd-review-slider {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        @media (max-width: 767px) {
            .vd-review-slider {
                flex-direction: row;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
                gap: 12px;
                padding-bottom: 6px;
            }
            .vd-review-slider::-webkit-scrollbar { display: none; }
            .vd-review-slider > * {
                scroll-snap-align: center;
                flex: 0 0 90%;
                min-width: 0;
            }
        }
    </style>

    <script>
        /*
         * Turn raw GPS coordinates into a human place name — down to the
         * suburb ("Jhotwara"), not just the city ("Jaipur").
         *
         * Two free, keyless, CORS-enabled services, queried in parallel,
         * because neither alone does the whole job:
         *
         *   BigDataCloud — reliable city/state, but no suburb data in India.
         *                  For Jhotwara it returns city AND locality as
         *                  "Jaipur", and the deepest administrative entry it
         *                  knows is "Jaipur Municipal Corporation".
         *   Photon (OSM)  — carries the real suburb in `district`
         *                  ("Jothwara", "Kormangala East"), with `locality`
         *                  one level finer beneath it.
         *
         * So Photon supplies the suburb, BigDataCloud the city/state, and each
         * covers for the other if it fails. Every failure path (offline,
         * blocked, rate-limited, slow, HTML error page instead of JSON)
         * degrades to a coarser label or an empty string — it never blocks the
         * location save.
         *
         * Photon rather than Nominatim: Nominatim's operations policy rules out
         * client-side use and it answers 403 to unidentified callers, so it
         * would have failed in the field while looking fine in a terminal.
         * Photon is a free keyless service intended for browser use. It does
         * throttle bursts, which is survivable here because a lookup only
         * happens on first detection or an explicit refresh — the answer then
         * lives in a year-long cookie — and because failure is non-fatal.
         */
        window.resolvePlaceName = function (lat, lng) {
            var timeout = function (promise, ms) {
                return Promise.race([
                    promise,
                    new Promise(function (resolve) { setTimeout(function () { resolve(null); }, ms); })
                ]).catch(function () { return null; });
            };

            var get = function (url) {
                return fetch(url).then(function (r) { return r.ok ? r.json() : null; });
            };

            /* Administrative unit names are not places anyone says out loud.
               Photon's `district` reads "K/W Ward" in Mumbai and "Ward 104
               Kondapur" in Hyderabad, so anything shaped like a ward or civic
               body is skipped in favour of the next candidate down. */
            var isAdminNoise = function (name) {
                return !name || /\b(ward|zone|circle|municipal|corporation|tehsil|taluk|mandal|district|division)\b/i.test(name);
            };

            var pickSuburb = function (p) {
                if (!p) return '';
                // district = suburb ("Jothwara"); locality sits one level finer
                // and stands in when the district is an administrative label.
                var candidates = [p.district, p.locality, p.suburb, p.quarter];
                for (var i = 0; i < candidates.length; i++) {
                    if (!isAdminNoise(candidates[i])) return candidates[i];
                }
                return '';
            };

            // api-bdc.io is the current host; the older bigdatacloud.net name
            // only 307s across to it, so it stands in as the fallback.
            var bdcQuery = '/data/reverse-geocode-client?latitude=' + encodeURIComponent(lat) +
                           '&longitude=' + encodeURIComponent(lng) + '&localityLanguage=en';
            var bdc = get('https://api-bdc.io' + bdcQuery)
                .catch(function () { return get('https://api.bigdatacloud.net' + bdcQuery); });

            var photon = get('https://photon.komoot.io/reverse?lat=' + encodeURIComponent(lat) +
                             '&lon=' + encodeURIComponent(lng));

            return Promise.all([timeout(bdc, 4000), timeout(photon, 4000)]).then(function (results) {
                var b = results[0] || {};
                var f = results[1] && results[1].features && results[1].features[0];
                var p = (f && f.properties) || {};

                var city  = b.city || b.locality || (isAdminNoise(p.city) ? '' : p.city) || '';
                var state = b.principalSubdivision || p.state || '';
                // Photon first — it is the only one of the two that knows suburbs.
                var suburb = pickSuburb(p) || b.locality || b.city || '';

                return { city: city, state: state, suburb: suburb };
            }).catch(function () {
                return { city: '', state: '', suburb: '' };
            });
        };

        /* Cookie values may contain spaces ("New Delhi") — encode on write; PHP
           url-decodes $_COOKIE on the way in, so Blade still reads the plain name. */
        window.writeLocationCookies = function (lat, lng, state, city, suburb) {
            var year = '; path=/; max-age=31536000; SameSite=Lax';
            document.cookie = 'location_granted=true' + year;
            document.cookie = 'user_lat=' + encodeURIComponent(lat ?? '') + year;
            document.cookie = 'user_lng=' + encodeURIComponent(lng ?? '') + year;
            document.cookie = 'user_state=' + encodeURIComponent(state ?? '') + year;
            document.cookie = 'user_city=' + encodeURIComponent(city ?? '') + year;
            document.cookie = 'user_suburb=' + encodeURIComponent(suburb ?? '') + year;
            try { localStorage.setItem('location_granted', 'true'); } catch (e) {}
        };

        /*
         * Same spelling corrections Blade applies (config/place_names.php),
         * mirrored here so a label patched in by the background backfill reads
         * identically to one rendered by the server.
         */
        window.__placeAliases = @json(\App\Services\PlaceNameService::aliasMap());

        window.correctPlaceName = function (name) {
            if (!name) return name || '';
            var hit = window.__placeAliases[String(name).trim().toLowerCase()];
            return hit || name;
        };

        window.readCookie = function (name) {
            var hit = ('; ' + document.cookie).split('; ' + name + '=');
            return hit.length === 2 ? decodeURIComponent(hit.pop().split(';').shift()) : '';
        };

        /*
         * Ask the device for its position without making the visitor pick
         * anything first.
         *
         * The site used to open on a blocking modal whose only routes forward
         * were a button press or a manual state/city dropdown, so a visitor
         * arriving on the landing page saw "Near Me" instead of where they
         * actually are. getCurrentPosition does not require a user gesture, so
         * this fires straight away: the OS prompt is the only thing between the
         * visitor and a suburb-level location.
         *
         * Nothing here can trap the visitor — every failure path (unsupported,
         * denied, timed out) rejects, and the caller falls back to the retry
         * screen, which can always be skipped.
         */
        window.detectLocation = function (options) {
            options = options || {};
            return new Promise(function (resolve, reject) {
                if (!('geolocation' in navigator)) {
                    return reject(new Error('unsupported'));
                }

                navigator.geolocation.getCurrentPosition(function (position) {
                    var lat = position.coords.latitude, lng = position.coords.longitude;
                    window.resolvePlaceName(lat, lng).then(function (place) {
                        window.writeLocationCookies(lat, lng, place.state, place.city, place.suburb);
                        resolve({ lat: lat, lng: lng, state: place.state, city: place.city, suburb: place.suburb });
                    });
                }, reject, { timeout: options.timeout || 10000, maximumAge: 600000 });
            });
        };

        /*
         * Has the visitor already allowed location at the OS level? Lets the
         * consent modal stay out of the way entirely for returning visitors —
         * we can just read the position instead of asking again. Browsers
         * without the Permissions API report 'prompt', which is the safe
         * assumption (we ask).
         */
        window.locationPermissionState = function () {
            if (!navigator.permissions || !navigator.permissions.query) {
                return Promise.resolve('prompt');
            }
            return navigator.permissions.query({ name: 'geolocation' })
                .then(function (status) { return status.state; })
                .catch(function () { return 'prompt'; });
        };

        /*
         * How this device re-enables a location permission the visitor has
         * already refused. Nothing on the page can undo a block — it lives in
         * browser/OS settings — so all we can do is name the exact taps.
         */
        window.locationHelpSteps = function () {
            var ua = navigator.userAgent || '';
            var isIOS = /iPad|iPhone|iPod/.test(ua) ||
                        (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
            var isAndroid = /Android/.test(ua);
            var isFirefox = /Firefox|FxiOS/.test(ua);
            // CriOS/FxiOS are Chrome/Firefox on iOS — still Safari's engine, but
            // their own permission entry, so they are named separately above.
            var isSafari = /Safari/.test(ua) && !/Chrome|CriOS|Chromium|Edg|OPR/.test(ua);

            if (isIOS) {
                var iosBrowser = /CriOS/.test(ua) ? 'Chrome' : (/FxiOS/.test(ua) ? 'Firefox' : 'Safari');
                return {
                    device: 'iPhone / iPad',
                    steps: [
                        'Open the <b>Settings</b> app on your device.',
                        'Go to <b>Privacy &amp; Security → Location Services</b> and turn it on.',
                        'In the same list, tap <b>' + iosBrowser + '</b> and choose <b>While Using the App</b>.',
                        'Return here and tap <b>Try Again</b> below.'
                    ]
                };
            }

            if (isAndroid) {
                return {
                    device: 'Android',
                    steps: [
                        'Tap the <b>lock</b> (or <b>⚙</b>) icon on the left of the address bar.',
                        'Tap <b>Permissions → Location</b> and set it to <b>Allow</b>.',
                        'If location is off for the whole phone, switch it on in <b>Settings → Location</b>.',
                        'Return here and tap <b>Try Again</b> below.'
                    ]
                };
            }

            if (isFirefox) {
                return {
                    device: 'Firefox',
                    steps: [
                        'Click the <b>lock</b> icon on the left of the address bar.',
                        'Find <b>Access Your Location — Blocked</b> and click the <b>✕</b> beside it.',
                        'Reload the page, then allow location when Firefox asks.'
                    ]
                };
            }

            if (isSafari) {
                return {
                    device: 'Safari',
                    steps: [
                        'Open <b>Safari → Settings → Websites → Location</b>.',
                        'Find this site in the list and set it to <b>Allow</b>.',
                        'Return here and tap <b>Try Again</b> below.'
                    ]
                };
            }

            return {
                device: 'Chrome / Edge',
                steps: [
                    'Click the <b>lock</b> (or <b>sliders</b>) icon on the left of the address bar.',
                    'Set <b>Location</b> to <b>Allow</b>.',
                    'Return here and click <b>Try Again</b> below.'
                ]
            };
        };

        /*
         * What every location icon on the site runs when tapped.
         *
         * A refusal is not treated as a permanent answer: we call
         * getCurrentPosition again regardless, so any browser still willing to
         * prompt does so. Where the block is sticky (Chrome and Safari never
         * re-prompt once denied) the call rejects with PERMISSION_DENIED
         * without a prompt ever appearing — indistinguishable from a fresh
         * refusal, and in both cases the only way forward is browser settings,
         * so we raise the how-to-enable steps.
         *
         * Rejections carry `handled` when the steps were shown, so callers can
         * skip their own error toast.
         */
        window.requestLocationWithHelp = function (options) {
            return window.detectLocation(options).catch(function (error) {
                // Only a permission block is fixable in settings; a timeout or
                // a browser with no geolocation at all is left to the caller's
                // toast, which says something truthful about those.
                if (!error || error.code !== 1) throw error;

                window.dispatchEvent(new CustomEvent('location-help'));

                // Rethrown as our own error rather than tagging the browser's
                // GeolocationPositionError, which is a host object we have no
                // business adding properties to.
                var handled = new Error(error.message || 'permission denied');
                handled.code = error.code;
                handled.handled = true;
                throw handled;
            });
        };

        /*
         * Backfill for visitors who granted location before suburbs existed:
         * their cookies hold coordinates but no suburb name. Resolve it quietly
         * in the background and patch the labels in place — no reload, and no
         * second permission prompt, because the coordinates are already ours.
         */
        window.__backfillSuburb = function () {
            var lat = window.readCookie('user_lat'), lng = window.readCookie('user_lng');
            if (!lat || !lng || window.readCookie('user_suburb')) return;

            window.resolvePlaceName(lat, lng).then(function (place) {
                if (!place.suburb && !place.city) return;
                window.writeLocationCookies(lat, lng, place.state, place.city, place.suburb);

                var label = window.correctPlaceName(place.suburb || place.city);
                document.querySelectorAll('[data-location-label]').forEach(function (el) {
                    var max = parseInt(el.getAttribute('data-location-max') || '0', 10);
                    el.textContent = (max && label.length > max) ? label.slice(0, max) + '…' : label;
                });
            });
        };

        document.addEventListener('DOMContentLoaded', function () {
            if (document.cookie.indexOf('location_granted=true') !== -1) {
                window.__backfillSuburb();
            }
        });
    </script>
</head>
<body class="antialiased {{ $bodyClass }} {{ $defaultBrand ? 'brand-default' : '' }} min-h-screen relative overflow-x-hidden bg-theme-main">
    @if(!request()->cookie('location_granted'))
    <!-- Step 1: Mandatory Location Consent Modal (custom only — no browser geolocation prompt) -->
    <div x-data="{
             showLocationModal: true,
             loading: false,
             /* 'detecting' is the opening state: we ask the device straight
                away rather than making the visitor choose anything. It falls
                back to 'gps' (retry), which is the only other state — the
                manual city picker is gone, so it's GPS or skip. */
             mode: 'detecting',
             init() {
                 if (localStorage.getItem('location_granted') || document.cookie.includes('location_granted')) {
                     this.showLocationModal = false;
                     return;
                 }

                 window.locationPermissionState().then((state) => {
                     /* Already refused at the OS level — re-asking would be a
                        no-op prompt the browser never shows, so land on the
                        retry screen instead of spinning on 'detecting'. */
                     if (state === 'denied') {
                         this.mode = 'gps';
                         return;
                     }

                     this.autoDetect();
                 });
             },
             /* Silent when permission is already granted; a single OS prompt
                otherwise. Either way the visitor lands on their own suburb
                without picking anything. */
             autoDetect() {
                 this.mode = 'detecting';
                 this.loading = true;

                 window.detectLocation()
                     .then(() => window.location.reload())
                     .catch((error) => {
                         console.warn('Geolocation failed', error);
                         this.loading = false;
                         this.mode = 'gps';
                     });
             },
             /* Retry from the 'gps' screen. Goes through the shared helper so a
                visitor whose browser has stopped prompting gets the settings
                steps instead of a button that silently does nothing. */
             requestLocation() {
                 this.loading = true;
                 window.requestLocationWithHelp()
                     .then(() => window.location.reload())
                     .catch((error) => {
                         console.warn('Geolocation failed', error);
                         this.mode = 'gps';
                         this.loading = false;
                     });
             },
             saveLocation(lat, lng, state, city, suburb) {
                 window.writeLocationCookies(lat, lng, state, city, suburb ?? city);
                 window.location.reload();
             }
         }"
         x-show="showLocationModal"
         x-cloak
         style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 2147483647; display: flex; align-items: center; justify-content: center; background: rgba(10, 15, 44, 0.98); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);"
    >
        <div style="background-color: #0a0f2c !important; position: relative !important; z-index: 2147483647 !important;" class="max-w-md w-full border border-white/10 rounded-3xl p-8 flex flex-col items-center text-center shadow-2xl overflow-hidden">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-purple-500/10 rounded-full blur-3xl"></div>

            <div class="w-20 h-20 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mb-6 relative z-10">
                <svg class="w-10 h-10 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            
            <h2 class="text-2xl font-black text-white tracking-tight mb-3 relative z-10"
                x-text="mode === 'detecting' ? 'Finding Your Location' : 'Location Required'">Finding Your Location</h2>
            <p class="text-sm text-white/60 mb-8 relative z-10 leading-relaxed" x-show="mode === 'detecting'">
                Detecting your area so we can show the professionals closest to you. Allow location access if your browser asks.
            </p>
            <p class="text-sm text-white/60 mb-8 relative z-10 leading-relaxed" x-show="mode === 'gps'" x-cloak>
                To discover verified experts near you, please share your location.
            </p>

            {{-- Auto-detection in flight. No button to press: the only prompt
                 the visitor sees is the browser's own. --}}
            <div x-show="mode === 'detecting'" class="w-full space-y-3 relative z-10">
                <div class="w-full h-14 rounded-xl bg-white/5 border border-white/10 text-white/70 font-black uppercase tracking-widest text-xs flex items-center justify-center gap-3">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Detecting…
                </div>
            </div>

            <div x-show="mode === 'gps'" x-cloak class="w-full space-y-3 relative z-10">
                <button 
                    @click="requestLocation()" 
                    :disabled="loading"
                    class="w-full h-14 rounded-xl theme-gradient-bg text-white font-black uppercase tracking-widest text-xs flex items-center justify-center gap-2 transition-all hover:brightness-110 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span x-show="!loading" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        Use GPS Location
                    </span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Locating...
                    </span>
                </button>
                {{-- The only way past a failed/denied GPS attempt now that the
                     manual city picker is gone — without it the modal would
                     trap the visitor on a permission they can't grant. --}}
                <button
                    @click="saveLocation('', '', '', '')"
                    :disabled="loading"
                    class="w-full h-12 text-blue-400 hover:text-blue-300 text-xs font-bold uppercase tracking-widest transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Browse Globally (Skip)
                </button>
            </div>
        </div>
    </div>
    @endif

    {{--
        "Location is blocked" steps.

        Raised by requestLocationWithHelp whenever a location request comes back
        PERMISSION_DENIED — the case where the browser has stopped showing its
        own prompt, so tapping the pin again would do nothing visible forever.
        Rendered on every page (not just when location is unset): a visitor who
        allowed location once can revoke it later and still tap the pin.
    --}}
    <div x-data="{
             open: false,
             loading: false,
             retried: false,
             device: '',
             steps: [],
             show() {
                 const help = window.locationHelpSteps();
                 this.device = help.device;
                 this.steps = help.steps;
                 this.retried = false;
                 this.open = true;
             },
             /* Settings changes take effect immediately, so a retry from here
                succeeds as soon as the visitor has flipped the switch. */
             retry() {
                 this.loading = true;
                 window.detectLocation()
                     .then(() => window.location.reload())
                     .catch(() => {
                         this.loading = false;
                         this.retried = true;
                     });
             }
         }"
         @location-help.window="show()"
         x-show="open"
         x-cloak
         style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 2147483647; display: flex; align-items: center; justify-content: center; padding: 20px; background: rgba(10, 15, 44, 0.98); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);"
    >
        <div style="background-color: #0a0f2c !important; position: relative !important;" class="max-w-md w-full border border-white/10 rounded-3xl p-8 shadow-2xl overflow-hidden">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl"></div>

            <div class="relative z-10 flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-width="2" d="M4 4l16 16" />
                    </svg>
                </div>

                <h2 class="text-2xl font-black text-white tracking-tight mb-3">Location Is Blocked</h2>
                <p class="text-sm text-white/60 mb-6 leading-relaxed">
                    Your browser is no longer asking for permission, so it has to be turned back on in settings.
                    Here's how on <span class="text-white/90 font-bold" x-text="device"></span>:
                </p>
            </div>

            <ol class="relative z-10 w-full space-y-3 text-left mb-6">
                <template x-for="(step, index) in steps" :key="index">
                    <li class="flex items-start gap-3 text-sm text-white/75 leading-relaxed">
                        <span class="shrink-0 w-6 h-6 rounded-full bg-white/5 border border-white/10 text-[11px] font-black text-white/70 flex items-center justify-center"
                              x-text="index + 1"></span>
                        <span x-html="step"></span>
                    </li>
                </template>
            </ol>

            <p x-show="retried" x-cloak class="relative z-10 text-xs text-rose-300/80 text-center mb-4 leading-relaxed">
                Still blocked. Check the steps above once more — on some phones the page needs a full reload afterwards.
            </p>

            <div class="relative z-10 space-y-3">
                <button
                    @click="retry()"
                    :disabled="loading"
                    class="w-full h-14 rounded-xl theme-gradient-bg text-white font-black uppercase tracking-widest text-xs flex items-center justify-center gap-2 transition-all hover:brightness-110 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span x-show="!loading">Try Again</span>
                    <span x-show="loading" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Checking…
                    </span>
                </button>
                <button
                    @click="open = false"
                    :disabled="loading"
                    class="w-full h-12 text-white/40 hover:text-white text-xs font-bold uppercase tracking-widest transition-colors disabled:opacity-50"
                >
                    Not Now
                </button>
            </div>
        </div>
    </div>

    {{--
        Notification consent.

        ALWAYS rendered — it used to be omitted entirely once the visitor had
        decided once, which meant a customer who dismissed it could never be asked
        again no matter how many appointments they later booked, and stayed
        permanently unreachable. It costs nothing to render: it is hidden until
        something asks for it.

        It is raised on two occasions:
          - every booking placed (trigger-notification-prompt), asked once per
            booking so each new appointment gets its own chance;
          - on arrival, when the device is already holding a booking we have no
            way to notify about ($pushSetupNeeded).

        Never raised when the browser has already refused: once permission is
        'denied' no prompt can appear, so the modal could not do anything and
        would just be nagging. Those customers get a one-line pointer to their
        browser settings instead.
    --}}
    <!-- Step 2: Notification Consent Modal (custom — browser prompt only fires AFTER user clicks Enable) -->
    <div x-data="{
             showNotifModal: false,
             loading: false,
             // A booking is on file that we currently cannot reach.
             pushNeeded: {{ ($pushSetupNeeded ?? false) ? 'true' : 'false' }},

             canAsk() {
                 return (typeof Notification !== 'undefined') && Notification.permission === 'default';
             },

             /*
              * The two prompts never stack, so one has to yield.
              *
              * On iOS a Safari tab cannot receive web push at all — the site must
              * be installed to the Home Screen first — so there the install prompt
              * genuinely has to come first or notifications are pointless.
              * Everywhere else push works in an ordinary browser, so asking for it
              * is the thing that actually delivers the updates, and the install
              * prompt waits its turn.
              */
             raise(delay = 0) {
                 if (!this.canAsk() || this.showNotifModal) return;

                 const iOS = /iphone|ipad|ipod/i.test(navigator.userAgent || '');

                 setTimeout(() => {
                     if (window.__a2hsCanShow && iOS) return;
                     this.showNotifModal = true;
                 }, delay);
             },

             init() {
                 const isSupported = (typeof Notification !== 'undefined');

                 /*
                  * A booking was just placed. Ask again — every booking earns its
                  * own ask, regardless of anything dismissed before, because the
                  * customer has just committed to turning up somewhere and the
                  * updates are now worth something to them.
                  */
                 window.addEventListener('trigger-notification-prompt', () => {
                     if (this.canAsk()) {
                         this.raise(600);
                     } else if (isSupported && Notification.permission === 'denied') {
                         // Cannot be re-prompted by us; point at the only thing
                         // that can actually change it.
                         window.dispatchEvent(new CustomEvent('toast', {
                             detail: {
                                 message: 'Turn on notifications in your browser settings to get queue updates.',
                                 type: 'info',
                             },
                         }));
                     }
                 });

                 // Arriving with a booking we cannot reach: offer once per visit.
                 if (this.pushNeeded && !sessionStorage.getItem('push_reoffered')) {
                     sessionStorage.setItem('push_reoffered', '1');
                     this.raise(1200);
                 }
             },
             enableNotifications() {
                 this.loading = true;
                 // This is user-initiated (click), so the browser prompt fires naturally
                 if (typeof window.__requestNotificationPermission === 'function') {
                     window.__requestNotificationPermission();
                 }
                 document.cookie = 'notif_consent_decided=true; path=/; max-age=31536000; SameSite=Lax';
                 localStorage.setItem('notif_consent_decided', 'true');
                 this.showNotifModal = false;
             },
             dismissNotifications() {
                 // Deliberately NOT permanent any more. The cookie still records
                 // that they have seen it (it suppresses the first-visit ask), but
                 // the next booking asks again — see the listener above.
                 document.cookie = 'notif_consent_decided=true; path=/; max-age=31536000; SameSite=Lax';
                 localStorage.setItem('notif_consent_decided', 'true');
                 this.showNotifModal = false;
             }
          }"
         x-show="showNotifModal"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 2147483647; display: flex; align-items: center; justify-content: center; background: rgba(10, 15, 44, 0.95); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);"
    >
        <div style="background-color: #0a0f2c !important; position: relative !important; z-index: 2147483647 !important;" class="max-w-md w-full border border-white/10 rounded-3xl p-8 flex flex-col items-center text-center shadow-2xl overflow-hidden">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl"></div>

            <div class="w-20 h-20 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mb-6 relative z-10">
                <svg class="w-10 h-10 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            
            <h2 class="text-2xl font-black text-white tracking-tight mb-3 relative z-10">Enable Notifications</h2>
            <p class="text-sm text-white/60 mb-8 relative z-10 leading-relaxed">
                Stay updated with real-time booking confirmations, appointment reminders, and important messages from your service providers.
            </p>

            <button 
                @click="enableNotifications()" 
                :disabled="loading"
                class="w-full h-14 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 text-white font-black uppercase tracking-widest text-xs flex items-center justify-center gap-2 transition-all hover:brightness-110 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed relative z-10 mb-3"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                Enable Notifications
            </button>

            <button 
                @click="dismissNotifications()" 
                class="w-full h-12 rounded-xl bg-white/5 border border-white/10 text-white/40 font-bold uppercase tracking-widest text-[10px] flex items-center justify-center gap-2 transition-all hover:bg-white/10 hover:text-white/60 active:scale-95 relative z-10"
            >
                Maybe Later
            </button>
        </div>
    </div>

    @if(!request()->cookie('a2hs_decided'))
    <!-- Add to Home Screen prompt — shown after a booking when the page is running in a
         normal mobile browser (NOT already installed). Android/desktop get a native install
         button; iOS gets manual Share-sheet steps. Suppressed in standalone mode so the
         installed app shows the notification-consent prompt instead. -->
    <div x-data="{
             show: false,
             isIOS: false,
             isMobile: false,
             canNativeInstall: false,
             dismissed: false,
             init() {
                 const standalone = window.__isStandalone && window.__isStandalone();
                 const ua = window.navigator.userAgent || '';
                 this.isIOS = /iphone|ipad|ipod/i.test(ua) && !standalone;
                 this.isMobile = /android|iphone|ipad|ipod|mobile/i.test(ua);
                 this.canNativeInstall = !!window.__deferredInstallPrompt;
                 this.dismissed = !!localStorage.getItem('a2hs_decided')
                               || document.cookie.includes('a2hs_decided');

                 // Take over the post-booking prompt on a mobile browser that is NOT yet
                 // installed. This does NOT depend on `beforeinstallprompt` having fired —
                 // on Android that event is async and often arrives after booking, and on
                 // iOS it never fires — so we always show the prompt and fall back to manual
                 // instructions when the one-tap native install isn't available.
                 // (Installed app / desktop keep the notification-consent prompt instead.)
                 const canShow = !standalone && !this.dismissed && this.isMobile;
                 window.__a2hsCanShow = canShow;
                 if (!canShow) return;

                 // If Chrome fires beforeinstallprompt (now or later), upgrade the manual
                 // instructions to the one-tap native install button.
                 window.addEventListener('installprompt-available', () => {
                     this.canNativeInstall = true;
                 });

                 window.addEventListener('trigger-notification-prompt', () => {
                     /*
                      * Stand down for the notification ask on anything but iOS.
                      * Off iOS, push works without installing, so permission is the
                      * more valuable of the two and only one prompt may show. On
                      * iOS the reverse is true — push is impossible until the site
                      * is installed — so there this prompt still goes first.
                      */
                     const canAskNotif = (typeof Notification !== 'undefined')
                                      && Notification.permission === 'default';

                     if (!this.isIOS && canAskNotif) return;

                     if (!this.show && !this.dismissed) {
                         this.show = true;
                     }
                 });
             },
             async install() {
                 const evt = window.__deferredInstallPrompt;
                 if (!evt) { this.dismiss(); return; }
                 evt.prompt();
                 try { await evt.userChoice; } catch (e) {}
                 window.__deferredInstallPrompt = null;
                 this.persistDismiss();
                 this.show = false;
             },
             dismiss() {
                 this.persistDismiss();
                 this.show = false;
             },
             persistDismiss() {
                 this.dismissed = true;
                 document.cookie = 'a2hs_decided=true; path=/; max-age=31536000; SameSite=Lax';
                 localStorage.setItem('a2hs_decided', 'true');
             }
          }"
         x-show="show"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 2147483647; display: flex; align-items: center; justify-content: center; background: rgba(10, 15, 44, 0.95); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);"
    >
        <div style="background-color: #0a0f2c !important; position: relative !important; z-index: 2147483647 !important;" class="max-w-md w-full border border-white/10 rounded-3xl p-8 flex flex-col items-center text-center shadow-2xl overflow-hidden">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl"></div>

            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-400 flex items-center justify-center mb-6 relative z-10 shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2m-6 4v6m0 0l-2-2m2 2l2-2M4 7h16a1 1 0 011 1v11a2 2 0 01-2 2H5a2 2 0 01-2-2V8a1 1 0 011-1z" />
                </svg>
            </div>

            <h2 class="text-2xl font-black text-white tracking-tight mb-3 relative z-10">Don't miss your turn!</h2>
            <p class="text-sm text-white/60 mb-8 relative z-10 leading-relaxed">
                Add this to your home screen to get a live sound alert the moment it's your turn — no need to keep this page open.
            </p>

            <!-- Android / desktop: native install -->
            <template x-if="canNativeInstall">
                <button
                    @click="install()"
                    class="w-full h-14 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 text-white font-black uppercase tracking-widest text-xs flex items-center justify-center gap-2 transition-all hover:brightness-110 active:scale-95 relative z-10 mb-3"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16" />
                    </svg>
                    Add to Home Screen
                </button>
            </template>

            <!-- iOS Safari: no install API, show manual steps -->
            <template x-if="isIOS && !canNativeInstall">
                <div class="w-full rounded-xl bg-white/5 border border-white/10 p-5 mb-3 relative z-10 text-left">
                    <p class="text-sm text-white/80 leading-relaxed">
                        <span class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-black">1</span>
                            Tap the
                            <svg class="w-5 h-5 inline text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0-12l-3 3m3-3l3 3M6 12v6a2 2 0 002 2h8a2 2 0 002-2v-6" /></svg>
                            <strong class="text-white">Share</strong> icon
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-black">2</span>
                            Choose <strong class="text-white">Add to Home Screen</strong>
                        </span>
                    </p>
                </div>
            </template>

            <!-- Android/other browsers where the native prompt hasn't fired: manual steps -->
            <template x-if="!canNativeInstall && !isIOS">
                <div class="w-full rounded-xl bg-white/5 border border-white/10 p-5 mb-3 relative z-10 text-left">
                    <p class="text-sm text-white/80 leading-relaxed">
                        <span class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-black">1</span>
                            Tap the browser menu
                            <svg class="w-5 h-5 inline text-white/70" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-black">2</span>
                            Choose <strong class="text-white">Add to Home screen</strong> / <strong class="text-white">Install app</strong>
                        </span>
                    </p>
                </div>
            </template>

            <button
                @click="dismiss()"
                class="w-full h-12 rounded-xl bg-white/5 border border-white/10 text-white/40 font-bold uppercase tracking-widest text-[10px] flex items-center justify-center gap-2 transition-all hover:bg-white/10 hover:text-white/60 active:scale-95 relative z-10"
            >
                Maybe Later
            </button>
        </div>
    </div>
    @endif

    <div x-data="layoutData()" data-panel-type="{{ $panelType }}" class="relative z-10 flex flex-col min-h-screen">
        <!-- Navigation (Section 4) -->
        <nav @scroll.window="scrolled = (window.pageYOffset > 50)"
             :class="{ 'bg-[#0a0f2c]/80 backdrop-blur-2xl border-b border-white/5 py-3': scrolled, 'bg-transparent py-5 md:py-6': !scrolled }"
             class="fixed top-0 inset-x-0 z-[100] transition-all duration-500 px-4 md:px-8 flex items-center justify-between overflow-visible border-0 border-none">
            
            @php
                $logoHref = '/';
                if ($panelType === 'vendor') {
                    $logoHref = route('vendor.dashboard');
                } elseif ($panelType === 'employee') {
                    $logoHref = route('employee.dashboard');
                } elseif ($panelType === 'admin') {
                    $logoHref = route('admin.dashboard');
                }
            @endphp
            <div class="flex items-center gap-4 md:gap-10 {{ $panelType ? 'lg:hidden' : '' }}">
                <a href="{{ $logoHref }}" class="group flex items-center gap-2 md:gap-3">
                    {{--  <div class="nav-brand-mark w-10 h-10 md:w-12 md:h-12 rounded-xl md:rounded-2xl theme-gradient-bg flex items-center justify-center text-white text-xl md:text-2xl font-black theme-glow-sm transition-transform group-hover:rotate-12 group-hover:scale-110">
                        {{ $theme['icon'] ?? 'B' }}
                    </div>--}}
                    <img src="{{ asset('logo.png') }}?v=2" alt="Logo" class="h-12 sm:h-14 md:h-[75px] w-auto max-w-full object-contain">
                    {{--<span class="text-xl md:text-2xl font-black tracking-tighter text-white whitespace-nowrap">
                         {{ config('brand.logo_prefix') }}<span class="nav-brand-word theme-gradient-text">{{ config('brand.logo_suffix') }}</span>
                    </span>--}}
                </a>
            </div>

            <!-- Desktop Menu -->
            <div class="nav-desktop-menu items-center gap-4 ml-auto">
                <div class="flex items-center gap-10">
                    {{--  <a href="{{ route('home') }}" class="text-xs font-black uppercase tracking-widest text-white/70 hover:text-[var(--theme-primary)] transition-colors">Explore</a> --}}

                    @if(!$panelType)
                        <a href="{{ route('about') }}" class="text-xs font-black uppercase tracking-widest {{ request()->routeIs('about') ? 'text-[var(--theme-primary)]' : 'text-white/70 hover:text-[var(--theme-primary)]' }} transition-colors">About</a>
                        <a href="{{ route('contact') }}" class="text-xs font-black uppercase tracking-widest {{ request()->routeIs('contact') ? 'text-[var(--theme-primary)]' : 'text-white/70 hover:text-[var(--theme-primary)]' }} transition-colors">Contact</a>
                    @endif

                    {{-- Live bookings the visitor is holding, at any business. Shown
                         only once they hold one, so a first-time visitor is not
                         offered an empty page. --}}
                    @if(!$panelType && ($myBookingCount ?? 0) > 0)
                        <a href="{{ route('bookings.mine') }}" class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-white/70 hover:text-[var(--theme-primary)] transition-colors">
                            My Bookings
                            <span class="min-w-[20px] h-5 px-1.5 rounded-full theme-gradient-bg text-white text-[10px] font-black flex items-center justify-center">{{ $myBookingCount }}</span>
                        </a>
                    @endif

                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="/admin/dashboard" class="text-xs font-black uppercase tracking-widest text-white/70 hover:text-[var(--theme-primary)] transition-colors">Admin Portal</a>
                        @elseif(auth()->user()->isVendor())
                            <a href="/vendor/dashboard" class="text-xs font-black uppercase tracking-widest theme-gradient-text hover:brightness-110 transition-colors">Business Hub</a>
                        @endif
                    @endauth
                </div>

                <div class="h-4 w-px bg-white"></div>

                <div class="flex items-center gap-6">
                    @auth
                        <form  class="flex" method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-[10px] font-black uppercase tracking-widest text-white/30 hover:text-white transition-colors">Logout</button>
                        </form>
                    @else
                        <a href="/login" class="text-xs font-black uppercase tracking-widest text-white/70 hover:text-white">Sign In</a>
                        <a href="/register/vendor" 
                           class="theme-btn px-8 py-3 rounded-xl text-[10px] shadow-none">
                            Join Now
                        </a>
                    @endauth
                </div>
            </div>

            <div class="flex items-center gap-1">
                @if(!$panelType)
                    @php
                        // Which place the visitor is standing in. GPS now resolves a
                        // suburb-level name (see resolvePlaceName), so the pin reads
                        // "Koramangala" rather than the old generic "Current Location".
                        // Corrected on display — OSM misspells some suburbs
                        // (see config/place_names.php).
                        $navSuburb = \App\Services\PlaceNameService::correct(request()->cookie('user_suburb'));
                        $navCity   = trim((string) request()->cookie('user_city'));
                        $navState  = trim((string) request()->cookie('user_state'));
                        $navCoords = is_numeric(request()->cookie('user_lat')) && is_numeric(request()->cookie('user_lng'));

                        $navLocationSet = request()->cookie('location_granted')
                            && ($navSuburb !== '' || $navCity !== '' || $navState !== '' || $navCoords);

                        $navLocationLabel = $navSuburb ?: ($navCity ?: ($navState ?: 'Current Location'));
                        // Roomier than the old 5-character cut, which turned every
                        // suburb into an ellipsis.
                        $navLocationShort = \Illuminate\Support\Str::limit($navLocationLabel, 12, '…');
                    @endphp

                    {{-- Mobile "Near Me": bare pin icon beside the hamburger --}}
                    <button type="button"
                            class="nav-mobile-nearme {{ $navLocationSet ? 'has-location' : 'is-empty' }}"
                            title="{{ $navLocationSet ? $navLocationLabel . ' — tap to update' : 'Find experts near you' }}"
                            aria-label="{{ $navLocationSet ? 'Location: ' . $navLocationLabel . '. Tap to update' : 'Use my location' }}"
                            x-data="{
                                locating: false,
                                useGPS() {
                                    this.locating = true;
                                    /* Re-asks even after an earlier refusal; when the
                                       browser won't prompt any more the helper raises
                                       the settings steps and flags the error handled. */
                                    window.requestLocationWithHelp()
                                        .then(() => window.location.reload())
                                        .catch((error) => {
                                            this.locating = false;
                                            console.warn('Geolocation failed', error);
                                            if (error && error.handled) return;
                                            const message = error && error.message === 'unsupported'
                                                ? 'GPS not supported by this browser.'
                                                : 'Could not get your location. Please allow access and retry.';
                                            window.dispatchEvent(new CustomEvent('toast', { detail: { message, type: 'error' } }));
                                        });
                                }
                            }"
                            :class="{ 'is-locating': locating }"
                            @click="useGPS()">
                        @if($navLocationSet)
                            {{-- Location picked: a small pin plus the (truncated) place name --}}
                            <span class="nav-mobile-nearme-chip" x-show="!locating">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{-- data-location-label lets the background suburb
                                     backfill patch this in place, without a reload. --}}
                                <span class="nav-mobile-nearme-label"
                                      data-location-label
                                      data-location-max="12">{{ $navLocationShort }}</span>
                            </span>
                        @else
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-show="!locating">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        @endif
                        <svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24" x-show="locating" x-cloak>
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                @endif

                {{-- Notification bell for the vendor/employee panels: on
                     phones the sidebar (and its Notifications entry) is
                     hidden behind the hamburger, so the unread count gets a
                     glanceable spot in the header itself. Desktop already
                     shows it in the sidebar, hence lg:hidden. --}}
                @if(in_array($panelType, ['vendor', 'employee']) && auth()->check())
                    @php
                        $navUnreadNotifications = auth()->user()->unreadNotifications()->count();
                    @endphp
                    <a href="{{ route($panelType . '.notifications.index') }}"
                       class="relative w-10 h-10 rounded-xl bg-white/5/5 border border-white/10 flex lg:hidden items-center justify-center text-white transition-all hover:bg-white/5/10 active:scale-95"
                       aria-label="Notifications{{ $navUnreadNotifications > 0 ? ', ' . $navUnreadNotifications . ' unread' : '' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @if($navUnreadNotifications > 0)
                            <span class="absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 rounded-full bg-blue-500 text-white text-[9px] font-black flex items-center justify-center tabular-nums shadow-[0_0_6px_rgba(59,130,246,0.7)]">
                                {{ $navUnreadNotifications > 99 ? '99+' : $navUnreadNotifications }}
                            </span>
                        @endif
                    </a>
                @endif

                <!-- Mobile Menu Toggle -->
                <button @click="mobileMenu = !mobileMenu" class="nav-mobile-toggle w-10 h-10 rounded-xl bg-white/5/5 border border-white/10 items-center justify-center text-white transition-all hover:bg-white/5/10 active:scale-95">
                    <svg x-show="!mobileMenu" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileMenu" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </nav>

        @if(!$panelType)
        <!-- Mobile Navigation Overlay (Public Site) -->
        <div x-show="mobileMenu" 
             x-cloak
             x-transition.opacity.duration.300ms
             class="flex lg:hidden fixed inset-0 z-[300]">
             
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-[#0a0f2c]/80 backdrop-blur-md"></div>

            {{-- Modal Content --}}
            <div class="relative w-full h-full flex items-center justify-center p-4" @click="mobileMenu = false">
                <div @click.stop
                     :class="mobileMenu ? 'transform translate-y-0 scale-100' : 'transform translate-y-8 scale-95'"
                     class="transition-all duration-300 w-full max-w-sm max-h-[85vh] bg-[#0a0f2c] rounded-[2.5rem] flex flex-col shadow-[0_50px_100px_-20px_rgba(0,0,0,0.5)] overflow-hidden border border-white/10">
                    
                    <!-- Header with Close Button -->
                    <div class="flex items-center justify-between p-6 border-b border-white/10">
                        <div class="text-xl font-black text-white tracking-tighter">
                            MENU
                        </div>
                        <button @click="mobileMenu = false" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white hover:bg-slate-300 transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Scrollable Content -->
                    <div class="flex-grow overflow-y-auto p-6 no-scrollbar">
                        <div class="flex flex-col gap-4">
                            @if(isset($mobileMenu))
                                <div class="vendor-mobile-links">
                                    {{ $mobileMenu }}
                                </div>
                            @endif

                            <div class="flex flex-col gap-3">
                                {{-- Only labelled once there is a link under it —
                                     with About/Contact living in the footer, a
                                     signed-out visitor with no bookings would
                                     otherwise get a heading over nothing but the
                                     sign-in buttons. --}}
                                @php
                                    $hasPlatformLinks = ($myBookingCount ?? 0) > 0
                                        || (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isVendor()));
                                @endphp
                                @if($hasPlatformLinks)
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-2 mb-1">Platform</h4>
                                @endif
                                {{--  <a href="{{ route('home') }}" class="flex items-center gap-4 px-6 py-4 rounded-2xl bg-white/5 text-white font-black italic uppercase tracking-widest text-[11px] shadow-sm">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    Explore
                                </a>--}}

                                @if(($myBookingCount ?? 0) > 0)
                                    <a href="{{ route('bookings.mine') }}" class="flex items-center gap-4 px-6 py-4 rounded-2xl bg-white/5 text-white font-black italic uppercase tracking-widest text-[11px] shadow-sm">
                                        <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        My Bookings
                                        <span class="ml-auto min-w-[22px] h-[22px] px-1.5 rounded-full theme-gradient-bg text-white text-[10px] font-black flex items-center justify-center not-italic">{{ $myBookingCount }}</span>
                                    </a>
                                @endif

                                {{-- About Us / Contact Us deliberately absent here:
                                     both sit in the footer, which is on every
                                     public page on a phone, so repeating them in
                                     the menu was one route to the same two pages
                                     twice. The desktop nav still carries them. --}}

                                @auth
                                    @if(auth()->user()->isAdmin())
                                        <a href="/admin/dashboard" class="flex items-center gap-4 px-6 py-4 rounded-2xl bg-white/5 text-white font-black italic uppercase tracking-widest text-[11px] shadow-sm">
                                            <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                            Admin Portal
                                        </a>
                                    @elseif(auth()->user()->isVendor())
                                        <a href="/vendor/dashboard" class="flex items-center gap-4 px-6 py-4 rounded-2xl bg-white/5 text-white font-black italic uppercase tracking-widest text-[11px] shadow-sm">
                                            <svg class="w-5 h-5 theme-gradient-text" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            Business Hub
                                        </a>
                                    @endif
                                    
                                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                                        @csrf
                                        <button type="submit" class="w-full h-14 rounded-2xl bg-rose-950/20 text-rose-400 text-[10px] font-black uppercase tracking-[0.2em] italic flex items-center justify-center gap-3 border border-rose-500/20">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            Logout Account
                                        </button>
                                    </form>
                                @else
                                    <div class="grid grid-cols-1 gap-3 mt-4">
                                        <a href="/login" class="flex items-center justify-between h-16 px-8 rounded-2xl bg-white/5 border border-white/10 text-[10px] font-black uppercase tracking-widest text-white shadow-sm transition-all active:scale-[0.98]">
                                            Sign In Portal
                                            <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                        <a href="/register/vendor" class="flex items-center justify-between h-16 px-8 rounded-2xl theme-gradient-bg text-[10px] font-black uppercase tracking-widest text-white shadow-xl shadow-orange-500/20 transition-all active:scale-[0.98]">
                                            Become a Provider
                                            <svg class="w-4 h-4 text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </div>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- Mobile Navigation Slide-in Sidebar (Admin/Vendor Dashboards) -->
        <div x-show="mobileMenu"
             x-cloak
             x-transition.opacity.duration.300ms
             class="block lg:hidden fixed inset-0 z-[9999]">
             
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-[#0a0f2c]/60 backdrop-blur-sm" @click="mobileMenu = false"></div>

            <!-- Slide-in Menu -->
            <div @click.stop
                 :class="mobileMenu ? 'transform translate-x-0' : 'transform -translate-x-full'"
                 class="transition-all duration-300 dashboard-mobile-sidebar bg-[#0a0f2c] border-r border-white/5 flex flex-col">
                 
                 <!-- Header with Close Button -->
                 <div class="flex items-center justify-between p-6 border-b border-white/5">
                     <div class="text-xs font-black text-white tracking-widest uppercase">
                         Navigation
                     </div>
                     <button @click="mobileMenu = false" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-white hover:bg-white/10 transition-colors">
                         <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                     </button>
                 </div>

                 <!-- Scrollable Content -->
                 <div class="flex-grow overflow-y-auto p-6 no-scrollbar" @click="if ($event.target.closest('a')) mobileMenu = false">
                     @if(isset($mobileMenu))
                         {{ $mobileMenu }}
                     @endif

                     <div class="h-px bg-white/5 my-4"></div>
                     @unless(auth()->check() && auth()->user()->isEmployee())
                     <a href="{{ route('home') }}" class="flex items-center gap-4 px-6 py-4 rounded-xl bg-white/5 text-white font-black italic uppercase tracking-widest text-[11px] shadow-sm mb-4">
                         Explore
                     </a>
                     @endunless

                     @auth
                         <form method="POST" action="{{ route('logout') }}">
                             @csrf
                             <button type="submit" class="w-full h-12 rounded-xl bg-rose-950/20 text-rose-400 text-[10px] font-black uppercase tracking-[0.2em] italic flex items-center justify-center gap-3">
                                 Logout
                             </button>
                         </form>
                     @endauth
                 </div>
            </div>
        </div>
        @endif

        <!-- Page Content -->
        <main class="flex-grow">
            {{ $slot }}
        </main>

        @if(!$panelType && $footerMode === 'minimal')
        <!-- Minimal footer: copyright bar only, no link columns or curved top -->
        <footer class="bg-[#0a0f2c] py-8 border-t border-white/5">
            <div class="container mx-auto px-4 md:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-center md:text-left">
                    <div class="text-[10px] font-black uppercase tracking-[0.4em] text-white/20">
                        &copy; {{ date('Y') }} {{ strtoupper(config('brand.platform')) }}. ALL RIGHTS RESERVED.
                    </div>
                    <div class="flex flex-wrap justify-center gap-6 text-[9px] font-black uppercase tracking-[0.3em] text-white/30">
                        <a href="{{ route('privacy') }}" class="hover:text-[var(--theme-primary)] transition-colors">Privacy</a>
                        <a href="{{ route('terms') }}" class="hover:text-[var(--theme-primary)] transition-colors">Terms</a>
                        <a href="{{ route('about') }}" class="hover:text-[var(--theme-primary)] transition-colors">About</a>
                        <a href="{{ route('contact') }}" class="hover:text-[var(--theme-primary)] transition-colors">Contact</a>
                    </div>
                </div>
            </div>
        </footer>
        @elseif(!$panelType)
        <!-- Footer (Section 12) - Hidden on Dashboards -->
        <footer class="site-footer-curved bg-[#0a0f2c] pt-24 pb-12 border-t border-white/5">
            <div class="container mx-auto px-4 md:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-10 mb-16 px-4">
                    <div class="flex flex-col items-center md:items-start gap-4">
                        <div class="text-3xl font-black text-white tracking-tighter">{{ config('brand.logo_prefix') }}<span class="theme-gradient-text">{{ config('brand.footer_suffix') }}</span></div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.3em] max-w-sm text-center md:text-left text-white/30 leading-loose">
                            {{ config('brand.tagline') }}
                        </p>
                    </div>
                    
                    <div class="flex flex-wrap justify-center gap-8 md:gap-10 text-[9px] font-black uppercase tracking-[0.3em] text-white/40">
                        <a href="{{ route('about') }}" class="hover:text-[var(--theme-primary)] transition-colors">About Us</a>
                        <a href="{{ route('contact') }}" class="hover:text-[var(--theme-primary)] transition-colors">Contact</a>
                        <a href="{{ route('privacy') }}" class="hover:text-[var(--theme-primary)] transition-colors">Privacy</a>
                        <a href="{{ route('terms') }}" class="hover:text-[var(--theme-primary)] transition-colors">Terms</a>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row items-center justify-between pt-12 border-t border-white/5 gap-6">
                    <div class="text-[10px] font-black uppercase tracking-[0.4em] text-white/20">
                        &copy; {{ date('Y') }} {{ strtoupper(config('brand.platform')) }}. ALL RIGHTS RESERVED.
                    </div>
                    {{-- Social handles come from the admin settings screen; a network
                         with no URL saved is simply not shown. --}}
                    @php
                        $footerSocials = array_filter([
                            '𝕏'  => \App\Models\SiteSetting::get('social_twitter'),
                            '📸' => \App\Models\SiteSetting::get('social_instagram'),
                            'f'  => \App\Models\SiteSetting::get('social_facebook'),
                            '💼' => \App\Models\SiteSetting::get('social_linkedin'),
                        ]);
                    @endphp
                    @if(!empty($footerSocials))
                        <div class="flex items-center gap-6">
                            @foreach($footerSocials as $glyph => $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener"
                                   class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-white/40 hover:text-white transition-colors border border-white/10">{{ $glyph }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </footer>
        @endif
    </div>

    <!-- Notification Sound -->
    <audio id="notification-sound" src="{{ asset('audio/notification.wav') }}" preload="auto"></audio>

    <!-- Toast Notifications -->
    <div x-data="{
        show: false, message: '', type: 'success', timer: null,
        triggerToast(msg, type = 'success', playSound = false) {
            this.message = msg; this.type = type; this.show = true;
            
            if (playSound) {
                let sound = document.getElementById('notification-sound');
                if (sound) {
                    sound.currentTime = 0;
                    let playPromise = sound.play();
                    if (playPromise !== undefined) {
                        playPromise.catch(error => console.log('Audio playback prevented by browser:', error));
                    }
                }
            }

            if(this.timer) clearTimeout(this.timer);
            this.timer = setTimeout(() => this.show = false, 4000);
        },
        init() {
            @if(session('success'))
                setTimeout(() => this.triggerToast('{{ addslashes(session('success')) }}', 'success'), 100);
            @endif
            @if(session('error'))
                setTimeout(() => this.triggerToast('{{ addslashes(session('error')) }}', 'error'), 100);
            @endif
            @if($errors->any())
                @php
                    $allErrors = implode(' | ', $errors->all());
                @endphp
                setTimeout(() => this.triggerToast('{{ addslashes($allErrors) }}', 'error'), 100);
            @endif
        }
    }"
    @toast.window="triggerToast($event.detail.message, $event.detail.type, $event.detail.sound)"
    class="fixed bottom-12 left-1/2 -translate-x-1/2 z-[9999] pointer-events-none">
        <div x-show="show"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 translate-y-20 scale-90"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-400"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-20 scale-90"
             class="px-8 py-5 rounded-[2rem] shadow-[0_30px_60px_-15px_rgba(0,0,0,0.1)] flex items-center gap-4 min-w-[320px] backdrop-blur-3xl border pointer-events-auto"
             :class="{
                'bg-emerald-600/90 text-white border-emerald-400/30': type === 'success',
                'bg-rose-600/90 text-white border-rose-400/30': type === 'error',
                'bg-blue-600/90 text-white border-blue-400/30': type === 'info'
             }" x-cloak>
            <div class="w-10 h-10 rounded-2xl bg-white/5/20 flex items-center justify-center shrink-0">
                <template x-if="type === 'success'"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></template>
                <template x-if="type === 'error'"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg></template>
            </div>
            <p class="font-black text-sm tracking-tight" x-text="message"></p>
        </div>
    </div>

    @livewireScripts

    <script>
        // This device's FCM registration token, once we have one. Null until the
        // silent registration below succeeds (it only runs when notification
        // permission was already granted), so always treat it as optional.
        window.__fcmToken = window.__fcmToken || null;

        // Define permission handler FIRST to guarantee it is available even if FCM fails
        window.__requestNotificationPermission = function() {
            if (!('Notification' in window)) {
                console.log('This browser does not support desktop notification');
                return;
            }
            
            Notification.requestPermission().then((permission) => {
                if (permission === 'granted') {
                    console.log('Notification permission granted.');
                    if (typeof window.__registerFcmTokenSilently === 'function') {
                        window.__registerFcmTokenSilently();
                    }
                } else {
                    console.log('Unable to get permission to notify.');
                }
            });
        };

        // Deferred Firebase scripts finish loading before DOMContentLoaded fires,
        // so initialise here to avoid blocking first paint and to guarantee
        // `firebase` is defined.
        document.addEventListener('DOMContentLoaded', function () {
        try {
            // Firebase initialization
            const firebaseConfig = {
                apiKey: "{{ env('FIREBASE_API_KEY', 'YOUR_API_KEY') }}",
                projectId: "apni-baari-6d2b0",
                messagingSenderId: "{{ env('FIREBASE_MESSAGING_SENDER_ID', '831015537203') }}",
                appId: "{{ env('FIREBASE_APP_ID', 'YOUR_APP_ID') }}"
            };

            if (!firebase.apps.length) {
                firebase.initializeApp(firebaseConfig);
            }

            let messaging = null;
            if (firebase.messaging.isSupported()) {
                messaging = firebase.messaging();
            } else {
                console.warn('Firebase Messaging is not supported by this browser.');
            }

            // Register FCM token WITHOUT prompting — only if permission was already granted
            window.__registerFcmTokenSilently = function() {
                if (!messaging) return;
                
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.register('/firebase-messaging-sw.js?v=7')
                        .then((registration) => {
                            messaging.useServiceWorker(registration);
                            return messaging.getToken({ vapidKey: "{{ env('FIREBASE_VAPID_KEY', 'YOUR_VAPID_KEY') }}" });
                        })
                        .then((currentToken) => {
                            if (currentToken) {
                                console.log('FCM Token:', currentToken);
                                // Published so anything else on the page can send
                                // the device's push address with its own request —
                                // the review modal's Google sign-in stamps it onto
                                // the account it signs the visitor into.
                                window.__fcmToken = currentToken;
                                fetch("{{ route('fcm.token.save') }}", {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({ token: currentToken })
                                }).then(res => res.json()).then(data => {
                                    console.log('Token saved to server.', data);
                                }).catch((err) => {
                                    console.log('Unable to save token to server.', err);
                                });
                            }
                        })
                        .catch((err) => {
                            console.log('An error occurred while retrieving token. ', err);
                        });
                }
            };

            if (messaging) {
                // On foreground message
                messaging.onMessage((payload) => {
                    console.log('Message received. ', payload);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: payload.notification.title + ': ' + payload.notification.body, type: 'info', sound: true } }));
                });
            }

            // Listen to messages from the Service Worker (e.g. background notification audio requests)
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.addEventListener('message', function(event) {
                    if (event.data && event.data.type === 'PLAY_NOTIFICATION_SOUND') {
                        console.log('Received notification sound request from Service Worker');
                        let sound = document.getElementById('notification-sound');
                        if (sound) {
                            sound.currentTime = 0;
                            let playPromise = sound.play();
                            if (playPromise !== undefined) {
                                playPromise.catch(error => console.log('Audio playback prevented by browser:', error));
                            }
                        }
                    }
                });
            }

            // If permission was already granted previously, silently register the token — NO prompt
            if ('Notification' in window && Notification.permission === 'granted') {
                setTimeout(() => {
                    if (typeof window.__registerFcmTokenSilently === 'function') {
                        window.__registerFcmTokenSilently();
                    }
                }, 2000);
            }
            
        } catch (error) {
            console.error('Firebase initialization or messaging setup failed:', error);
        }
        });
    </script>

    <script>
        /* ══════════════════════════════════════════════════════════════════
           AUTO-SLIDING CAROUSELS — MOBILE ONLY

           Opt in from any horizontal scroller with `data-auto-slide` (and an
           optional `data-auto-slide-interval` in ms). Used by the category
           strip on the listing page and the reviews strip on a vendor profile.

           Desktop is untouched, and not merely by the media query: every one
           of these containers stops being a horizontal scroller above its
           breakpoint (the category strip is display:none, the review list
           becomes a vertical column), so `scrollWidth === clientWidth` and the
           tick bails before it can move anything. The matchMedia gate is the
           second lock, not the only one.

           It advances by one real item — measured from the live DOM each tick,
           because the review strip is rendered by Alpine's x-for and its
           children appear late and change when a star filter is applied. At
           either end it reverses instead of rewinding, which avoids whipping
           the whole strip back past every card.
           ══════════════════════════════════════════════════════════════════ */
        (function () {
            'use strict';

            var MOBILE  = window.matchMedia('(max-width: 767px)');
            var REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)');

            // How long to stay out of the way after the user touches a slider.
            var RESUME_AFTER = 6000;

            function AutoSlide(el) {
                var period  = parseInt(el.dataset.autoSlideInterval, 10) || 3500;
                var timer   = null;
                var idle    = null;
                var inView  = false;
                var dir     = 1;

                // One item's advance: the gap between the first two children,
                // which already includes flex gap and padding. Falls back to a
                // near-viewport step for a strip that is still empty.
                function stride() {
                    var kids = el.children;
                    if (kids.length > 1) {
                        var d = kids[1].offsetLeft - kids[0].offsetLeft;
                        if (d > 8) return d;
                    }
                    return Math.round(el.clientWidth * 0.85);
                }

                function tick() {
                    var max = el.scrollWidth - el.clientWidth;
                    if (max <= 4) return;              // nothing to scroll (desktop layout)

                    var next = el.scrollLeft + dir * stride();
                    if (next >= max - 2) { next = max; dir = -1; }
                    else if (next <= 2)  { next = 0;   dir =  1; }

                    el.scrollTo({ left: next, behavior: 'smooth' });
                }

                function start() {
                    if (timer || !inView || !MOBILE.matches || REDUCED.matches) return;
                    if (document.hidden) return;
                    timer = setInterval(tick, period);
                }

                function stop() {
                    if (timer) { clearInterval(timer); timer = null; }
                }

                // Hand control back to the user the moment they touch it, and
                // pick up again only once they have been still for a while.
                // Deliberately keyed off real input events rather than the
                // scroll event — our own smooth scrolling fires that too.
                function yieldToUser() {
                    stop();
                    clearTimeout(idle);
                    idle = setTimeout(start, RESUME_AFTER);
                }

                ['pointerdown', 'touchstart', 'wheel', 'keydown'].forEach(function (evt) {
                    el.addEventListener(evt, yieldToUser, { passive: true });
                });

                // Only animate while the strip is actually on screen.
                if ('IntersectionObserver' in window) {
                    new IntersectionObserver(function (entries) {
                        inView = entries[0].isIntersecting;
                        inView ? start() : stop();
                    }, { threshold: 0.25 }).observe(el);
                } else {
                    inView = true;
                    start();
                }

                document.addEventListener('visibilitychange', function () {
                    document.hidden ? stop() : start();
                });

                var onBreakpoint = function () { stop(); start(); };
                MOBILE.addEventListener
                    ? MOBILE.addEventListener('change', onBreakpoint)
                    : MOBILE.addListener(onBreakpoint);
            }

            function init() {
                document.querySelectorAll('[data-auto-slide]').forEach(function (el) {
                    if (el.dataset.autoSlideBound) return;
                    el.dataset.autoSlideBound = '1';
                    AutoSlide(el);
                });
            }

            document.readyState === 'loading'
                ? document.addEventListener('DOMContentLoaded', init)
                : init();
        })();
    </script>

    {{-- Shared dropdown for every <select> on the site (all panels render
         through this layout). See the partial for what it fixes. --}}
    @include('partials.custom-select')

    {{-- Distance warning modal for vendor listings --}}
    <x-distance-warning-modal />

    <script>
        function layoutData() {
            return {
                scrolled: false,
                mobileMenu: false,
                showDistanceWarning: false,
                distanceWarning: null,
                pendingVendorUrl: null,

                async handleVendorClick(event, vendorUrl) {
                    event.preventDefault();

                    try {
                        // Check if vendor has location enabled (cookie exists)
                        const userLat = document.cookie.split('; ').find(row => row.startsWith('user_lat'))?.split('=')[1];
                        const userLng = document.cookie.split('; ').find(row => row.startsWith('user_lng'))?.split('=')[1];

                        // Only check distance if user has shared location
                        if (userLat && userLng) {
                            const response = await fetch(vendorUrl, {
                                headers: {
                                    'Accept': 'application/json',
                                },
                            });

                            const data = await response.json();

                            if (data.distance_warning) {
                                this.distanceWarning = data;
                                this.pendingVendorUrl = vendorUrl;
                                this.showDistanceWarning = true;
                                return;
                            }
                        }

                        // No warning or no location, proceed normally
                        window.location.href = vendorUrl;
                    } catch (error) {
                        console.error('Error checking vendor distance:', error);
                        // On error, allow navigation
                        window.location.href = vendorUrl;
                    }
                },

                async confirmDistanceWarning() {
                    if (this.pendingVendorUrl) {
                        window.location.href = this.pendingVendorUrl;
                    }
                    this.showDistanceWarning = false;
                    this.distanceWarning = null;
                    this.pendingVendorUrl = null;
                }
            };
        }
    </script>
</body>
</html>
