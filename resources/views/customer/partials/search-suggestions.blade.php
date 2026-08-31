{{-- Contents of the live-search dropdown: the matching businesses as compact
     cards, then professionals matched by name (linking straight to their
     booking page) and matching areas (which re-run the search scoped to that
     address), plus a line handing the rest over to the full results page.

     Rendered server-side (like the infinite-scroll batches) so the card markup
     lives in one place instead of being rebuilt in JavaScript.

     Expects $vendors, $employees, $locations, $allThemes, $total and $keyword. --}}
@php
    $sgHasAny = $vendors->isNotEmpty() || $employees->isNotEmpty() || $locations->isNotEmpty();
@endphp

@if(! $sgHasAny)
    <div class="bv-sg-empty">
        <div class="bv-sg-empty-icon">🔍</div>
        <div class="bv-sg-empty-title">No matches for &ldquo;{{ $keyword }}&rdquo;</div>
        <div class="bv-sg-empty-sub">Try a business name, a professional, a service or an area.</div>
    </div>
@else
    @if($vendors->isNotEmpty())
        <div class="bv-sg-head">
            {{ $total }} {{ Str::plural('match', $total) }} for &ldquo;{{ $keyword }}&rdquo;
        </div>

        @foreach($vendors as $vendor)
            @include('customer.partials.search-suggestion-card', [
                'vendor'    => $vendor,
                'allThemes' => $allThemes,
            ])
        @endforeach
    @endif

    @if($employees->isNotEmpty())
        <div class="bv-sg-head bv-sg-sec">Professionals</div>

        @foreach($employees as $sgEmp)
            <a href="{{ $sgEmp->public_url }}"
               class="bv-sg-card"
               style="--sgc:41,121,255;"
               role="option">

                <span class="bv-sg-img bv-sg-emp-av">
                    @if($sgEmp->photo)
                        <img src="{{ asset('storage/' . $sgEmp->photo) }}" alt="{{ $sgEmp->name }}" loading="lazy">
                    @else
                        <span class="bv-sg-emp-initial">{{ mb_strtoupper(mb_substr($sgEmp->name, 0, 1)) }}</span>
                    @endif
                </span>

                <span class="bv-sg-body">
                    <span class="bv-sg-top">
                        <span class="bv-sg-cat">Professional</span>
                    </span>

                    <span class="bv-sg-name">{{ $sgEmp->name }}</span>

                    @if($sgEmp->vendor)
                    <span class="bv-sg-meta">
                        <span class="bv-sg-addr">{{ $sgEmp->vendor->business_name }}</span>
                    </span>
                    @endif
                </span>

                <span class="bv-sg-side">
                    <span class="bv-sg-price">₹{{ number_format((float) $sgEmp->service_fee_override) }}</span>
                </span>
            </a>
        @endforeach
    @endif

    @if($locations->isNotEmpty())
        <div class="bv-sg-head bv-sg-sec">Areas</div>

        {{-- Buttons, not links: picking an area replays the page's own search
             form with the address as the keyword (see listing-scripts), so a
             category page's area search stays inside its category. --}}
        @foreach($locations as $sgLoc)
            <button type="button"
                    class="bv-sg-card bv-sg-locrow"
                    style="--sgc:0,200,83;"
                    data-suggest-fill="{{ $sgLoc->address }}"
                    role="option">

                <span class="bv-sg-img bv-sg-loc-ic">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </span>

                <span class="bv-sg-body">
                    <span class="bv-sg-name">{{ $sgLoc->address }}</span>
                    <span class="bv-sg-meta">
                        <span class="bv-sg-addr">{{ $sgLoc->vendor_count }} {{ Str::plural('business', $sgLoc->vendor_count) }} here</span>
                    </span>
                </span>

                <span class="bv-sg-side">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="opacity:.5;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            </button>
        @endforeach
    @endif

    @if($total > $vendors->count())
        {{-- Submits the search form the panel belongs to, so "see the rest"
             lands on the same page's own results rather than guessing a URL. --}}
        <button type="button" class="bv-sg-more" data-suggest-submit>
            View all {{ $total }} results
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    @endif
@endif
