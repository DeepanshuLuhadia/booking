{{-- Search bar + category selector, shared by the discovery listing and the
     category detail page. $activeCategory is the currently selected category
     slug ('' for none); the pills link straight to the category detail page.
     $searchAction is the route the form submits to — each page passes its own,
     so a search always resolves on the page the customer is standing on. --}}
@php
    $activeCategory = $activeCategory ?? request('type', '');
    $searchAction   = $searchAction ?? route('home');
    $resetUrl       = $resetUrl ?? route('home');
    $canReset       = $canReset ?? (request('search') || $activeCategory || request('location'));

    // The catalogue page — "All" is a destination in the category strip like
    // every other pill, not a link back to the landing page.
    $allSlug     = \App\Http\Controllers\CustomerDiscoveryController::ALL_CATEGORIES_SLUG;
    $isAllActive = $activeCategory === '' || $activeCategory === $allSlug;
@endphp
                {{-- data-suggest-* drive the search-as-you-type dropdown (see
                     listing-scripts). The category is the page's own scope:
                     the landing page sends none and searches everything, a
                     category page sends its slug and stays inside it. --}}
                <div class="bv-search-wrap"
                     data-suggest-url="{{ route('discover.suggestions') }}"
                     data-suggest-min="{{ \App\Http\Controllers\CustomerDiscoveryController::SUGGEST_MIN_CHARS }}"
                     data-suggest-category="{{ $activeCategory }}">
                    <div class="bv-search-bar">
                        <form action="{{ $searchAction }}" method="GET" class="bv-search-form">
                            {{-- Category is carried by the "All Categories" dropdown below
                                 (name="type"), which is the single source of truth shared
                                 with the category pills so both selectors stay in sync. --}}

                            {{-- Expert Name --}}
                            <div class="bv-search-field bv-search-field-text">
                                <svg class="bv-search-user-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <input class="bv-search-input" type="text" name="search" value="{{ request('search') }}"
                                    placeholder="Service or Professional"
                                    autocomplete="off" role="combobox" aria-expanded="false"
                                    aria-controls="bvSuggestPanel" aria-autocomplete="list">
                                {{-- Reset sits inside the field, right against the search
                                     icon, rather than adding a button of its own. Shown only
                                     once there is something to clear. --}}
                                @if($canReset)
                                <a href="{{ $resetUrl }}" class="bv-reset-btn" title="Clear search" aria-label="Clear search">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.6"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                                @endif
                                {{-- Icon-only submit, shown on mobile in place of the text button --}}
                                <button class="bv-search-icon-btn" type="submit" aria-label="Search">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Specialty --}}
                            <div class="bv-search-field custom-dropdown-wrap" id="specialty-dropdown-wrap" style="position: relative; overflow: visible; z-index: 50; cursor: pointer;">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24" style="flex-shrink: 0;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <div class="custom-dropdown-trigger" id="specialty-trigger">
                                    <span class="custom-dropdown-label" id="specialty-label">
                                        @php
                                            $selectedLabel = 'All Categories';
                                            $selectedCategory = $activeCategory;
                                            if ($selectedCategory && isset($allThemes[$selectedCategory])) {
                                                $selectedLabel = ($allThemes[$selectedCategory]['emoji'] ?? '✨') . ' ' . ($allThemes[$selectedCategory]['label'] ?? ucfirst($selectedCategory));
                                            }
                                        @endphp
                                        {{ $selectedLabel }}
                                    </span>
                                </div>
                                <svg class="bv-search-caret" width="14" height="14" fill="none" stroke="white"
                                    stroke-width="2" viewBox="0 0 24 24" style="pointer-events: none; position: absolute; right: 20px; top: 50%; transform: translateY(-50%); opacity: 0.4;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>

                                <div class="custom-dropdown-menu">
                                    <div class="custom-dropdown-item {{ $isAllActive ? 'selected' : '' }}" data-value="">All Categories</div>
                                    @foreach($allThemes as $key => $t)
                                        <div class="custom-dropdown-item {{ $activeCategory == $key ? 'selected' : '' }}" data-value="{{ $key }}">
                                            {{ $t['emoji'] ?? '✨' }} {{ $t['label'] ?? ucfirst($key) }}
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="type" id="specialty-input" value="{{ $activeCategory }}">
                            </div>

                            {{-- Current location.
                                 Once the visitor's position is known this stops
                                 being a "Near Me" call to action and simply states
                                 where they are, down to the suburb — which is also
                                 the ordering the results below are already in
                                 (nearest first, see rankCandidates). It stays
                                 clickable so the location can be refreshed after a
                                 move; a click re-detects and resubmits the form. --}}
                            @php
                                // Corrected on display — see config/place_names.php.
                                $locSuburb = \App\Services\PlaceNameService::correct(request()->cookie('user_suburb'));
                                $locCity   = trim((string) request()->cookie('user_city'));
                                $locState  = trim((string) request()->cookie('user_state'));
                                $locCoords = is_numeric(request()->cookie('user_lat'))
                                    && is_numeric(request()->cookie('user_lng'));

                                $locLabel = $locSuburb ?: ($locCity ?: $locState);
                                // Coordinates with no name yet: the label is filled in
                                // by the background backfill a moment later.
                                $hasLocation = $locLabel !== '' || $locCoords;
                                $locLabel    = $locLabel ?: 'Current Location';
                                // Line beneath the place name — the city/state the
                                // suburb sits in, when we know something wider.
                                $locContext  = $locSuburb !== '' ? ($locCity ?: $locState) : $locState;
                            @endphp
                            <div class="bv-search-field bv-nearme {{ $hasLocation ? 'has-location' : '' }}"
                                style="border-right:none; cursor:pointer;"
                                title="{{ $hasLocation ? $locLabel . ' — tap to update your location' : 'Find experts near you' }}"
                                :class="{ 'is-locating': locating }"
                                x-data="{
                                    locating: false,
                                    useGPS() {
                                        this.locating = true;
                                        const form = $el.closest('form');
                                        /* Asks again even if location was refused before;
                                           if the browser has stopped prompting, the helper
                                           opens the how-to-enable modal instead. */
                                        window.requestLocationWithHelp()
                                            .then(() => form.submit())
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
                                @click="useGPS()">
                                <span class="bv-nearme-icon">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24" style="flex-shrink:0;" x-show="!locating">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <svg class="animate-spin" width="20" height="20" fill="none" viewBox="0 0 24 24" style="flex-shrink:0;" x-show="locating" x-cloak>
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                @if($hasLocation)
                                    <span class="bv-nearme-place" x-show="!locating">
                                        <span class="bv-nearme-eyebrow">Your location</span>
                                        <span class="bv-nearme-label"
                                              data-location-label
                                              data-location-max="22">{{ \Illuminate\Support\Str::limit($locLabel, 22, '…') }}</span>
                                        @if($locContext !== '')
                                            <span class="bv-nearme-context">{{ $locContext }}</span>
                                        @endif
                                    </span>
                                    <span class="bv-nearme-label" x-show="locating" x-cloak>Updating…</span>
                                @else
                                    <span class="bv-nearme-label" x-text="locating ? 'Locating…' : 'Near Me'"></span>
                                @endif
                                <span class="bv-nearme-arrow" aria-hidden="true">
                                    <svg width="17" height="17" fill="currentColor" stroke="none"
                                        viewBox="0 0 24 24">
                                        <path d="M21 3L3 10.53v.98l6.84 2.65L12.48 21h.98L21 3z" />
                                    </svg>
                                </span>
                            </div>

                            <button class="bv-search-btn" type="submit">Search Services</button>
                        </form>
                    </div>

                    {{-- Live results panel. Empty until the customer types, and
                         re-parented to <body> on load: the hero clips its own
                         overflow (the glow orbs depend on it), so a panel left
                         inside it would be cut off. Positioned under the search
                         bar by the script instead. --}}
                    <div class="bv-suggest" id="bvSuggestPanel" role="listbox"
                         aria-label="Matching businesses" hidden></div>

                    {{-- ── Category Pills ── --}}
                    {{-- ── Category Pills ── --}}
                    @php
                    $catMeta = [
                    'health' => ['g'=>['#00c853','#64dd17'], 'rgb'=>'0,200,83', 'sub'=>'Green Care'],
                    'doctor' => ['g'=>['#00c853','#64dd17'], 'rgb'=>'0,200,83', 'sub'=>'Doctors & Clinics'],
                    'beauty' => ['g'=>['#ff6d00','#ffab40'], 'rgb'=>'255,109,0', 'sub'=>'Best Stylists'],
                    'barber' => ['g'=>['#ff6d00','#ffab40'], 'rgb'=>'255,109,0', 'sub'=>'Mens Grooming'],
                    'sports' => ['g'=>['#ffd600','#ffea00'], 'rgb'=>'255,214,0', 'sub'=>'Active Routine'],
                    'activity' => ['g'=>['#ffd600','#ffea00'], 'rgb'=>'255,214,0', 'sub'=>'Active Routine'],
                    'consultant' => ['g'=>['#2979ff','#00b0ff'], 'rgb'=>'41,121,255', 'sub'=>'Pro & Prime'],
                    'training' => ['g'=>['#7c3aed','#a78bfa'], 'rgb'=>'124,58,237', 'sub'=>'Get Stronger'],
                    'default' => ['g'=>['#1a237e','#3949ab'], 'rgb'=>'26,35,126', 'sub'=>'All Experts'],
                    ];
                    
                    $categoriesList = [];
                    
                    // First item: All Services — its own page, streamed the same
                    // way every category page is.
                    $categoriesList[] = [
                        'key' => $allSlug,
                        'label' => 'All',
                        'sub' => 'Services',
                        'emoji' => '⭐',
                        'rgb' => '255,109,0',
                        'g' => ['#ff6d00', '#ffab40']
                    ];
                    
                    // Dynamic themes
                    foreach($allThemes as $key => $t) {
                        $cm = $catMeta[$key] ?? $catMeta['default'];
                        $categoriesList[] = [
                            'key' => $key,
                            'label' => $t['label'],
                            'sub' => $cm['sub'],
                            'emoji' => $t['emoji'] ?? '✨',
                            'rgb' => $cm['rgb'],
                            'g' => $cm['g']
                        ];
                    }
                    
                    // Find active index
                    $activeIndex = 0;
                    $currentType = $activeCategory === '' ? $allSlug : $activeCategory;
                    foreach($categoriesList as $index => $cat) {
                        if ($cat['key'] === $currentType) {
                            $activeIndex = $index;
                            break;
                        }
                    }
                    
                    $totalCats = count($categoriesList);
                    $prevIndex = ($activeIndex - 1 + $totalCats) % $totalCats;
                    $nextIndex = ($activeIndex + 1) % $totalCats;
                    
                    $prevCat = $categoriesList[$prevIndex];
                    $nextCat = $categoriesList[$nextIndex];
                    $activeCat = $categoriesList[$activeIndex];
                    @endphp

                    {{-- ── Desktop Category Pills ── --}}
                    <div class="bv-cat-desktop-wrap">
                        <div class="bv-cat-scroll-wrap">
                            <button class="bv-cat-scroll-btn" id="cat-prev" aria-label="Previous" onclick="document.getElementById('catRow').scrollBy({left:-200,behavior:'smooth'})">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <div class="bv-cat-row" id="catRow">
                                @foreach($categoriesList as $cat)
                                @php
                                [$cr,$cg,$cb] = explode(',', $cat['rgb']);
                                $iconStyle = "background:linear-gradient(135deg,{$cat['g'][0]},{$cat['g'][1]});";
                                $isActive = $cat['key'] === $allSlug ? $isAllActive : $activeCategory === $cat['key'];
                                $catHref = route('category.show', $cat['key']);
                                @endphp
                                <a href="{{ $catHref }}"
                                    class="bv-cat-pill {{ $isActive ? 'active' : '' }}"
                                    style="--cr:{{ trim($cr) }};--cg:{{ trim($cg) }};--cb:{{ trim($cb) }};">
                                    <div class="bv-cat-icon" style="{{ $iconStyle }}">{{ $cat['emoji'] }}</div>
                                    <div class="bv-cat-text">
                                        <span class="bv-cat-name">{{ $cat['label'] }}</span>
                                        <span class="bv-cat-sub" style="{{ $cat['key'] === '' ? 'color:rgba(255,171,64,0.9);' : '' }}">{{ $cat['sub'] }}</span>
                                    </div>
                                </a>
                                @endforeach
                            </div>{{-- /bv-cat-row --}}
                            <button class="bv-cat-scroll-btn" id="cat-next" aria-label="Next" onclick="document.getElementById('catRow').scrollBy({left:200,behavior:'smooth'})">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>{{-- /bv-cat-scroll-wrap --}}
                    </div>

                    {{-- ── Mobile Category Slider ── --}}
                    <div class="bv-cat-mobile-container">
                        <div class="bv-cat-mobile-wrap">
                            <button type="button" class="bv-cat-mobile-btn" aria-label="Previous" onclick="scrollMobileCategories(-1)">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </button>

                            {{-- data-auto-slide: mobile-only auto-advance, driven by the
                                 shared carousel script in the layout. Pauses on touch. --}}
                            <div class="bv-cat-mobile-row" id="catRowMobile"
                                 data-auto-slide data-auto-slide-interval="2800">
                                @foreach($categoriesList as $index => $cat)
                                @php
                                [$cr,$cg,$cb] = explode(',', $cat['rgb']);
                                $iconStyle = "background:linear-gradient(135deg,{$cat['g'][0]},{$cat['g'][1]});";
                                $isActive = $cat['key'] === $allSlug ? $isAllActive : $activeCategory === $cat['key'];
                                $catHref = route('category.show', $cat['key']);
                                @endphp
                                <a href="{{ $catHref }}" 
                                   class="bv-cat-mobile-pill {{ $isActive ? 'active' : '' }}" 
                                   style="--cr:{{ trim($cr) }}; --cg:{{ trim($cg) }}; --cb:{{ trim($cb) }};">
                                    <div class="bv-cat-mobile-icon" style="{{ $iconStyle }}">
                                        {{ $cat['emoji'] }}
                                    </div>
                                    <div class="bv-cat-mobile-text">
                                        <span class="bv-cat-mobile-name">{{ $cat['label'] }}</span>
                                        <span class="bv-cat-mobile-sub" style="color: rgba({{ $cat['rgb'] }}, 0.95);">{{ $cat['sub'] }}</span>
                                    </div>
                                </a>
                                @endforeach
                            </div>

                            <button type="button" class="bv-cat-mobile-btn" aria-label="Next" onclick="scrollMobileCategories(1)">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>

                        <div class="bv-cat-mobile-dots">
                            @foreach($categoriesList as $index => $cat)
                                <span class="bv-cat-mobile-dot {{ $index === $activeIndex ? 'active' : '' }}"
                                   data-rgb="{{ $cat['rgb'] }}"
                                   onclick="scrollToMobileCategory({{ $index }})"
                                   role="button"
                                   aria-label="Go to category {{ $cat['label'] }}"
                                   style="{{ $index === $activeIndex ? 'background-color: rgb(' . $cat['rgb'] . '); box-shadow: 0 0 8px rgb(' . $cat['rgb'] . ');' : '' }}"></span>
                            @endforeach
                        </div>
                    </div>
                </div>
