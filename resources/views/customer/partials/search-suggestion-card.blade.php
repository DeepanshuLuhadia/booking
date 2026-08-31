{{-- One search-suggestion row: the listing card, shrunk to dropdown size.

     Deliberately its own markup rather than a re-used vendor-card: the listing
     card renders a desktop and a mobile variant and is sized for the grid, so
     dropping it into a 400px panel would have meant overriding half its CSS.
     The content and the visual language (image left, category, name, address,
     rating, price) are the same, only smaller.

     Expects $vendor and $allThemes. --}}
@php
    $sgType = $vendor->category?->slug ?? 'consultant';

    $sgTheme = array_merge([
        'primary'      => '#2979ff',
        'primary_dark' => '#00b0ff',
        'label'        => ucfirst($sgType),
    ], $allThemes[$sgType] ?? ($allThemes['consultant'] ?? []));

    $sgOpen = (bool) ($vendor->is_bookable_now ?? $vendor->isEffectivelyOpen()) && (bool) $vendor->is_open;

    $sgRgb = match($sgType) {
        'health','doctor'   => '0,200,83',
        'beauty','barber'   => '255,109,0',
        'sports','activity' => '255,214,0',
        'consultant'        => '41,121,255',
        'training'          => '124,58,237',
        default             => '26,35,126'
    };

    if ($vendor->shop_photo) {
        $sgImg = asset('storage/' . $vendor->shop_photo);
    } elseif (in_array($sgType, ['health','doctor'])) {
        $sgImg = asset('images/placeholders/health.svg');
    } elseif (in_array($sgType, ['beauty','barber'])) {
        $sgImg = asset('images/placeholders/beauty.svg');
    } elseif (in_array($sgType, ['sports','activity'])) {
        $sgImg = asset('images/placeholders/sports.svg');
    } elseif ($sgType === 'training') {
        $sgImg = asset('images/placeholders/training.svg');
    } else {
        $sgImg = asset('images/placeholders/default.svg');
    }

    $sgAddress = trim((string) $vendor->address);
    $sgActive  = $vendor->isSubscriptionActive();
@endphp

<a href="{{ route('vendor.show', $vendor->slug) }}"
   class="bv-sg-card {{ $sgOpen ? '' : 'bv-sg-closed' }}"
   style="--c1:{{ $sgTheme['primary'] }};--c2:{{ $sgTheme['primary_dark'] }};--sgc:{{ $sgRgb }};"
   role="option">

    <span class="bv-sg-img">
        <img src="{{ $sgImg }}" alt="{{ $vendor->business_name }}" loading="lazy">
    </span>

    <span class="bv-sg-body">
        <span class="bv-sg-top">
            <span class="bv-sg-cat">{{ $sgTheme['label'] ?? ucfirst($sgType) }}</span>
            @if($sgActive)
                @if($sgOpen)
                    <span class="bv-sg-open"><span class="bv-sg-dot"></span>Open</span>
                @else
                    <span class="bv-sg-shut">Closed</span>
                @endif
            @endif
        </span>

        <span class="bv-sg-name">
            {{ $vendor->business_name }}
            @if($vendor->is_verified)
            <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13" style="color:#38bdf8; flex-shrink:0;">
                <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
            @endif
        </span>

        @if($sgAddress !== '' || ($sgActive && $vendor->distance_km !== null))
        <span class="bv-sg-meta">
            @if($sgAddress !== '')
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0; opacity:.55;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="bv-sg-addr">{{ $sgAddress }}</span>
            @endif
            @if($sgActive && $vendor->distance_km !== null)
            <span class="bv-sg-dist">{{ $vendor->distance_km < 1
                ? round($vendor->distance_km * 1000) . ' m'
                : '~' . number_format($vendor->distance_km, 1) . ' km' }}</span>
            @endif
        </span>
        @endif
    </span>

    <span class="bv-sg-side">
        @if($sgActive && ($vendor->reviews_count ?? 0) > 0)
        <span class="bv-sg-rating">
            <span style="color:#ffab40;">★</span>{{ number_format((float) $vendor->avg_rating, 1) }}
        </span>
        @endif
        <span class="bv-sg-price">₹{{ number_format((float) $vendor->starting_fee) }}</span>
    </span>
</a>
