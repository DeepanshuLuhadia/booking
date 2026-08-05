<x-app-layout page-title="Book Verified Experts | Professional Appointments">

    {{-- ================================================================
    GLOBAL PAGE STYLES — inlined here so they take priority over
    any compiled Tailwind / app.css rules that were fighting us.
    ================================================================ --}}
    @include('customer.partials.listing-styles')

    <div class="bv-page">

        {{-- ═══════════════════════════════════════════════════════
        HERO + SEARCH + CATEGORIES
        ═══════════════════════════════════════════════════════ --}}
        <section class="bv-hero">
            {{-- Backdrop layers, decorative only (no hit area, hidden from AT).
                 All of these are display:none below 601px so the phone hero is
                 byte-for-byte the layout it was before. --}}
            {{-- Source order matches paint order: glows, then the header
                 artwork, then the mesh grid ruled across the top of it. --}}
            <div class="bv-hero-glow-1" aria-hidden="true"></div>
            <div class="bv-hero-glow-2" aria-hidden="true"></div>
            <div class="bv-hero-crowd" aria-hidden="true"></div>
            <div class="bv-hero-grid" aria-hidden="true"></div>

            <div style="position:relative; z-index:10; text-align:center;">

                {{-- Badge --}}
                {{-- <div
                    style="display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:999px; padding:8px 20px; font-size:10px; font-weight:800; color:rgba(255,255,255,.7); text-transform:uppercase; letter-spacing:.25em; margin-bottom:32px;">
                    <span
                        style="width:8px; height:8px; border-radius:50%; background:#ff6d00; display:inline-block; box-shadow:0 0 10px rgba(255,109,0,.6);"></span>
                    TRUSTED BOOKING PLATFORM
                </div> --}}

                {{-- H1 --}}
                <h1
                    style="font-size:clamp(2rem,7vw,5rem); font-weight:900; color:#fff; line-height:1.05; letter-spacing:-.03em; margin:0 0 18px;">
                    Find Trusted <span
                        style="color:transparent; background:linear-gradient(135deg,#ff8c42,#ffab40); -webkit-background-clip:text; background-clip:text; font-style:italic; padding-right:0.12em; display:inline-block;">Professionals</span><br>
                    Near You
                </h1>

                {{-- Subheading --}}
                <p
                    style="color:rgba(255,255,255,.45); font-size:1.1rem; max-width:520px; margin:0 auto 48px; line-height:1.7;">
                    Book trusted local services quickly and easily.
                </p>

                {{-- ── Search Bar ── --}}
                @include('customer.partials.search-categories', ['allThemes' => $allThemes])

                {{-- ── Stats ── --}}
                @php
                    // All five come from the controller (cached aggregates) —
                    // nothing on this page is a placeholder figure any more.
                    $totalClients      = $stats['clients'];
                    $totalCities       = $stats['cities'];
                    $totalAppointments = $stats['appointments'];
                    $avgRating         = $stats['rating'];
                    $hasRatings        = $stats['reviews'] > 0;
                @endphp
                @if($totalClients > 0 || $totalCities > 0 || $totalAppointments > 0)
                <div class="bv-stats bv-stats-desktop">
                    <div>
                        <div class="bv-stat-num"><span data-counter data-target="{{ $totalClients }}" data-suffix="+">0</span></div>
                        <div class="bv-stat-label">Happy Clients</div>
                    </div>
                    <div>
                        <div class="bv-stat-num"><span data-counter data-target="{{ $totalCities }}" data-suffix="+">0</span></div>
                        <div class="bv-stat-label">Cities Reach</div>
                    </div>
                    <div>
                        <div class="bv-stat-num"><span data-counter data-target="{{ $totalAppointments }}" data-suffix="+" data-decimals="0">0</span></div>
                        <div class="bv-stat-label">Appointments</div>
                    </div>
                    {{-- Only shown once there is at least one real review to
                         average — better a three-tile row than a made-up score. --}}
                    @if($hasRatings)
                    <div>
                        <div class="bv-stat-num">
                            <span data-counter data-target="{{ number_format($avgRating, 1) }}" data-decimals="1">0</span>
                            <span style="color:#ffab40; font-size:1.6rem;">★</span>
                        </div>
                        <div class="bv-stat-label">User Rating</div>
                    </div>
                    @endif
                </div>
                @endif

            </div>

            {{-- Curved bottom edge — the section below adopts the same tone so
                 this reads as one continuous arc, not a seam. --}}
            <div class="bv-hero-curve" aria-hidden="true"></div>
        </section>

        {{-- ═══════════════════════════════════════════════════════
        RECOMMENDED PROFESSIONALS
        ═══════════════════════════════════════════════════════ --}}
        <section class="bv-section">
            <div style="max-width:1100px; margin:0 auto;">
                <div>
                    <h2 class="bv-section-title">
                        Recommended <span class="bv-section-accent">Professionals</span>
                    </h2>
                    <div class="bv-section-bar"></div>
                    {{-- Says out loud how the eight cards were picked, so the
                         ordering never looks arbitrary. --}}
                    <p class="bv-section-sub">
                        @if($rankedByDistance)
                            {{-- Name the place when we have one: "closest to
                                 Koramangala" is a claim the visitor can check,
                                 "closest to your current location" is not. --}}
                            Closest specialists to {{ $locationLabel ?? 'your current location' }}
                        @else
                            Top-rated specialists for your premium experience
                        @endif
                    </p>
                </div>

                <div class="bv-grid">
                    @forelse($vendors as $vendor)
                    @include('customer.partials.vendor-card', [
                        'vendor'    => $vendor,
                        'allThemes' => $allThemes,
                        'eager'     => $loop->iteration <= 6,
                    ])

                    @empty
                    <div style="grid-column:1/-1; padding:80px 0; text-align:center;">
                        <div style="font-size:5rem; opacity:.2;">📭</div>
                        <h3 style="color:#fff; font-size:2rem; margin:24px 0 12px;">No Experts Found</h3>
                        <p style="color:rgba(255,255,255,.4);">Try adjusting your search or filters.</p>
                        <a href="{{ route('home') }}"
                            style="display:inline-block; margin-top:28px; background:linear-gradient(135deg,#ff6d00,#ffab40); color:#fff; font-weight:800; padding:14px 32px; border-radius:12px; text-decoration:none;">Reset
                            Search</a>
                    </div>
                    @endforelse
                </div>

                {{-- The default listing is a fixed shortlist, so it gets a
                     "view all" escape hatch instead of a pager; search results
                     stay paginated as before. --}}
                @if($isShortlist && $totalVendors > $vendors->count())
                <div style="margin-top:52px; text-align:center;">
                    <a href="{{ route('home', ['view' => 'all']) }}"
                        style="display:inline-flex; align-items:center; gap:10px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.12); color:#fff; font-weight:800; font-size:12px; text-transform:uppercase; letter-spacing:.2em; padding:16px 34px; border-radius:14px; text-decoration:none;">
                        View All {{ $totalVendors }} Professionals
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
                @elseif($vendors instanceof \Illuminate\Contracts\Pagination\Paginator && $vendors->hasPages())
                <div style="margin-top:60px;">{{ $vendors->links() }}</div>
                @endif
            </div>
        </section>

        {{-- ═══════════════════════════════════════════════════════
        BOOK IN 3 EASY STEPS
        ═══════════════════════════════════════════════════════ --}}
        <section class="bv-steps-section">
            <div class="bv-steps-head" style="text-align:center; margin-bottom:60px;">
                <h2 style="font-size:2.4rem; font-weight:900; color:#fff; letter-spacing:-.02em; margin:0 0 10px;">
                    Book in <span style="color:#ff8c42; font-style:italic;">3 Easy Steps</span>
                </h2>
                <p
                    style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.35em; color:rgba(255,255,255,.25);">
                    Your professional journey starts here</p>
            </div>

            <div class="bv-steps-grid">

                {{-- Step 1: Find & Filter — map+magnifier 3D illustration --}}
                <div class="bv-step-card">
                    <div class="bv-step-icon-wrap">
                        <div class="bv-step-num">1</div>
                        {{-- 3D Map + Magnifier Image --}}
                        <img src="{{ asset('images/steps/step1.png') }}" alt="Find & Filter" loading="lazy">
                    </div>
                    <h3 class="bv-step-title">Find &amp; Filter</h3>
                    <p class="bv-step-desc">Search for top-tier professionals in your area that fulfill your specific
                        needs.</p>
                </div>

                {{-- Step 2: Choose Easy — calendar + checkmark 3D illustration --}}
                <div class="bv-step-card">
                    <div class="bv-step-icon-wrap">
                        <div class="bv-step-num">2</div>
                        {{-- 3D Calendar Image --}}
                        <img src="{{ asset('images/steps/step2.png') }}" alt="Choose Easy" loading="lazy">
                    </div>
                    <h3 class="bv-step-title">Choose Easy</h3>
                    <p class="bv-step-desc">See detailed ratings and reviews, then book the best expert instantly.</p>
                </div>

                {{-- Step 3: Confirm & Go — ticket/pass 3D illustration --}}
                <div class="bv-step-card">
                    <div class="bv-step-icon-wrap">
                        <div class="bv-step-num">3</div>
                        {{-- 3D Ticket Image --}}
                        <img src="{{ asset('images/steps/step3.png') }}" alt="Confirm & Go" loading="lazy">
                    </div>
                    <h3 class="bv-step-title">Confirm &amp; Go</h3>
                    <p class="bv-step-desc">Get instant confirmation and reminders for your professional appointment.
                    </p>
                </div>
            </div>

            {{-- Ambient glow --}}
            <div
                style="position:absolute; top:20%; right:-10%; width:500px; height:500px; background:rgba(255,109,0,.05); border-radius:50%; filter:blur(100px); pointer-events:none;">
            </div>

            {{-- Stats — repositioned below the steps on mobile only --}}
            @if($totalClients > 0 || $totalCities > 0 || $totalAppointments > 0)
            <div class="bv-stats bv-stats-mobile">
                <div>
                    <div class="bv-stat-num"><span data-counter data-target="{{ $totalClients }}" data-suffix="+">0</span></div>
                    <div class="bv-stat-label">Happy Clients</div>
                </div>
                <div>
                    <div class="bv-stat-num"><span data-counter data-target="{{ $totalCities }}" data-suffix="+">0</span></div>
                    <div class="bv-stat-label">Cities Reach</div>
                </div>
                <div>
                    <div class="bv-stat-num"><span data-counter data-target="{{ $totalAppointments }}" data-suffix="+" data-decimals="0">0</span></div>
                    <div class="bv-stat-label">Appointments</div>
                </div>
                @if($hasRatings)
                <div>
                    <div class="bv-stat-num">
                        <span data-counter data-target="{{ number_format($avgRating, 1) }}" data-decimals="1">0</span>
                        <span style="color:#ffab40; font-size:1.6rem;">★</span>
                    </div>
                    <div class="bv-stat-label">User Rating</div>
                </div>
                @endif
            </div>
            @endif
        </section>

        {{-- ═══════════════════════════════════════════════════════
        GROW YOUR BUSINESS CTA
        ═══════════════════════════════════════════════════════ --}}
        <section class="bv-cta-section">
            <div class="bv-cta-glow"></div>
            <div style="position:relative; z-index:2;">
                <div class="bv-cta-badge">
                    <span
                        style="width:6px;height:6px;border-radius:50%;background:#ff6d00;display:inline-block;"></span>
                    Join With {{ config('brand.platform') }}
                </div>
                <h2 class="bv-cta-title">
                    GROW YOUR <br><span class="bv-cta-accent">BUSINESS</span> WITH US
                </h2>
                <p class="bv-cta-desc">
                    Are you a professional? Join us to get more bookings and grow your client base with our advanced
                    tools.
                </p>
                <a href="/register/vendor" class="bv-cta-btn">
                    Join as a Professional
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </a>
            </div>
            {{-- Decorative star --}}
            <div style="position:absolute; bottom:40px; right:60px; width:60px; height:60px; opacity:.15;">
                <svg viewBox="0 0 100 100" fill="white">
                    <polygon points="50,5 61,35 95,35 68,57 79,91 50,70 21,91 32,57 5,35 39,35" />
                </svg>
            </div>
        </section>

    </div>{{-- .bv-page --}}

    {{-- ── Counter animation script ─────────────────────────────── --}}
    @include('customer.partials.listing-scripts')

</x-app-layout>