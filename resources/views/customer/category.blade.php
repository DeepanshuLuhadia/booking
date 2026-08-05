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

        /* Infinite-scroll furniture */
        .bv-scroll-status {
            padding: 40px 0 10px;
            text-align: center;
        }

        .bv-scroll-spinner {
            width: 34px;
            height: 34px;
            margin: 0 auto;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, .12);
            border-top-color: #ff8c42;
            animation: bv-spin .8s linear infinite;
        }

        @keyframes bv-spin {
            to { transform: rotate(360deg); }
        }

        .bv-scroll-end {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .3em;
            color: rgba(255, 255, 255, .25);
        }

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

        .bv-scroll-retry {
            display: inline-block;
            margin-top: 14px;
            padding: 12px 28px;
            border-radius: 12px;
            background: linear-gradient(135deg, #ff6d00, #ffab40);
            color: #fff;
            font-weight: 800;
            font-size: 12px;
            border: 0;
            cursor: pointer;
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

                <a href="{{ route('home') }}" class="bv-cat-back">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    All Categories
                </a>

                <h1 style="font-size:clamp(2rem,7vw,4.4rem); font-weight:900; color:#fff; line-height:1.05; letter-spacing:-.03em; margin:0 0 14px;">
                    {{ $categoryEmoji }} {{ $categoryLabel }}
                    <span style="color:transparent; background:linear-gradient(135deg,#ff8c42,#ffab40); -webkit-background-clip:text; background-clip:text; font-style:italic; padding-right:0.12em; display:inline-block;">Professionals</span>
                </h1>

                {{-- No invented figure: the count is the real size of the list
                     the grid below is streaming through. --}}
                <p style="color:rgba(255,255,255,.45); font-size:1.05rem; max-width:520px; margin:0 auto; line-height:1.7;">
                    Book verified {{ strtolower($categoryLabel) }} specialists near you.
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
                <div class="bv-grid" id="bvCategoryGrid">
                    @include('customer.partials.vendor-cards', [
                        'vendors'    => $vendors,
                        'allThemes'  => $allThemes,
                        'eagerFirst' => true,
                    ])
                </div>

                {{-- Scroll sentinel + status. Kept outside the grid so an added
                     row never lands in a card column. --}}
                {{-- The feed URL carries the page's own query string, so a search
                     narrows every batch the scroll pulls, not just the first. --}}
                <div class="bv-scroll-status"
                     id="bvScrollStatus"
                     data-endpoint="{{ route('category.vendors', ['slug' => $categorySlug] + request()->except(['page', 'slug'])) }}"
                     data-next-page="2"
                     data-has-more="{{ $hasMore ? '1' : '0' }}">
                    <div class="bv-scroll-spinner" id="bvScrollSpinner" style="display:none;"></div>
                    <div class="bv-scroll-end" id="bvScrollEnd" style="{{ $hasMore ? 'display:none;' : '' }}">
                        You&rsquo;ve seen every {{ strtolower($categoryLabel) }} professional
                    </div>
                    <div id="bvScrollError" style="display:none;">
                        <div style="color:rgba(255,255,255,.45); font-size:13px;">Could not load more professionals.</div>
                        <button type="button" class="bv-scroll-retry" id="bvScrollRetry">Try Again</button>
                    </div>
                </div>
                @else
                <div style="padding:80px 0; text-align:center;">
                    <div style="font-size:5rem; opacity:.2;">📭</div>
                    @if($isSearch)
                    <h3 style="color:#fff; font-size:2rem; margin:24px 0 12px;">No Matches in {{ $categoryLabel }}</h3>
                    <p style="color:rgba(255,255,255,.4);">Nothing here matches &ldquo;{{ $searchTerm }}&rdquo;. Try another term.</p>
                    <a href="{{ route('category.show', $categorySlug) }}"
                        style="display:inline-block; margin-top:28px; background:linear-gradient(135deg,#ff6d00,#ffab40); color:#fff; font-weight:800; padding:14px 32px; border-radius:12px; text-decoration:none;">Clear
                        Search</a>
                    @else
                    <h3 style="color:#fff; font-size:2rem; margin:24px 0 12px;">No {{ $categoryLabel }} Experts Yet</h3>
                    <p style="color:rgba(255,255,255,.4);">Nobody in this category is open for bookings right now.</p>
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

    {{-- ── Infinite scroll ──────────────────────────────────────────
         Pulls the next batch of pre-rendered cards as the sentinel nears
         the viewport. One request in flight at a time; a failure surfaces a
         retry rather than silently ending the list. --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const grid    = document.getElementById('bvCategoryGrid');
            const status  = document.getElementById('bvScrollStatus');
            if (!grid || !status) return;

            const spinner = document.getElementById('bvScrollSpinner');
            const endMsg  = document.getElementById('bvScrollEnd');
            const errBox  = document.getElementById('bvScrollError');
            const retry   = document.getElementById('bvScrollRetry');

            let nextPage = parseInt(status.dataset.nextPage, 10) || 2;
            let hasMore  = status.dataset.hasMore === '1';
            let loading  = false;

            const showEnd = () => {
                hasMore = false;
                spinner.style.display = 'none';
                errBox.style.display  = 'none';
                endMsg.style.display  = '';
                observer.disconnect();
            };

            const loadMore = () => {
                if (loading || !hasMore) return;
                loading = true;
                spinner.style.display = '';
                errBox.style.display  = 'none';

                // URL-built rather than concatenated: the endpoint already
                // carries the search terms, so it may have a query string.
                const url = new URL(status.dataset.endpoint, window.location.origin);
                url.searchParams.set('page', nextPage);

                fetch(url.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                })
                    .then((response) => {
                        if (!response.ok) throw new Error('HTTP ' + response.status);
                        return response.json();
                    })
                    .then((data) => {
                        loading = false;
                        spinner.style.display = 'none';

                        if (data.html && data.html.trim() !== '') {
                            grid.insertAdjacentHTML('beforeend', data.html);
                        }

                        nextPage = data.next_page || (nextPage + 1);

                        if (!data.has_more) {
                            showEnd();
                        }
                    })
                    .catch(() => {
                        loading = false;
                        spinner.style.display = 'none';
                        errBox.style.display  = '';
                    });
            };

            // rootMargin starts the fetch a screenful early, so the next cards
            // are usually in place before the customer reaches the bottom.
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) loadMore();
                });
            }, { rootMargin: '600px 0px' });

            observer.observe(status);
            retry.addEventListener('click', loadMore);
        });
    </script>

</x-app-layout>
