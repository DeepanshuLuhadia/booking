<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Kiosk/TV page: never let the platform behind a slow phone shrink the
         text or let the browser chrome nag about installing anything. --}}
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $vendor->business_name }} — Live Status</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        // Minimal copy of the app-layout helper: this page has no header of its
        // own, so it defines its own gate for realtime listeners registered
        // before the deferred Echo bundle has finished loading.
        window.whenRealtimeReady = function (callback) {
            var start = function () { if (window.Echo) callback(window.Echo); };
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', start, { once: true });
            } else {
                start();
            }
        };
    </script>

    <style>
        {!! \App\Services\ThemeService::getCssVars($theme) !!}
        html, body { height: 100%; }
        body {
            margin: 0;
            font-family: 'Outfit', sans-serif;
            background: var(--theme-body-bg);
            color: var(--theme-body-text);
            overflow-x: hidden;
            overflow-y: auto;
        }
        .kiosk-shell { min-height: 100vh; display: flex; flex-direction: column; }
        .kiosk-header {
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
            padding: 1.5rem clamp(1.25rem, 4vw, 3rem); border-bottom: 1px solid rgba(255,255,255,.08);
            flex-wrap: wrap;
        }
        .kiosk-brand { font-size: clamp(1.2rem, 2.4vw, 2.2rem); font-weight: 900; letter-spacing: -.02em; }
        .kiosk-status {
            display: inline-flex; align-items: center; gap: .6rem;
            padding: .55rem 1.1rem; border-radius: 999px; font-size: .8rem;
            font-weight: 800; text-transform: uppercase; letter-spacing: .08em;
            border: 1px solid rgba(255,255,255,.12);
        }
        .kiosk-dot { width: .55rem; height: .55rem; border-radius: 999px; }
        .kiosk-clock { font-variant-numeric: tabular-nums; font-weight: 800; font-size: clamp(1.1rem, 1.8vw, 1.6rem); opacity: .75; }
        .kiosk-stage { flex: 1; display: flex; align-items: center; justify-content: center; padding: 1.5rem clamp(1rem, 4vw, 3rem) 3rem; position: relative; }
        .kiosk-card {
            width: min(1100px, 100%);
            border-radius: clamp(1.5rem, 4vw, 2.5rem);
            border: 1px solid var(--theme-card-border);
            background: var(--theme-card-bg);
            box-shadow: var(--theme-card-shadow);
            padding: clamp(1.75rem, 5vw, 4.5rem);
            display: flex; align-items: center; gap: clamp(1.5rem, 4vw, 4rem);
            backdrop-filter: blur(20px);
        }
        .kiosk-photo {
            width: clamp(6rem, 16vw, 15rem); height: clamp(6rem, 16vw, 15rem);
            border-radius: 1.5rem; flex-shrink: 0; overflow: hidden;
            background: var(--theme-hero-gradient);
            display: flex; align-items: center; justify-content: center;
            font-size: clamp(2rem, 6vw, 5rem); font-weight: 900; color: var(--theme-text-on-primary);
        }
        .kiosk-photo img { width: 100%; height: 100%; object-fit: cover; }
        .kiosk-name { font-size: clamp(1.5rem, 3.4vw, 3.2rem); font-weight: 900; letter-spacing: -.02em; margin: 0 0 .5rem; word-break: break-word; }
        .kiosk-label { font-size: clamp(.75rem, 1.2vw, 1rem); font-weight: 800; text-transform: uppercase; letter-spacing: .12em; opacity: .55; margin-bottom: .35rem; }
        .kiosk-figure { font-size: clamp(2.5rem, 9vw, 7.5rem); font-weight: 900; line-height: 1; letter-spacing: -.03em; color: var(--theme-primary); }
        .kiosk-figure--idle { font-size: clamp(1rem, 2.2vw, 1.4rem); text-transform: uppercase; letter-spacing: .06em; opacity: .5; color: var(--theme-body-text); }
        .kiosk-sub { margin-top: .75rem; font-size: clamp(1rem, 1.6vw, 1.35rem); font-weight: 700; opacity: .8; }
        {{-- Below this, side-by-side photo+text no longer has room to breathe
             (the mode pill and labels started wrapping mid-word). Stack
             instead, matching the single-employee display page. --}}
        @media (max-width: 680px) {
            .kiosk-card { flex-direction: column; text-align: center; }
            .kiosk-photo { margin: 0 auto; }
        }
        .kiosk-chip {
            display: inline-flex; align-items: center; gap: .5rem; margin-top: 1.25rem;
            padding: .5rem 1rem; border-radius: 999px; font-size: .85rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: .06em; background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
        }
        .kiosk-paused {
            display: inline-flex; align-items: center; gap: .6rem; margin-top: 1.25rem;
            padding: .6rem 1.2rem; border-radius: 999px; font-size: .9rem; font-weight: 900;
            text-transform: uppercase; letter-spacing: .08em;
            background: rgba(245, 158, 11, .15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, .35);
        }
        .kiosk-dots { display: flex; justify-content: center; gap: .6rem; padding-bottom: 2.5rem; }
        .kiosk-dots span {
            width: .65rem; height: .65rem; border-radius: 999px; background: rgba(255,255,255,.18);
            transition: background .3s ease, transform .3s ease;
        }
        .kiosk-dots span.active { background: var(--theme-primary); transform: scale(1.3); }
        .kiosk-empty { text-align: center; opacity: .5; font-weight: 700; font-size: 1.4rem; }
        .kiosk-mode-pill {
            font-size: .7rem; font-weight: 800; text-transform: uppercase; letter-spacing: .1em;
            padding: .3rem .7rem; border-radius: 999px; background: rgba(255,255,255,.08); opacity: .7;
        }
    </style>
