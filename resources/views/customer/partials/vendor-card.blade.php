{{-- One vendor card.
     Renders TWO versions of the card:
       • .bv-desktop-card-wrap — original full-image overlay card (hidden on mobile via CSS)
       • .bv-hcard             — new horizontal card (hidden on desktop, shown on mobile via CSS)
     Expects $vendor, $allThemes and an optional $eager flag. --}}
@php
    $vType = $vendor->category?->slug ?? 'consultant';

    $vTheme = array_merge([
        'primary'      => '#2979ff',
        'primary_dark' => '#00b0ff',
        'label'        => ucfirst($vType),
        'emoji'        => '✨',
    ], $allThemes[$vType] ?? ($allThemes['consultant'] ?? []));

    $isOpen = (bool) ($vendor->is_bookable_now ?? $vendor->isEffectivelyOpen()) && (bool) $vendor->is_open;

    $c1 = $vTheme['primary'];
    $c2 = $vTheme['primary_dark'];
    $rgbStr = match($vType) {
        'health','doctor'   => '0,200,83',
        'beauty','barber'   => '255,109,0',
        'sports','activity' => '255,214,0',
        'consultant'        => '41,121,255',
        'training'          => '124,58,237',
        default             => '26,35,126'
    };
    [$cr,$cg,$cb] = explode(',', $rgbStr);

    if ($vendor->shop_photo) {
        $img = asset('storage/' . $vendor->shop_photo);
    } elseif (in_array($vType, ['health','doctor'])) {
        $img = asset('images/placeholders/health.svg');
    } elseif (in_array($vType, ['beauty','barber'])) {
        $img = asset('images/placeholders/beauty.svg');
    } elseif (in_array($vType, ['sports','activity'])) {
        $img = asset('images/placeholders/sports.svg');
    } elseif ($vType === 'training') {
        $img = asset('images/placeholders/training.svg');
    } else {
        $img = asset('images/placeholders/default.svg');
    }

    $catCode = 'general';
    if (in_array($vType, ['health','doctor']))      $catCode = 'doctor';
    elseif (in_array($vType, ['beauty','barber']))  $catCode = 'barber';
    elseif (in_array($vType, ['sports','activity']))$catCode = 'sports';
    elseif ($vType === 'consultant')                $catCode = 'consultant';
    elseif ($vType === 'training')                  $catCode = 'training';

    $routeUrl   = route('vendor.show', $vendor->slug);
    $priceStr   = '₹' . number_format($vendor->starting_fee);
    $name       = $vendor->business_name;
    $address    = trim((string) $vendor->address);
    $catLabel   = $vTheme['label'] ?? ucfirst($vType);

    $priceLabel = match($catCode) {
        'doctor'     => 'Consultation',
        'barber'     => 'Starts From',
        'consultant' => 'Session',
        'training'   => 'Session',
        'sports'     => 'Entry / Pass',
        default      => 'Starts From'
    };

    /* Offer badge — shown when vendor has a discount_percent or offer_text */
    $offerText = null;
    if (!empty($vendor->discount_percent) && $vendor->discount_percent > 0) {
        $offerText = $vendor->discount_percent . '% OFF';
    } elseif (!empty($vendor->offer_text)) {
        $offerText = $vendor->offer_text;
    }
@endphp

{{-- ═══════════════════════════════════════════════════════════
     DESKTOP CARD (hidden on mobile via CSS)
     Original sports/overlay card — unchanged from before.
     ═══════════════════════════════════════════════════════════ --}}
