    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const counters = document.querySelectorAll('[data-counter]');
            const animate = (el) => {
                const target = parseFloat(el.dataset.target);
                const decimals = parseInt(el.dataset.decimals) || 0;
                const suffix = el.dataset.suffix || '';
                const start = performance.now();
                const duration = 2000;
                const tick = (now) => {
                    const p = Math.min((now - start) / duration, 1);
                    const e = 1 - Math.pow(1 - p, 4);
                    el.innerText = (e * target).toFixed(decimals) + suffix;
                    if (p < 1) requestAnimationFrame(tick);
                    else el.innerText = target.toFixed(decimals) + suffix;
                };
                requestAnimationFrame(tick);
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(en => { if (en.isIntersecting) { animate(en.target); observer.unobserve(en.target); } });
            }, { threshold: 0.1 });
            counters.forEach(c => observer.observe(c));

            // Custom Dropdown Logic
            const dropdownWrap = document.getElementById('specialty-dropdown-wrap');
            const dropdownLabel = document.getElementById('specialty-label');
            const dropdownInput = document.getElementById('specialty-input');
            const dropdownItems = document.querySelectorAll('.custom-dropdown-item');

            if(dropdownWrap) {
                dropdownWrap.addEventListener('click', function(e) {
                    if (e.target.closest('.custom-dropdown-item')) return;
                    e.stopPropagation();
                    dropdownWrap.classList.toggle('open');
                });

                dropdownItems.forEach(item => {
                    item.addEventListener('click', function(e) {
                        e.stopPropagation();
                        // Update input
                        const val = this.getAttribute('data-value');
                        dropdownInput.value = val;
                        
                        // Update label
                        dropdownLabel.innerHTML = this.innerHTML.trim();
                        
                        // Update selected class
                        dropdownItems.forEach(i => i.classList.remove('selected'));
                        this.classList.add('selected');
                        
                        // Close dropdown
                        dropdownWrap.classList.remove('open');
                    });
                });

                document.addEventListener('click', function(e) {
                    if (!dropdownWrap.contains(e.target)) {
                        dropdownWrap.classList.remove('open');
                    }
                });
            }
            /* ══════════════════════════════════════════════════════════
               SEARCH-AS-YOU-TYPE
               From the 3rd character, the matching businesses are shown as
               mini cards under the search bar. Scope comes from the page:
               the category dropdown's value on the listing, the page's own
               slug on a category page (both live in #specialty-input).
               ══════════════════════════════════════════════════════════ */
            const suggestWrap = document.querySelector('.bv-search-wrap[data-suggest-url]');
            const suggestPanel = document.getElementById('bvSuggestPanel');

            if (suggestWrap && suggestPanel) {
                const bar       = suggestWrap.querySelector('.bv-search-bar');
                const form      = suggestWrap.querySelector('.bv-search-form');
                const input     = suggestWrap.querySelector('.bv-search-input');
                const typeInput = suggestWrap.querySelector('#specialty-input');
                const minChars  = parseInt(suggestWrap.dataset.suggestMin, 10) || 3;

                /* The hero clips its overflow, so the panel is anchored to the
                   search bar from <body> instead of nesting inside it. */
                document.body.appendChild(suggestPanel);
                suggestPanel.hidden = false;

                let debounce = null;
                let request  = null;
                let lastQuery = null;
                let open = false;

                const place = () => {
                    const rect = bar.getBoundingClientRect();
                    const room = window.innerHeight - rect.bottom - 16;

                    suggestPanel.style.left  = rect.left + 'px';
                    suggestPanel.style.width = rect.width + 'px';
                    suggestPanel.style.top   = (rect.bottom + 10) + 'px';
                    suggestPanel.style.maxHeight = Math.max(180, Math.min(430, room)) + 'px';
                };

                const show = () => {
                    if (suggestPanel.innerHTML.trim() === '') return;
                    open = true;
                    place();
                    suggestPanel.classList.add('is-open');
                    input.setAttribute('aria-expanded', 'true');
                };

                const hide = () => {
                    open = false;
                    suggestPanel.classList.remove('is-open');
                    input.setAttribute('aria-expanded', 'false');
                };

                const render = (html) => {
                    suggestPanel.innerHTML = html;
                    suggestPanel.scrollTop = 0;
                    if (html.trim() === '') { hide(); return; }
                    show();
                };

                const load = (query) => {
                    const url = new URL(suggestWrap.dataset.suggestUrl, window.location.origin);
                    url.searchParams.set('q', query);

                    const type = (typeInput && typeInput.value)
                        || suggestWrap.dataset.suggestCategory
                        || '';
                    if (type) url.searchParams.set('type', type);

                    /* One request in flight: an earlier keystroke's answer must
                       never land on top of a later one. */
                    if (request) request.abort();
                    request = new AbortController();

                    if (!open) render('<div class="bv-sg-loading">Searching…</div>');

                    fetch(url.toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        signal: request.signal,
                    })
                        .then((response) => {
                            if (!response.ok) throw new Error('HTTP ' + response.status);
                            return response.json();
                        })
                        .then((data) => {
                            lastQuery = query;
                            render(data.html || '');
                        })
                        .catch((error) => {
                            if (error.name === 'AbortError') return;
                            hide();
                        });
                };

                const onType = () => {
                    const query = input.value.trim();

                    clearTimeout(debounce);

                    if (query.length < minChars) {
                        if (request) request.abort();
                        lastQuery = null;
                        suggestPanel.innerHTML = '';
                        hide();
                        return;
                    }

                    if (query === lastQuery) { show(); return; }

                    debounce = setTimeout(() => load(query), 2000);
                };

                input.addEventListener('input', onType);

                input.addEventListener('focus', () => {
                    if (input.value.trim().length >= minChars) onType();
                });

                /* Keyboard: walk the results, Enter opens the highlighted one,
                   Escape closes without clearing what was typed. */
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') { hide(); return; }

                    if (!open) return;

                    const cards = Array.from(suggestPanel.querySelectorAll('.bv-sg-card'));
                    if (!cards.length) return;

                    const current = cards.findIndex(c => c.classList.contains('is-active'));

                    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                        e.preventDefault();
                        const step = e.key === 'ArrowDown' ? 1 : -1;
                        const next = current < 0
                            ? (step > 0 ? 0 : cards.length - 1)
                            : (current + step + cards.length) % cards.length;
                        cards.forEach(c => c.classList.remove('is-active'));
                        cards[next].classList.add('is-active');
                        cards[next].scrollIntoView({ block: 'nearest' });
                    } else if (e.key === 'Enter' && current > -1) {
                        e.preventDefault();
                        // Business and professional rows are links; area rows
                        // are buttons whose click replays the search.
                        if (cards[current].href) {
                            window.location.href = cards[current].href;
                        } else {
                            cards[current].click();
                        }
                    }
                });

                /* "View all N results" hands over to the page's own results;
                   an area row replays the search with its address as the
                   keyword, so the results page applies its usual filters. */
                suggestPanel.addEventListener('click', (e) => {
                    if (e.target.closest('[data-suggest-submit]')) {
                        hide();
                        form.submit();
                        return;
                    }

                    const fill = e.target.closest('[data-suggest-fill]');
                    if (fill) {
                        input.value = fill.dataset.suggestFill;
                        hide();
                        form.submit();
                    }
                });

                document.addEventListener('click', (e) => {
                    if (!suggestPanel.contains(e.target) && !suggestWrap.contains(e.target)) hide();
                });

                /* The panel is anchored to the bar's viewport rect, so it has
                   to follow it — scrolling, resizing and the mobile keyboard
                   opening all move it. */
                let placing = false;
                const reposition = () => {
                    if (!open || placing) return;
                    placing = true;
                    requestAnimationFrame(() => { placing = false; if (open) place(); });
                };

                window.addEventListener('scroll', reposition, { passive: true, capture: true });
                window.addEventListener('resize', reposition);
                if (window.visualViewport) {
                    window.visualViewport.addEventListener('resize', reposition);
                    window.visualViewport.addEventListener('scroll', reposition);
                }
            }

            // Mobile Category Carousel Scroll Logic
            const rowMobile = document.getElementById('catRowMobile');
            const dotsMobile = document.querySelectorAll('.bv-cat-mobile-dot');
            const pillsMobile = document.querySelectorAll('.bv-cat-mobile-pill');
            
            /*
             * Centre a pill inside its own scroller.
             *
             * Deliberately NOT scrollIntoView: that walks up and scrolls every
             * scrollable ancestor including the document, so centring a pill
             * that sits far along the row (the last category, say) dragged the
             * whole page sideways on mobile. Setting scrollLeft moves the row
             * and nothing else. Measured from bounding rects so it does not
             * care which element is the pill's offsetParent.
             */
            const centreInRow = (row, pill, behavior) => {
                if (!row || !pill || row.scrollWidth <= row.clientWidth) return;

                const offset = pill.getBoundingClientRect().left - row.getBoundingClientRect().left;
                const left   = row.scrollLeft + offset - (row.clientWidth - pill.offsetWidth) / 2;

                row.scrollTo({ left: Math.max(0, left), behavior: behavior });
            };

            if (rowMobile && dotsMobile.length && pillsMobile.length) {
                const pillWidth = 232; // 220px width + 12px gap

                window.scrollMobileCategories = function(direction) {
                    rowMobile.scrollBy({ left: direction * pillWidth, behavior: 'smooth' });
                };

                window.scrollToMobileCategory = function(index) {
                    centreInRow(rowMobile, pillsMobile[index], 'smooth');
                };

                // Centre the active category pill on load — the category page
                // always opens on one, and it is rarely the first.
                setTimeout(() => {
                    centreInRow(rowMobile, rowMobile.querySelector('.bv-cat-mobile-pill.active'), 'auto');
                    centreInRow(
                        document.getElementById('catRow'),
                        document.querySelector('#catRow .bv-cat-pill.active'),
                        'auto'
                    );
                }, 100);

                // Update active indicator dot as the container scrolls (finger swipe or arrows)
                rowMobile.addEventListener('scroll', () => {
                    let closestIndex = 0;
                    let minDiff = Infinity;
                    pillsMobile.forEach((pill, index) => {
                        const pillCenter = pill.offsetLeft + pill.clientWidth / 2;
                        const containerCenter = rowMobile.scrollLeft + rowMobile.clientWidth / 2;
                        const diff = Math.abs(pillCenter - containerCenter);
                        if (diff < minDiff) {
                            minDiff = diff;
                            closestIndex = index;
                        }
                    });

                    dotsMobile.forEach((dot, index) => {
                        if (index === closestIndex) {
                            dot.classList.add('active');
                            const rgb = dot.getAttribute('data-rgb');
                            dot.style.backgroundColor = `rgb(${rgb})`;
                            dot.style.boxShadow = `0 0 8px rgb(${rgb})`;
                        } else {
                            dot.classList.remove('active');
                            dot.style.backgroundColor = '';
                            dot.style.boxShadow = '';
                        }
                    });
                });
            }
        });
    </script>