</head>
@php
    $vendorState = ['id' => $vendor->id, 'business_name' => $vendor->business_name, 'is_open' => $vendor->is_currently_open, 'bookings_paused' => (bool) $vendor->bookings_paused];
    $dataUrl = route('vendor.display.data', $vendor->slug);
@endphp
<body>
    <div class="kiosk-shell"
        x-data="vendorDisplaySystem({{ \Illuminate\Support\Js::from($panels) }}, {{ \Illuminate\Support\Js::from($vendorState) }}, '{{ $dataUrl }}')"
        {{-- No x-init: init() is Alpine's magic lifecycle hook and already runs
             automatically. Calling it again here double-registered every timer,
             which made a 2-employee slider cancel its own advance every tick. --}}>

        <div class="kiosk-header">
            <div class="kiosk-brand">{{ $vendor->business_name }}</div>
            <div style="display:flex; align-items:center; gap:1.5rem;">
                <span class="kiosk-status" :style="vendor.is_open && !vendor.bookings_paused ? 'color:#34d399' : 'color:#fb7185'">
                    <span class="kiosk-dot" :style="vendor.is_open && !vendor.bookings_paused ? 'background:#34d399' : 'background:#fb7185'"></span>
                    <span x-text="vendor.is_open ? (vendor.bookings_paused ? 'Bookings Paused' : 'Open Now') : 'Closed'"></span>
                </span>
                <span class="kiosk-clock" x-text="clock"></span>
            </div>
        </div>

        <div class="kiosk-stage">
            <template x-if="panels.length === 0">
                <div class="kiosk-empty">No specialists on duty right now.</div>
            </template>

            <template x-for="(panel, i) in panels" :key="panel.id">
                <div class="kiosk-card" x-show="i === index" x-cloak>
                    <div class="kiosk-photo">
                        <img :src="panel.photo_url" x-show="panel.photo_url" alt="">
                        <span x-show="!panel.photo_url" x-text="panel.name.charAt(0)"></span>
                    </div>
                    <div style="min-width:0;">
                        <span class="kiosk-mode-pill" x-text="panel.mode === 'token' ? 'Token Queue' : (panel.mode === 'hybrid' ? 'Token + Appointments' : 'By Appointment')"></span>
                        <h1 class="kiosk-name" x-text="panel.name"></h1>

                        <template x-if="panel.is_paused">
                            <div class="kiosk-paused">
                                <svg style="width:1.1em;height:1.1em" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                On Break
                            </div>
                        </template>

                        <template x-if="!panel.is_paused && (panel.mode === 'token' || panel.mode === 'hybrid')">
                            <div>
                                <div class="kiosk-label" x-text="panel.serving_label"></div>
                                <template x-if="panel.serving_display !== '—'">
                                    <div class="kiosk-figure" x-text="panel.serving_display"></div>
                                </template>
                                <template x-if="panel.serving_display === '—'">
                                    <div class="kiosk-figure kiosk-figure--idle">No One Waiting</div>
                                </template>
                                <div class="kiosk-chip">
                                    <span x-text="panel.waiting_count"></span>&nbsp;waiting
                                </div>
                            </div>
                        </template>

                        <template x-if="!panel.is_paused && (panel.mode === 'time_slot' || panel.mode === 'hybrid')">
                            <div :style="panel.mode === 'hybrid' ? 'margin-top:1.75rem;' : ''">
                                <div class="kiosk-label">Next Appointment</div>
                                <template x-if="panel.next_appointment">
                                    <div class="kiosk-sub" style="font-size:clamp(1.6rem,3vw,2.4rem); color: var(--theme-primary); font-weight:900;">
                                        <span x-text="panel.next_appointment.date_label"></span> · <span x-text="panel.next_appointment.time"></span>
                                    </div>
                                </template>
                                <template x-if="!panel.next_appointment">
                                    <div class="kiosk-sub">No upcoming appointment</div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <div class="kiosk-dots" x-show="panels.length > 1">
            <template x-for="(panel, i) in panels" :key="'dot-' + panel.id">
                <span :class="{ active: i === index }"></span>
            </template>
        </div>
    </div>

    <script>
        function vendorDisplaySystem(initialPanels, initialVendor, dataUrl) {
            return {
                panels: initialPanels,
                vendor: initialVendor,
                index: 0,
                clock: '',
                init() {
                    if (this.panels.length > 1) {
                        setInterval(() => { this.index = (this.index + 1) % this.panels.length; }, 2000);
                    }
                    const tickClock = () => { this.clock = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }); };
                    tickClock();
                    setInterval(tickClock, 1000);

                    window.whenRealtimeReady((Echo) => {
                        Echo.channel(`shop.${this.vendor.id}`).listen('.shop.status', () => this.refresh());
                        this.panels.forEach((panel) => {
                            Echo.channel(`queue.${panel.id}`).listen('.queue.updated', () => this.refresh());
                        });
                    });

                    // Fallback for when the socket never connects (Reverb down,
                    // blocked network) — the screen still stays roughly live.
                    setInterval(() => {
                        if (!window.Realtime || !window.Realtime.connected()) this.refresh();
                    }, 20000);
                },
                async refresh() {
                    try {
                        const res = await fetch(dataUrl, { headers: { Accept: 'application/json' } });
                        if (!res.ok) return;
                        const data = await res.json();
                        this.vendor = data.vendor;
                        this.panels = data.employees;
                        if (this.index >= this.panels.length) this.index = 0;
                    } catch (e) {
                        // Keep showing the last known state; try again on the next tick.
                    }
                },
            };
        }
    </script>
</body>
</html>
