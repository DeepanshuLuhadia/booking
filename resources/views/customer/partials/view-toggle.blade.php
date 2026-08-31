{{-- ── Mobile View Toggle — Grid / List ────────────────────────────────
     Visible only on ≤600px. Toggling adds/removes .bv-list-mode on the
     .bv-grid elements. Preference is persisted in localStorage so it
     survives navigation.
──────────────────────────────────────────────────────────────────────── --}}
<div class="bv-view-toggle" id="bvViewToggle" role="group" aria-label="View mode">

    {{-- Grid view button --}}
    <button type="button"
            id="bvToggleGrid"
            class="bv-view-btn bv-view-btn--active"
            aria-pressed="true"
            title="Grid view">
        {{-- 2×2 grid icon --}}
        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
            <rect x="0" y="0" width="7" height="7" rx="1.5"/>
            <rect x="9" y="0" width="7" height="7" rx="1.5"/>
            <rect x="0" y="9" width="7" height="7" rx="1.5"/>
            <rect x="9" y="9" width="7" height="7" rx="1.5"/>
        </svg>
        <span class="bv-view-label">Grid</span>
    </button>

    {{-- List view button --}}
    <button type="button"
            id="bvToggleList"
            class="bv-view-btn"
            aria-pressed="false"
            title="List view">
        {{-- Horizontal list icon --}}
        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
            <rect x="0" y="0" width="5" height="5" rx="1"/>
            <rect x="7" y="1" width="9" height="2" rx="1"/>
            <rect x="7" y="3.5" width="6" height="1.2" rx="0.6"/>
            <rect x="0" y="7" width="5" height="5" rx="1"/>
            <rect x="7" y="8" width="9" height="2" rx="1"/>
            <rect x="7" y="10.5" width="6" height="1.2" rx="0.6"/>
        </svg>
        <span class="bv-view-label">List</span>
    </button>

</div>

<script>
(function () {
    'use strict';

    const STORAGE_KEY = 'bv_listing_view'; // 'grid' | 'list'

    function init() {
        const toggle  = document.getElementById('bvViewToggle');
        const btnGrid = document.getElementById('bvToggleGrid');
        const btnList = document.getElementById('bvToggleList');

        if (!toggle || !btnGrid || !btnList) return;

        function getGrids() {
            return document.querySelectorAll('.bv-grid');
        }

        /* ── Apply a mode ────────────────────────────────────────────── */
        function applyMode(mode) {
            const isList = mode === 'list';
            const grids = getGrids();

            grids.forEach(grid => {
                grid.classList.toggle('bv-list-mode', isList);
            });

            btnGrid.classList.toggle('bv-view-btn--active', !isList);
            btnGrid.setAttribute('aria-pressed', String(!isList));

            btnList.classList.toggle('bv-view-btn--active', isList);
            btnList.setAttribute('aria-pressed', String(isList));

            try { localStorage.setItem(STORAGE_KEY, mode); } catch (_) {}
        }

        /* ── Restore saved preference on load ────────────────────────── */
        const saved = (() => {
            try { return localStorage.getItem(STORAGE_KEY); } catch (_) { return null; }
        })();

        applyMode(saved === 'list' ? 'list' : 'grid'); // default: grid

        /* ── Button click listeners ─────────────────────────────────── */
        btnGrid.onclick = function(e) {
            e.preventDefault();
            applyMode('grid');
        };

        btnList.onclick = function(e) {
            e.preventDefault();
            applyMode('list');
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
