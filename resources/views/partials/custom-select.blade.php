{{--
|--------------------------------------------------------------------------
| Custom Select — one dropdown for the whole site
|--------------------------------------------------------------------------
| Every native <select> on the page (customer site, vendor hub, admin and
| employee panels — they all render through this layout) is upgraded to the
| same styled dropdown, so a filter in the admin panel looks like a filter on
| the landing page instead of the browser's own widget.
|
| Two things it fixes over the plain <select>:
|   1. One appearance everywhere. The native control could not be styled past
|      its box, so its option list came out white-on-white on these dark
|      screens and looked nothing like the search bar's category dropdown.
|   2. On phones the list is a bottom sheet pinned to the viewport. The old
|      absolutely-positioned menus were clipped by the nearest overflow:hidden
|      card and half the options were unreachable.
|
| The native <select> stays in the DOM (kept transparent behind the trigger),
| so form submission, `required`, `x-model`, `onchange="this.form.submit()"`
| and server-side validation keep working exactly as before. If JavaScript
| never runs, the native control is what the visitor gets.
|
| Opt out with data-no-custom on the select (or on any ancestor).
--}}
<style>
    .cs-wrap {
        position: relative;
        display: block;
        width: 100%;
    }

    /* Selects that were not full-width keep their intrinsic sizing so inline
       filter rows (e.g. the admin vendor page) do not stretch. */
    .cs-wrap.cs-auto {
        display: inline-block;
        width: auto;
        min-width: 190px;
        vertical-align: middle;
    }

    /* Kept, not removed: it is still the field that submits. Transparent and
       click-through, sitting exactly under the trigger. */
    .cs-native {
        position: absolute !important;
        inset: 0;
        width: 100% !important;
        height: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        min-height: 0 !important;
        opacity: 0 !important;
        pointer-events: none;
        border: 0 !important;
        background: transparent !important;
        -webkit-appearance: none;
        appearance: none;
    }

    /*
    | The trigger copies the class list off the <select> it replaces, so it is
    | the same height, radius, padding and type as the inputs beside it on that
    | page — the dropdown is shared, the field keeps the page's rhythm. Only
    | layout lives here; anything that would fight the copied classes does not.
    */
    .cs-trigger {
        position: relative;
        display: flex;
        align-items: center;
        gap: 10px;
        text-align: left;
        cursor: pointer;
        touch-action: manipulation;
        -webkit-appearance: none;
        appearance: none;
        transition: background .2s ease, border-color .2s ease, box-shadow .2s ease;
    }

    /* A select with no styling of its own still gets a sensible field. */
    .cs-trigger.cs-bare {
        width: 100%;
        min-height: 52px;
        padding: 12px 18px;
        border-radius: 14px;
        border: 1px solid rgba(255, 255, 255, .12);
        background: rgba(255, 255, 255, .05);
        color: #fff;
        font-size: 14px;
        font-weight: 700;
    }

    .cs-wrap.cs-open .cs-trigger,
    .cs-trigger:focus-visible {
        outline: none;
        border-color: var(--cs-accent, #ff6d00);
        box-shadow: 0 0 0 3px rgba(255, 109, 0, .16);
    }

    .cs-trigger:disabled {
        opacity: .4;
        cursor: not-allowed;
    }

    .cs-value {
        display: block;
        flex: 1 1 auto;
        min-width: 0;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    /* Nothing chosen yet — the placeholder option reads as a placeholder. */
    .cs-value.cs-placeholder {
        color: rgba(255, 255, 255, .4);
        font-weight: 600;
    }

    .cs-caret {
        flex: 0 0 auto;
        width: 14px;
        height: 14px;
        margin-left: auto;
        opacity: .45;
        pointer-events: none;
        transition: transform .25s ease;
    }

    .cs-wrap.cs-open .cs-caret {
        transform: rotate(180deg);
    }

    /* ── The menu ──────────────────────────────────────────────────────────
       Fixed, not absolute, and teleported to <body>: positioned from the
       trigger's rect each time it opens so no overflow:hidden card between
       here and the root can clip it. */
    .cs-menu {
        position: fixed;
        z-index: 10000;
        display: none;
        padding: 8px;
        background: rgba(13, 19, 51, .98);
        -webkit-backdrop-filter: blur(20px);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .6);
        overflow-y: auto;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }

    .cs-menu.cs-open {
        display: block;
        animation: cs-pop .18s cubic-bezier(.16, 1, .3, 1);
    }

    @keyframes cs-pop {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .cs-sheet-head {
        display: none;
    }

    .cs-option {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        touch-action: manipulation;
        padding: 11px 16px;
        border-radius: 10px;
        color: rgba(255, 255, 255, .8);
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background .18s ease, color .18s ease;
    }

    .cs-option:hover,
    .cs-option.cs-active {
        background: rgba(255, 109, 0, .15);
        color: #fff;
    }

    .cs-option.cs-selected {
        background: linear-gradient(135deg, rgba(255, 109, 0, .2), rgba(255, 171, 64, .2));
        border-left: 3px solid var(--cs-accent, #ff6d00);
        color: #ffab40;
    }

    .cs-option.cs-disabled {
        opacity: .35;
        cursor: not-allowed;
    }

    .cs-option .cs-tick {
        flex-shrink: 0;
        width: 16px;
        height: 16px;
        opacity: 0;
    }

    .cs-option.cs-selected .cs-tick {
        opacity: 1;
    }

    /* Group headings, for selects that use <optgroup>. */
    .cs-group {
        padding: 12px 16px 6px;
        color: rgba(255, 255, 255, .35);
        font-size: 9px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .2em;
    }

    .cs-backdrop {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        background: rgba(2, 6, 23, .6);
        -webkit-backdrop-filter: blur(2px);
        backdrop-filter: blur(2px);
    }

    /* ── Phones: the menu becomes a bottom sheet ──────────────────────────
       Pinned to the bottom of the viewport, so it can never be pushed off
       screen or cut off by a scroll container the way the old menus were. */
    @media (max-width: 640px) {
        .cs-backdrop.cs-open {
            display: block;
        }

        /* Pinned to the bottom of the viewport, edge to edge. !important so a
           desktop-width menu that is still carrying JS-set coordinates cannot
           end up half off-screen after a rotate or resize. */
        .cs-menu {
            top: auto !important;
            right: 0 !important;
            bottom: 0 !important;
            left: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            max-height: 70vh !important;
            border-radius: 24px 24px 0 0;
            border-bottom: 0;
            padding: 0 10px calc(14px + env(safe-area-inset-bottom, 0px));
        }

        .cs-menu.cs-open {
            animation: cs-sheet-up .22s cubic-bezier(.16, 1, .3, 1);
        }

        @keyframes cs-sheet-up {
            from { transform: translateY(100%); }
            to   { transform: translateY(0); }
        }

        .cs-sheet-head {
            position: sticky;
            top: 0;
            z-index: 1;
            display: block;
            padding: 14px 6px 10px;
            background: rgba(13, 19, 51, .98);
        }

        .cs-sheet-grip {
            width: 44px;
            height: 4px;
            margin: 0 auto 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .18);
        }

        .cs-sheet-title {
            display: block;
            color: rgba(255, 255, 255, .45);
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .2em;
            text-align: center;
        }

        /* Comfortable tap targets on a phone. */
        .cs-option {
            padding: 15px 16px;
            font-size: 15px;
        }
    }

    /* The page behind the sheet must not scroll under the finger. */
    body.cs-locked {
        overflow: hidden;
    }
</style>

<script>
    /*
    | Upgrades every <select> to the shared dropdown, including selects that
    | arrive later — the live-refresh helper (resources/js/echo.js) replaces
    | whole table sections, filters and all.
    */
    (function () {
        'use strict';

        var MOBILE = '(max-width: 640px)';
        var open = null;      // the one instance whose menu is showing
        var live = [];        // every instance, so detached ones can be swept
        var openedAt = 0;     // when the current menu opened (see settling())
        var lastWidth = window.innerWidth;

        /*
        | A tap on a phone is not one event. Browsers still emit a synthesised
        | "ghost" click up to ~350ms after the real one, at the same screen
        | point — which by then is covered by the backdrop we just opened. That
        | second click was closing the sheet the instant it appeared, which is
        | the blink: open, gone. Dismissals ignore anything that arrives while
        | the menu is still settling.
        */
        function settling() {
            return (window.performance ? performance.now() : Date.now()) - openedAt < 400;
        }

        function stamp() {
            openedAt = window.performance ? performance.now() : Date.now();
        }

        function isMobile() {
            return window.matchMedia(MOBILE).matches;
        }

        function svg(markup) {
            var span = document.createElement('span');
            span.innerHTML = markup;
            return span.firstElementChild;
        }

        /* The <label> tied to this select, used as the sheet title on phones. */
        function labelFor(select) {
            var text = '';
            if (select.id) {
                var tied = document.querySelector('label[for="' + CSS.escape(select.id) + '"]');
                if (tied) text = tied.textContent;
            }
            if (!text) {
                var wrapper = select.closest('label');
                if (wrapper) text = wrapper.textContent;
            }
            if (!text) {
                var prev = select.parentElement ? select.parentElement.previousElementSibling : null;
                if (prev && prev.tagName === 'LABEL') text = prev.textContent;
            }
            if (!text && select.getAttribute('aria-label')) text = select.getAttribute('aria-label');
            return text.replace(/\s+/g, ' ').trim().slice(0, 40) || 'Choose an option';
        }

        function enhance(select) {
            if (!(select instanceof HTMLSelectElement)) return;
            if (select.dataset.csReady) return;
            // Multi-selects and list boxes are a different control; leave them.
            if (select.multiple || select.size > 1) return;
            if (select.closest('[data-no-custom]')) return;

            select.dataset.csReady = '1';

            // Width only: the height comes from the classes copied below, which
            // beats measuring — several panel rules carry !important and would
            // override an inline height anyway.
            var width = Math.round(select.getBoundingClientRect().width);

            var wrap = document.createElement('div');
            wrap.className = 'cs-wrap';
            // Full-width selects keep filling their column; the rest keep their
            // own footprint so inline rows are not stretched.
            if (!/(^|\s)w-full(\s|$)/.test(select.className)) {
                wrap.classList.add('cs-auto');
                if (width) wrap.style.minWidth = Math.max(160, width) + 'px';
            }

            select.parentNode.insertBefore(wrap, select);
            wrap.appendChild(select);
            select.classList.add('cs-native');
            select.setAttribute('tabindex', '-1');
            select.setAttribute('aria-hidden', 'true');

            var trigger = document.createElement('button');
            trigger.type = 'button';
            // The select's own utility classes carry the field's height,
            // padding, radius, border, background and type over to the trigger.
            var inherited = select.className.replace(/(^|\s)cs-native(\s|$)/g, ' ').trim();
            trigger.className = 'cs-trigger ' + (inherited || 'cs-bare');
            trigger.setAttribute('aria-haspopup', 'listbox');
            trigger.setAttribute('aria-expanded', 'false');
            trigger.disabled = select.disabled;

            var value = document.createElement('span');
            value.className = 'cs-value';
            trigger.appendChild(value);
            trigger.appendChild(svg('<svg class="cs-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>'));
            wrap.appendChild(trigger);

            var menu = document.createElement('div');
            menu.className = 'cs-menu';
            menu.setAttribute('role', 'listbox');
            // On <body> rather than inside the wrap: a fixed child of a
            // transformed ancestor is positioned against that ancestor, not the
            // viewport, and several cards on this site animate with transforms.
            document.body.appendChild(menu);

            var backdrop = document.createElement('div');
            backdrop.className = 'cs-backdrop';
            document.body.appendChild(backdrop);

            var instance = {
                select: select, wrap: wrap, trigger: trigger, menu: menu,
                backdrop: backdrop, options: [], active: -1,
            };

            /* Trigger text follows whatever the native select currently holds. */
            function sync() {
                var picked = select.options[select.selectedIndex];
                value.textContent = picked ? picked.textContent.trim() : '';
                // An empty-valued option is a placeholder ("All Shops", "Pick a
                // shop first"), not a real choice.
                value.classList.toggle('cs-placeholder', !picked || picked.value === '');
                trigger.disabled = select.disabled;
            }

            /* Rebuilt on every open, so options the server swapped in are current. */
            function build() {
                menu.innerHTML = '';
                instance.options = [];
                instance.active = -1;

                if (isMobile()) {
                    var head = document.createElement('div');
                    head.className = 'cs-sheet-head';
                    head.innerHTML = '<span class="cs-sheet-grip"></span>';
                    var title = document.createElement('span');
                    title.className = 'cs-sheet-title';
                    title.textContent = labelFor(select);
                    head.appendChild(title);
                    menu.appendChild(head);
                }

                Array.prototype.forEach.call(select.querySelectorAll('optgroup, option'), function (node) {
                    if (node.tagName === 'OPTGROUP') {
                        var group = document.createElement('div');
                        group.className = 'cs-group';
                        group.textContent = node.label;
                        menu.appendChild(group);
                        return;
                    }

                    var item = document.createElement('div');
                    item.className = 'cs-option';
                    item.setAttribute('role', 'option');
                    if (node.disabled) item.classList.add('cs-disabled');
                    if (node.selected) {
                        item.classList.add('cs-selected');
                        instance.active = instance.options.length;
                    }
                    item.setAttribute('aria-selected', node.selected ? 'true' : 'false');

                    var text = document.createElement('span');
                    text.textContent = node.textContent.trim();
                    item.appendChild(text);
                    item.appendChild(svg('<svg class="cs-tick" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>'));

                    item.addEventListener('click', function () {
                        if (node.disabled) return;
                        choose(node);
                    });

                    menu.appendChild(item);
                    instance.options.push({ item: item, option: node });
                });
            }

            /* Desktop: hang the menu off the trigger, flipping up when the space
               below runs out. Mobile keeps the CSS bottom-sheet placement. */
            function place() {
                if (isMobile()) {
                    menu.style.cssText = '';
                    return;
                }

                var rect = trigger.getBoundingClientRect();
                var below = window.innerHeight - rect.bottom - 16;
                var above = rect.top - 16;
                var flip = below < 220 && above > below;
                var room = Math.max(160, Math.min(320, flip ? above : below));

                menu.style.width = Math.max(rect.width, 200) + 'px';
                menu.style.left = Math.max(8, Math.min(rect.left, window.innerWidth - Math.max(rect.width, 200) - 8)) + 'px';
                menu.style.maxHeight = room + 'px';

                if (flip) {
                    menu.style.top = 'auto';
                    menu.style.bottom = (window.innerHeight - rect.top + 8) + 'px';
                } else {
                    menu.style.bottom = 'auto';
                    menu.style.top = (rect.bottom + 8) + 'px';
                }
            }

            function show() {
                if (trigger.disabled) return;
                if (open && open !== instance) open.close();

                sync();
                build();
                place();

                wrap.classList.add('cs-open');
                menu.classList.add('cs-open');
                backdrop.classList.add('cs-open');
                trigger.setAttribute('aria-expanded', 'true');
                if (isMobile()) document.body.classList.add('cs-locked');

                open = instance;
                stamp();

                var current = instance.options[instance.active];
                if (current) current.item.scrollIntoView({ block: 'nearest' });
            }

            function close() {
                wrap.classList.remove('cs-open');
                menu.classList.remove('cs-open');
                backdrop.classList.remove('cs-open');
                trigger.setAttribute('aria-expanded', 'false');
                document.body.classList.remove('cs-locked');
                if (open === instance) open = null;
            }

            /* Writing through the native select is what keeps every existing
               binding alive: x-model, @change, onchange="this.form.submit()". */
            function choose(option) {
                var changed = select.value !== option.value;
                select.value = option.value;
                sync();
                close();
                trigger.focus({ preventScroll: true });
                if (!changed) return;
                select.dispatchEvent(new Event('input', { bubbles: true }));
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }

            function highlight(index) {
                var list = instance.options;
                if (!list.length) return;
                var next = Math.max(0, Math.min(index, list.length - 1));
                list.forEach(function (entry) { entry.item.classList.remove('cs-active'); });
                list[next].item.classList.add('cs-active');
                list[next].item.scrollIntoView({ block: 'nearest' });
                instance.active = next;
            }

            instance.close = close;
            live.push(instance);

            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                // Same tap arriving twice must not toggle twice.
                if (wrap.classList.contains('cs-open')) {
                    if (settling()) return;
                    close();
                } else {
                    show();
                }
            });

            trigger.addEventListener('keydown', function (event) {
                var opened = wrap.classList.contains('cs-open');

                if (event.key === 'Escape' && opened) { event.preventDefault(); close(); return; }
                if (!opened && (event.key === 'ArrowDown' || event.key === 'ArrowUp' || event.key === 'Enter' || event.key === ' ')) {
                    event.preventDefault();
                    show();
                    return;
                }
                if (!opened) return;

                if (event.key === 'ArrowDown')  { event.preventDefault(); highlight(instance.active + 1); }
                else if (event.key === 'ArrowUp') { event.preventDefault(); highlight(instance.active - 1); }
                else if (event.key === 'Home')  { event.preventDefault(); highlight(0); }
                else if (event.key === 'End')   { event.preventDefault(); highlight(instance.options.length - 1); }
                else if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    var entry = instance.options[instance.active];
                    if (entry && !entry.option.disabled) choose(entry.option);
                }
            });

            backdrop.addEventListener('click', function () {
                if (settling()) return;   // the tap that opened the sheet
                close();
            });
            menu.addEventListener('click', function (event) { event.stopPropagation(); });

            // Something else moved the value (Alpine, a reset, the back button).
            select.addEventListener('change', sync);
            new MutationObserver(sync).observe(select, {
                attributes: true,
                attributeFilter: ['disabled'],
            });

            sync();
            // Alpine writes x-model values after its own init, which can land
            // after this script runs.
            setTimeout(sync, 0);
            document.addEventListener('alpine:initialized', sync, { once: true });
        }

        /*
        | The live-refresh helper swaps whole sections of the page out. Their
        | menus live on <body>, so they would survive the section that owned
        | them; drop the ones whose trigger is gone.
        */
        function sweep() {
            live = live.filter(function (instance) {
                if (document.contains(instance.wrap)) return true;
                if (open === instance) { instance.close(); }
                instance.menu.remove();
                instance.backdrop.remove();
                return false;
            });
        }

        function scan(root) {
            var scope = root && root.querySelectorAll ? root : document;
            scope.querySelectorAll('select').forEach(enhance);
            if (root instanceof HTMLSelectElement) enhance(root);
        }

        // Outside click / scroll / resize all dismiss the open menu. Scroll
        // closes rather than re-positions: the trigger may have scrolled out of
        // sight entirely, and a menu left hanging over the page reads as a bug.
        document.addEventListener('click', function () {
            if (open && !settling()) open.close();
        });

        /*
        | Only a real width change counts. Phones fire resize when the address
        | bar collapses and when the on-screen keyboard opens or closes — and a
        | visitor tapping this field straight after typing in the one above it
        | dismisses the keyboard, which was firing resize and closing the sheet
        | on the spot. The sheet is pinned to the viewport, so a height change
        | cannot misplace it anyway.
        */
        window.addEventListener('resize', function () {
            if (window.innerWidth === lastWidth) return;
            lastWidth = window.innerWidth;
            if (open) open.close();
        });

        window.addEventListener('scroll', function () {
            if (open && !isMobile() && !settling()) open.close();
        }, true);
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && open) open.close();
        });

        new MutationObserver(function (records) {
            var removed = false;
            records.forEach(function (record) {
                record.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1) scan(node);
                });
                if (record.removedNodes.length) removed = true;
            });
            if (removed) sweep();
        }).observe(document.documentElement, { childList: true, subtree: true });

        document.readyState === 'loading'
            ? document.addEventListener('DOMContentLoaded', function () { scan(document); })
            : scan(document);
    })();
</script>