<div class="bv-desktop-card-wrap">
    <a href="{{ $routeUrl }}" class="bv-dynamic-card bv-card-sports {{ $isOpen ? '' : 'bv-closed pointer-events-none' }}"
        style="--c1:{{ $c1 }};--c2:{{ $c2 }};--cr:{{ $cr }};--cg:{{ $cg }};--cb:{{ $cb }};"
        @click="handleVendorClick($event, '{{ $routeUrl }}')">
        <img src="{{ $img }}" alt="{{ $name }}" loading="{{ ($eager ?? false) ? 'eager' : 'lazy' }}">
        @if($vendor->isSubscriptionActive() && ($vendor->reviews_count ?? 0) > 0)
        <div class="bv-rc-rating" title="{{ $vendor->reviews_count }} {{ Str::plural('review', $vendor->reviews_count) }}" style="position:absolute; top:12px; right:12px; background:rgba(0,0,0,0.75); backdrop-filter:blur(8px); padding:4px 10px; border-radius:999px; color:#fff; font-size:12px; font-weight:800; display:flex; gap:5px; border:1px solid rgba(255,255,255,0.15); z-index: 2;">
            <span style="color:#ffab40;">★</span> {{ number_format((float) $vendor->avg_rating, 1) }}
            <span style="color:rgba(255,255,255,0.55); font-weight:700;">({{ $vendor->reviews_count }})</span>
        </div>
        @endif
        <div class="bv-card-sports-overlay">
            <h3 class="bv-rc-name" style="color:#fff; font-size:24px; font-weight:900; margin:0 0 8px; line-height:1.1; display:flex; align-items:center; gap:8px;">
                {{ $name }}
                @if($vendor->is_verified)
                <svg style="color:#38bdf8; flex-shrink:0; width:20px; height:20px;" viewBox="0 0 24 24" fill="currentColor" title="Verified">
                    <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"></path>
                </svg>
                @endif
            </h3>
            <div class="bv-rc-loc"
                style="display:flex; align-items:center; gap:6px; font-size:13px; color:rgba(255,255,255,0.8); margin-bottom:20px;">
                @if($address !== '')
                <svg class="bv-rc-loc-pin" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="bv-rc-addr" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">{{ $address }}</span>
                @endif
                @if($vendor->isSubscriptionActive() && $vendor->distance_km !== null)
                <span class="bv-rc-dist" style="margin-left:auto; font-weight:700; color:rgba(var(--cr),var(--cg),var(--cb),0.9); font-size:11px; text-transform:uppercase; letter-spacing:0.05em;">{{ $vendor->distance_km < 1 ? round($vendor->distance_km * 1000) . ' m' : '~' . number_format($vendor->distance_km, 1) . ' km' }}</span>
                @endif
            </div>
            <div class="bv-rc-pricebar"
                style="display:flex; align-items:center; justify-content:space-between; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); padding:14px; border-radius:14px;">
                <div>
                    <div class="bv-rc-price-label"
                        style="font-size:11px; font-weight:800; text-transform:uppercase; color:rgba(255,255,255,0.6); letter-spacing:.05em;">
                        {{ $priceLabel }}</div>
                    <div class="bv-rc-price" style="font-size:20px; font-weight:900; color:#fff;">{{ $priceStr }} {{ $vendor->starting_fee > 0 ? 'onwards' : '' }}</div>
                </div>
                @if($vendor->isSubscriptionActive())
                <div class="bv-rc-status" style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                    @if(!$isOpen)
                        <div style="font-size: 9px; font-weight: 900; color: #ffab40; text-transform: uppercase;">Closed</div>
                        <div style="font-size: 11px; font-weight: 700; color: #fff;">Opens At: {{ \Carbon\Carbon::parse($vendor->global_opening_time)->format('h:i A') }}</div>
                    @else
                        <div style="font-size: 9px; font-weight: 900; color: #4ade80; text-transform: uppercase; display: flex; align-items: center; gap: 4px;">
                            <span style="width:6px; height:6px; border-radius:50%; background:#4ade80; display:inline-block; box-shadow:0 0 8px #4ade80;"></span>
                            @if(($vendor->live_queue_count ?? 0) > 0)
                                {{ $vendor->live_queue_count }} In Queue
                            @else
                                Live Queue
                            @endif
                        </div>
                        <div
                            style="width:36px; height:36px; background:var(--c1); border-radius:50%; display:flex; align-items:center; justify-content:center; color:#000; box-shadow:0 6px 16px rgba(var(--cr),var(--cg),var(--cb),0.4);">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="3"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    @endif
                </div>
                @else
                <div class="bv-rc-status" style="display: flex; align-items: center; justify-content: center;">
                    <div style="width:36px; height:36px; background:rgba(255,255,255,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </a>
