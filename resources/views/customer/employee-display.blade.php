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
            position: relative;
        }
        {{--
            Two soft, theme-colored glows pinned to opposite corners — purely
            decorative, `pointer-events:none` and behind everything else, so a
            TV-mounted display reads as designed rather than a bare card
            floating on a flat background.
        --}}
        body::before, body::after {
            content: ''; position: fixed; width: 60vmax; height: 60vmax; border-radius: 999px;
            background: var(--theme-hero-gradient); opacity: .18; filter: blur(90px);
            pointer-events: none; z-index: 0;
        }
        body::before { top: -20vmax; left: -20vmax; }
        body::after { bottom: -20vmax; right: -20vmax; }
        {{--
            A header ROW in normal flow, not position:fixed. Fixed badges used to
            sit at the page corners while the card was centered independently —
            on any viewport short/narrow enough for the card to reach a corner,
            the card's own stacking context (created by its backdrop-filter)
            painted on top of them and hid the badges entirely. A shared column
            layout can't overlap itself.
        --}}
        .kiosk-shell { position: relative; z-index: 1; min-height: 100vh; display: flex; flex-direction: column; }
        .kiosk-header {
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
            padding: 1.5rem clamp(1.25rem, 4vw, 3rem);
            flex-wrap: wrap;
        }
        .kiosk-clock {
            display: inline-flex; align-items: center; gap: .5rem;
            font-variant-numeric: tabular-nums; font-weight: 800;
            font-size: clamp(1.1rem, 1.8vw, 1.6rem); opacity: .7;
        }
        .kiosk-clock svg { width: 1.1em; height: 1.1em; opacity: .7; }
        .kiosk-status {
            display: inline-flex; align-items: center; gap: .6rem;
            padding: .55rem 1.1rem; border-radius: 999px; font-size: .8rem;
            font-weight: 800; text-transform: uppercase; letter-spacing: .08em;
            border: 1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.04);
        }
        .kiosk-dot { width: .55rem; height: .55rem; border-radius: 999px; flex-shrink: 0; box-shadow: 0 0 0 4px rgba(255,255,255,.12); }
        .kiosk-stage {
            flex: 1; display: flex; align-items: center; justify-content: center;
            padding: 1rem clamp(1rem, 4vw, 3rem) 3rem;
        }
        .kiosk-card {
            position: relative;
            width: min(720px, 100%);
            border-radius: clamp(1.5rem, 4vw, 3rem);
            border: 1px solid var(--theme-card-border);
            background: var(--theme-card-bg);
            box-shadow: var(--theme-card-shadow), 0 0 0 1px var(--theme-primary) inset;
            padding: clamp(1.75rem, 5vw, 4rem);
            display: flex; flex-direction: column; align-items: center; text-align: center;
            backdrop-filter: blur(20px);
        }
        .kiosk-avatar-row { display: flex; align-items: center; justify-content: center; gap: clamp(.75rem, 2.5vw, 1.75rem); margin-bottom: 1.75rem; }
        .kiosk-spark { color: var(--theme-primary); opacity: .55; width: clamp(1rem, 2vw, 1.5rem); height: clamp(1rem, 2vw, 1.5rem); }
        .kiosk-spark--left { transform: rotate(-15deg); }
        .kiosk-spark--right { transform: rotate(15deg) scaleX(-1); }
        .kiosk-photo {
            width: clamp(6rem, 15vw, 9.5rem); height: clamp(6rem, 15vw, 9.5rem);
            border-radius: 1.75rem; overflow: hidden;
            background: var(--theme-hero-gradient);
            box-shadow: 0 0 0 6px rgba(255,255,255,.08);
            display: flex; align-items: center; justify-content: center;
            font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 900; color: var(--theme-text-on-primary);
            flex-shrink: 0;
        }
        .kiosk-photo img { width: 100%; height: 100%; object-fit: cover; }
        .kiosk-name-first { font-size: clamp(.85rem, 1.6vw, 1.15rem); font-weight: 800; opacity: .55; text-transform: uppercase; letter-spacing: .25em; margin-bottom: .35rem; }
        .kiosk-name { font-size: clamp(1.8rem, 5vw, 3.5rem); font-weight: 900; letter-spacing: -.02em; margin: 0 0 1.75rem; word-break: break-word; }
        .kiosk-pill-label {
            display: flex; align-items: center; gap: 1rem; justify-content: center;
            font-size: clamp(.75rem, 1.2vw, .9rem); font-weight: 800; text-transform: uppercase; letter-spacing: .2em; opacity: .6;
            margin-bottom: 1rem;
        }
        .kiosk-pill-label .line { flex: 1; max-width: 3.5rem; height: 1px; background: linear-gradient(90deg, transparent, currentColor, transparent); opacity: .4; }
        .kiosk-figure { font-size: clamp(3rem, 11vw, 7rem); font-weight: 900; line-height: 1; letter-spacing: -.03em; color: var(--theme-primary); }
        .kiosk-figure--idle { font-size: clamp(1.1rem, 2.4vw, 1.5rem); text-transform: uppercase; letter-spacing: .06em; opacity: .5; color: var(--theme-body-text); }
        .kiosk-chip {
            display: inline-flex; align-items: center; gap: .5rem; margin-top: 1.5rem;
            padding: .65rem 1.4rem; border-radius: 999px; font-size: .85rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: .08em; background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
        }
        .kiosk-chip svg { width: 1.1em; height: 1.1em; opacity: .7; }
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
            <span class="kiosk-status" :style="isOpenNow ? 'color:#34d399' : 'color:#fb7185'">
                <span class="kiosk-dot" :style="isOpenNow ? 'background:#34d399' : 'background:#fb7185'"></span>
                <span x-text="statusLabel"></span>
            </span>
            <span class="kiosk-clock">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-text="clock"></span>
            </span>
        </div>

        <div class="kiosk-stage">
            <div class="kiosk-card">
                <div class="kiosk-avatar-row">
                    <svg class="kiosk-spark kiosk-spark--left" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2.5" d="M12 2v5M4 12h5M2 20l7-7"/></svg>
                    <div class="kiosk-photo">
                        <img :src="panel.photo_url" x-show="panel.photo_url" alt="">
                        <span x-show="!panel.photo_url" x-text="panel.name.charAt(0)"></span>
                    </div>
                    <svg class="kiosk-spark kiosk-spark--right" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2.5" d="M12 2v5M4 12h5M2 20l7-7"/></svg>
                </div>
                <div class="kiosk-name-first" x-show="firstName" x-text="firstName"></div>
                <h1 class="kiosk-name" x-text="lastName"></h1>

                <template x-if="panel.is_paused">
                    <div class="kiosk-paused">
                        <svg style="width:1.2em;height:1.2em" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        On Break
                    </div>
                </template>

                <template x-if="!panel.is_paused && (panel.mode === 'token' || panel.mode === 'hybrid')">
                    <div class="kiosk-block">
                        <div class="kiosk-pill-label"><span class="line"></span><span x-text="panel.serving_label"></span><span class="line"></span></div>
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
                        <div class="kiosk-chip">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span x-text="panel.waiting_count"></span>&nbsp;waiting
                        </div>
                    </div>
                </template>

                <template x-if="!panel.is_paused && (panel.mode === 'time_slot' || panel.mode === 'hybrid')">
                    <div :class="panel.mode === 'hybrid' ? 'kiosk-block' : ''">
                        <div class="kiosk-pill-label"><span class="line"></span><span>Next Appointment</span><span class="line"></span></div>
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
                // Mirrors the mockup's two-tier name: everything but the last
                // word shown small above it, e.g. "Deepanshu" / "Jain".
                get firstName() {
                    const parts = this.panel.name.trim().split(/\s+/);
                    return parts.length > 1 ? parts.slice(0, -1).join(' ') : '';
                },
                get lastName() {
                    const parts = this.panel.name.trim().split(/\s+/);
                    return parts[parts.length - 1] || this.panel.name;
                },
                // This screen belongs to one specialist, so "open" tracks
                // *their* working hours (panel.is_open) and their own break
                // (panel.is_paused) — not the shop's operating hours. A
                // shop-wide bookings pause still overrides everyone, this
                // employee included.
                get isOpenNow() {
                    return this.panel.is_open && !this.panel.is_paused && !this.vendor.bookings_paused;
                },
                get statusLabel() {
                    if (this.panel.is_paused) return 'On Break';
                    if (this.vendor.bookings_paused) return 'Bookings Paused';
                    return this.panel.is_open ? 'Open Now' : 'Closed';
                },
                init() {
                    const tickClock = () => { this.clock = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }); };
                    tickClock();
                    setInterval(tickClock, 1000);

                    window.whenRealtimeReady((Echo) => {
                        Echo.channel(`queue.${this.panel.id}`).listen('.queue.updated', () => this.refresh());
                        Echo.channel(`shop.${this.vendor.id}`).listen('.shop.status', () => this.refresh());
                    });

                    // The socket above pushes instantly on an actual action (a token
                    // called, the shop toggled) — but nothing "happens" the moment an
                    // employee's own shift boundary passes, it's just the clock
                    // ticking past working_end_time. Nobody is going to refresh a
                    // kiosk screen by hand, so a flat poll is what actually catches
                    // that transition, on top of (not instead of) the live channels.
                    setInterval(() => this.refresh(), 30000);
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
