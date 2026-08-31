{{-- ── Infinite scroll ──────────────────────────────────────────────────
     Sentinel + status furniture + the loader that fills the grid above it.
     Shared by the discovery listing and the category pages, so both stream
     the same way and neither carries a next/previous pager.

     Callers pass:
       $gridId     — id of the .bv-grid the batches are appended to
       $endpoint   — JSON feed returning { html, has_more, next_page }
       $hasMore    — whether anything follows the first screenful
       $endMessage — line shown once the list is exhausted
──────────────────────────────────────────────────────────────────────── --}}
@php
    $gridId     = $gridId ?? 'bvFeedGrid';
    $endMessage = $endMessage ?? 'You’ve seen every professional';
@endphp

<style>
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

{{-- Kept outside the grid so an added row never lands in a card column. --}}
<div class="bv-scroll-status"
     id="bvScrollStatus"
     data-grid="{{ $gridId }}"
     data-endpoint="{{ $endpoint }}"
     data-next-page="2"
     data-has-more="{{ $hasMore ? '1' : '0' }}">
    <div class="bv-scroll-spinner" id="bvScrollSpinner" style="display:none;"></div>
    <div class="bv-scroll-end" id="bvScrollEnd" style="{{ $hasMore ? 'display:none;' : '' }}">
        {{ $endMessage }}
    </div>
    <div id="bvScrollError" style="display:none;">
        <div style="color:rgba(255,255,255,.45); font-size:13px;">Could not load more professionals.</div>
        <button type="button" class="bv-scroll-retry" id="bvScrollRetry">Try Again</button>
    </div>
</div>

{{-- Pulls the next batch of pre-rendered cards as the sentinel nears the
     viewport. One request in flight at a time; a failure surfaces a retry
     rather than silently ending the list. --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const status = document.getElementById('bvScrollStatus');
        if (!status) return;

        const grid = document.getElementById(status.dataset.grid);
        if (!grid) return;

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
