<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $employee->name }} — Live Status</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
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
        {{--
            A header ROW in normal flow, not position:fixed. Fixed badges used to
            sit at the page corners while the card was centered independently —
            on any viewport short/narrow enough for the card to reach a corner,
            the card's own stacking context (created by its backdrop-filter)
            painted on top of them and hid the badges entirely. A shared column
            layout can't overlap itself.
        --}}
        .kiosk-shell { min-height: 100vh; display: flex; flex-direction: column; }
        .kiosk-header {
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
            padding: 1.5rem clamp(1.25rem, 4vw, 3rem);
            flex-wrap: wrap;
        }
        .kiosk-clock {
            font-variant-numeric: tabular-nums; font-weight: 800;
            font-size: clamp(1.1rem, 1.8vw, 1.6rem); opacity: .6;
        }
        .kiosk-status {
            display: inline-flex; align-items: center; gap: .6rem;
            padding: .55rem 1.1rem; border-radius: 999px; font-size: .8rem;
            font-weight: 800; text-transform: uppercase; letter-spacing: .08em;
            border: 1px solid rgba(255,255,255,.12);
        }
        .kiosk-dot { width: .55rem; height: .55rem; border-radius: 999px; flex-shrink: 0; }
        .kiosk-stage {
            flex: 1; display: flex; align-items: center; justify-content: center;
            padding: 1rem clamp(1rem, 4vw, 3rem) 3rem;
        }
        .kiosk-card {
            width: min(1000px, 100%);
            border-radius: clamp(1.5rem, 4vw, 3rem);
            border: 1px solid var(--theme-card-border);
            background: var(--theme-card-bg);
            box-shadow: var(--theme-card-shadow);
            padding: clamp(1.75rem, 5vw, 5rem);
            display: flex; flex-direction: column; align-items: center; text-align: center;
            backdrop-filter: blur(20px);
        }
        .kiosk-photo {
            width: clamp(7rem, 18vw, 17rem); height: clamp(7rem, 18vw, 17rem);
            border-radius: 2rem; overflow: hidden; margin-bottom: 1.5rem;
            background: var(--theme-hero-gradient);
            display: flex; align-items: center; justify-content: center;
            font-size: clamp(2.5rem, 7vw, 6rem); font-weight: 900; color: var(--theme-text-on-primary);
        }
        .kiosk-photo img { width: 100%; height: 100%; object-fit: cover; }
        .kiosk-brand { font-size: clamp(.85rem, 1.6vw, 1.2rem); font-weight: 800; opacity: .55; text-transform: uppercase; letter-spacing: .1em; margin-bottom: .5rem; }
        .kiosk-name { font-size: clamp(1.6rem, 5vw, 4rem); font-weight: 900; letter-spacing: -.02em; margin: 0 0 1.5rem; word-break: break-word; }
        .kiosk-label { font-size: clamp(.8rem, 1.3vw, 1.05rem); font-weight: 800; text-transform: uppercase; letter-spacing: .12em; opacity: .55; margin-bottom: .5rem; }
        .kiosk-figure { font-size: clamp(3rem, 11vw, 9rem); font-weight: 900; line-height: 1; letter-spacing: -.03em; color: var(--theme-primary); }
        .kiosk-figure--idle { font-size: clamp(1.1rem, 2.4vw, 1.5rem); text-transform: uppercase; letter-spacing: .06em; opacity: .5; color: var(--theme-body-text); }
        .kiosk-chip {
            display: inline-flex; align-items: center; gap: .5rem; margin-top: 1.5rem;
            padding: .6rem 1.3rem; border-radius: 999px; font-size: .95rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: .06em; background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
        }
        .kiosk-paused {
            display: inline-flex; align-items: center; gap: .7rem; margin-top: .5rem;
            padding: .75rem 1.5rem; border-radius: 999px; font-size: 1.05rem; font-weight: 900;
            text-transform: uppercase; letter-spacing: .08em;
            background: rgba(245, 158, 11, .15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, .35);
        }
        .kiosk-block + .kiosk-block { margin-top: 2rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,.1); }
        .kiosk-next-time { font-size: clamp(1.6rem, 4.5vw, 3.5rem); font-weight: 900; color: var(--theme-primary); }
    </style>