</div>

{{-- ═══════════════════════════════════════════════════════════
     MOBILE CARD — horizontal left/right layout (shown on mobile only)
     ═══════════════════════════════════════════════════════════ --}}
<a href="{{ $routeUrl }}"
   class="bv-hcard {{ $isOpen ? '' : 'bv-closed' }}"
   style="--c1:{{ $c1 }};--c2:{{ $c2 }};--cr:{{ $cr }};--cg:{{ $cg }};--cb:{{ $cb }};"
   @click="handleVendorClick($event, '{{ $routeUrl }}')">

    {{-- LEFT: Image --}}
    <div class="bv-hcard-img">
        <img src="{{ $img }}"
             alt="{{ $name }}"
             loading="{{ ($eager ?? false) ? 'eager' : 'lazy' }}">
        @if($offerText)
        <div class="bv-hcard-offer">🏷 {{ $offerText }}</div>
        @endif
    </div>

    {{-- RIGHT: Details --}}
    <div class="bv-hcard-body">

        {{-- Category + status --}}
        <div class="bv-hcard-top">
            <span class="bv-hcard-cat">{{ $catLabel }}</span>
            @if($vendor->isSubscriptionActive())
                @if($isOpen)
                    <span class="bv-hcard-status-open">
                        <span class="bv-hcard-status-dot"></span>
                        @if(($vendor->live_queue_count ?? 0) > 0)
                            {{ $vendor->live_queue_count }} In Queue
                        @else
                            Open Now
                        @endif
                    </span>
                @else
                    <span class="bv-hcard-status-closed">
                        Closed
                    </span>
                @endif
            @endif
        </div>

        {{-- Name + verified --}}
        <h3 class="bv-hcard-name">
            {{ $name }}
            @if($vendor->is_verified)
            <svg style="color:#38bdf8; width:16px; height:16px; flex-shrink:0;" viewBox="0 0 24 24" fill="currentColor" title="Verified">
                <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
            @endif
        </h3>

        {{-- Address + distance --}}
        @if($address !== '' || ($vendor->isSubscriptionActive() && $vendor->distance_km !== null))
        <div class="bv-hcard-meta">
            @if($address !== '')
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0; color:rgba(255,255,255,.4);">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="bv-hcard-addr">{{ $address }}</span>
            @endif
            @if($vendor->isSubscriptionActive() && $vendor->distance_km !== null)
            <span class="bv-hcard-dist">
                {{ $vendor->distance_km < 1
                    ? round($vendor->distance_km * 1000) . ' m'
                    : '~' . number_format($vendor->distance_km, 1) . ' km' }}
            </span>
            @endif
        </div>
        @endif

        {{-- Rating --}}
        @if($vendor->isSubscriptionActive() && ($vendor->reviews_count ?? 0) > 0)
        <div class="bv-hcard-rating">
            <span style="color:#ffab40;">★</span>
            {{ number_format((float) $vendor->avg_rating, 1) }}
            <span style="color:rgba(255,255,255,.5); font-weight:700;">({{ $vendor->reviews_count }})</span>
        </div>
        @endif

        <div class="bv-hcard-divider"></div>

        {{-- Price + CTA --}}
        <div class="bv-hcard-footer">
            <div>
                <div class="bv-hcard-price-label">{{ $priceLabel }}</div>
                <div class="bv-hcard-price">
                    {{ $priceStr }}
                    @if($vendor->starting_fee > 0)
                    <span class="bv-hcard-price-onwards">onwards</span>
                    @endif
                </div>
            </div>
            <div class="bv-hcard-cta">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </div>

    </div>{{-- .bv-hcard-body --}}
</a>
