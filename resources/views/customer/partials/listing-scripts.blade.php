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