</head>
@php
    $vendorState = ['id' => $vendor->id, 'is_open' => $vendor->is_currently_open, 'bookings_paused' => (bool) $vendor->bookings_paused];
    $dataUrl = route('employee.display.data', $employee->slug ?? $employee->id);
@endphp
<body x-data="employeeDisplaySystem({{ \Illuminate\Support\Js::from($panel) }}, {{ \Illuminate\Support\Js::from($vendorState) }}, '{{ $dataUrl }}')">
    <div class="kiosk-shell">
        <div class="kiosk-header">
            <span class="kiosk-status" :style="vendor.is_open && !vendor.bookings_paused ? 'color:#34d399' : 'color:#fb7185'">
                <span class="kiosk-dot" :style="vendor.is_open && !vendor.bookings_paused ? 'background:#34d399' : 'background:#fb7185'"></span>
                <span x-text="vendor.is_open ? (vendor.bookings_paused ? 'Bookings Paused' : 'Open Now') : 'Closed'"></span>
            </span>
            <span class="kiosk-clock" x-text="clock"></span>
        </div>

        <div class="kiosk-stage">
            <div class="kiosk-card">
                <div class="kiosk-photo">
                    <img :src="panel.photo_url" x-show="panel.photo_url" alt="">
                    <span x-show="!panel.photo_url" x-text="panel.name.charAt(0)"></span>
                </div>
                <div class="kiosk-brand">{{ $vendor->business_name }}</div>
                <h1 class="kiosk-name" x-text="panel.name"></h1>

                <template x-if="panel.is_paused">
                    <div class="kiosk-paused">
                        <svg style="width:1.2em;height:1.2em" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        On Break
                    </div>
                </template>

                <template x-if="!panel.is_paused && (panel.mode === 'token' || panel.mode === 'hybrid')">
                    <div class="kiosk-block">
                        <div class="kiosk-label" x-text="panel.serving_label"></div>
                        {{-- serving_display is a bare '—' whenever there is nobody
                             in the chair and nobody waiting — true whether the
                             queue never started or just ran empty. A raw dash at
                             this font size reads as a rendering glitch, not a
                             status, so it gets a worded fallback instead. --}}
                        <template x-if="panel.serving_display !== '—'">
                            <div class="kiosk-figure" x-text="panel.serving_display"></div>
                        </template>
                        <template x-if="panel.serving_display === '—'">
                            <div class="kiosk-figure kiosk-figure--idle">No One Waiting</div>
                        </template>
                        <div class="kiosk-chip"><span x-text="panel.waiting_count"></span>&nbsp;waiting</div>
                    </div>
                </template>

                <template x-if="!panel.is_paused && (panel.mode === 'time_slot' || panel.mode === 'hybrid')">
                    <div :class="panel.mode === 'hybrid' ? 'kiosk-block' : ''">
                        <div class="kiosk-label">Next Appointment</div>
                        <template x-if="panel.next_appointment">
                            <div class="kiosk-next-time">
                                <span x-text="panel.next_appointment.date_label"></span> · <span x-text="panel.next_appointment.time"></span>
                            </div>
                        </template>
                        <template x-if="!panel.next_appointment">
                            <div class="kiosk-chip" style="margin-top:.5rem;">No upcoming appointment</div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        function employeeDisplaySystem(initialPanel, initialVendor, dataUrl) {
            return {
                panel: initialPanel,
                vendor: initialVendor,
                clock: '',
                init() {
                    const tickClock = () => { this.clock = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }); };
                    tickClock();
                    setInterval(tickClock, 1000);

                    window.whenRealtimeReady((Echo) => {
                        Echo.channel(`queue.${this.panel.id}`).listen('.queue.updated', () => this.refresh());
                        Echo.channel(`shop.${this.vendor.id}`).listen('.shop.status', () => this.refresh());
                    });

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
                        this.panel = data.employee;
                    } catch (e) {
                        // Keep showing the last known state; try again on the next tick.
                    }
                },
            };
        }
    </script>
</body>
</html>
