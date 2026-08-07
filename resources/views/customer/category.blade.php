<x-app-layout page-title="{{ $categoryLabel }} Professionals" footer-mode="minimal">

    {{-- Same visual system as the discovery listing — the page is that listing
         narrowed to one category, so it shares the stylesheet verbatim. --}}
    @include('customer.partials.listing-styles')

    <style>
        /* ── Category page ───────────────────────────────────────────────
           The hero stops at the category pills: no stat counters, no steps,
           no CTA — the grid is the page. */
        .bv-cat-hero {
            padding-bottom: 40px;
        }

        .bv-cat-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            padding: 8px 18px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .1);
            color: rgba(255, 255, 255, .6);
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .2em;
            text-decoration: none;
            transition: all .25s;
        }

        .bv-cat-back:hover {
            color: #fff;
            background: rgba(255, 255, 255, .1);
        }

        .bv-cat-count {
            display: inline-block;
            margin-top: 6px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .25em;
            color: rgba(255, 255, 255, .35);
        }

        /* The grid follows the pills directly — no stat row, no section heading
           to fill the space the listing page puts here — so the inherited
           section padding left a wide empty band above the first card. */
        .bv-cat-page .bv-section {
            padding-top: 32px;
        }

        .bv-cat-page .bv-grid {
            margin-top: 24px;
        }

        /* Infinite-scroll furniture ships with the feed partial. */

        /* ── Mobile (≤600px) ─────────────────────────────────────────────
           The landing page fills the space under the category pills with its
           stat counters and a section heading. This page has neither, so the
           inherited spacing left a dead band between the pills and the first
           card. Scoped to .bv-cat-page so the discovery listing keeps its
           original rhythm. */
        @media (max-width: 600px) {
            .bv-cat-page .bv-cat-hero {
                padding-bottom: 16px;
            }

            .bv-cat-page .bv-section {
                padding-top: 10px;
            }

            .bv-cat-page .bv-grid {
                margin-top: 14px;
            }

            .bv-cat-page .bv-scroll-status {
                padding-top: 26px;
            }

            /* Wide tracking pushed this onto two ragged lines on a phone */
            .bv-cat-page .bv-cat-count {
                font-size: 10px;
                letter-spacing: .16em;
            }

            .bv-cat-page .bv-cat-back {
                margin-bottom: 16px;
            }
        }
    </style>

    <div class="bv-page bv-cat-page">

        {{-- ═══════════════════════════════════════════════════════
        HEADER — hero, search and category pills, nothing below
        ═══════════════════════════════════════════════════════ --}}
        <section class="bv-hero bv-cat-hero">
            <div class="bv-hero-glow-1" aria-hidden="true"></div>
            <div class="bv-hero-glow-2" aria-hidden="true"></div>
            <div class="bv-hero-crowd" aria-hidden="true"></div>
            <div class="bv-hero-grid" aria-hidden="true"></div>

            <div style="position:relative; z-index:10; text-align:center;">

                {{-- The catalogue view is already "all categories", so from
                     there this reads as a way back to the landing page. --}}
                <a href="{{ route('home') }}" class="bv-cat-back">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    {{ $isAllCategories ? 'Back Home' : 'All Categories' }}
                </a>

                <h1 style="font-size:clamp(2rem,7vw,4.4rem); font-weight:900; color:#fff; line-height:1.05; letter-spacing:-.03em; margin:0 0 14px;">
                    {{ $categoryEmoji }} {{ $categoryLabel }}
                    <span style="color:transparent; background:linear-gradient(135deg,#ff8c42,#ffab40); -webkit-background-clip:text; background-clip:text; font-style:italic; padding-right:0.12em; display:inline-block;">Professionals</span>
                </h1>

                {{-- No invented figure: the count is the real size of the list
                     the grid below is streaming through. --}}
                <p style="color:rgba(255,255,255,.45); font-size:1.05rem; max-width:520px; margin:0 auto; line-height:1.7;">
                    @if($isAllCategories)
                        Book verified specialists from every category near you.
                    @else
                        Book verified {{ strtolower($categoryLabel) }} specialists near you.
                    @endif
                </p>
                @if($totalVendors > 0)
                <div class="bv-cat-count">
                    {{ $totalVendors }} {{ Str::plural('professional', $totalVendors) }}
                    @if($isSearch) matching &ldquo;{{ $searchTerm }}&rdquo; @else available @endif
                    {{-- Names the place the ordering is measured from, when known. --}}
                    · {{ $rankedByDistance
                            ? 'Nearest first' . ($locationLabel ? ' from ' . $locationLabel : '')
                            : 'Top rated first' }}
                </div>
                @endif

                <div style="margin-top:40px;">
                    {{-- Submits back to this page: a search inside a category
                         narrows the category rather than throwing the customer
                         out to the global listing. --}}
                    @include('customer.partials.search-categories', [
                        'allThemes'      => $allThemes,
                        'activeCategory' => $categorySlug,
                        'searchAction'   => route('category.show', $categorySlug),
                        'resetUrl'       => route('category.show', $categorySlug),
                        'canReset'       => $isSearch,
                    ])
                </div>
            </div>

            <div class="bv-hero-curve" aria-hidden="true"></div>
        </section>

        {{-- ═══════════════════════════════════════════════════════
        VENDORS — streamed {{ $perPage }} at a time as the page scrolls
        ═══════════════════════════════════════════════════════ --}}
        <section class="bv-section">
            <div style="max-width:1100px; margin:0 auto;">
                @if($vendors->isNotEmpty())
                {{-- Mobile only: grid / list toggle (hidden on desktop via CSS) --}}
                @include('customer.partials.view-toggle')

                <div class="bv-grid" id="bvCategoryGrid">
                    @include('customer.partials.vendor-cards', [
                        'vendors'    => $vendors,
                        'allThemes'  => $allThemes,
                        'eagerFirst' => true,
                    ])
                </div>

                {{-- The feed URL carries the page's own query string, so a search
                     narrows every batch the scroll pulls, not just the first. --}}
                @include('customer.partials.scroll-feed', [
                    'gridId'     => 'bvCategoryGrid',
                    'endpoint'   => route('category.vendors', ['slug' => $categorySlug] + request()->except(['page', 'slug'])),
                    'hasMore'    => $hasMore,
                    'endMessage' => $isAllCategories
                        ? 'You’ve seen every professional'
                        : 'You’ve seen every ' . strtolower($categoryLabel) . ' professional',
                ])
                @else
                <div style="padding:80px 0; text-align:center;">
                    <div style="font-size:5rem; opacity:.2;">📭</div>
                    @if($isSearch)
                    <h3 style="color:#fff; font-size:2rem; margin:24px 0 12px;">
                        {{ $isAllCategories ? 'No Matches Found' : 'No Matches in ' . $categoryLabel }}
                    </h3>
                    <p style="color:rgba(255,255,255,.4);">Nothing here matches &ldquo;{{ $searchTerm }}&rdquo;. Try another term.</p>
                    <a href="{{ route('category.show', $categorySlug) }}"
                        style="display:inline-block; margin-top:28px; background:linear-gradient(135deg,#ff6d00,#ffab40); color:#fff; font-weight:800; padding:14px 32px; border-radius:12px; text-decoration:none;">Clear
                        Search</a>
                    @else
                    <h3 style="color:#fff; font-size:2rem; margin:24px 0 12px;">
                        {{ $isAllCategories ? 'No Experts Available Yet' : 'No ' . $categoryLabel . ' Experts Yet' }}
                    </h3>
                    <p style="color:rgba(255,255,255,.4);">
                        {{ $isAllCategories
                            ? 'Nobody is open for bookings right now. Please check back shortly.'
                            : 'Nobody in this category is open for bookings right now.' }}
                    </p>
                    <a href="{{ route('home') }}"
                        style="display:inline-block; margin-top:28px; background:linear-gradient(135deg,#ff6d00,#ffab40); color:#fff; font-weight:800; padding:14px 32px; border-radius:12px; text-decoration:none;">Browse
                        All Professionals</a>
                    @endif
                </div>
                @endif
            </div>
        </section>

    </div>{{-- .bv-page --}}

    {{-- Shared search dropdown + mobile category carousel behaviour --}}
    @include('customer.partials.listing-scripts')

</x-app-layout>
