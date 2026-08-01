<x-app-layout page-title="Book Verified Experts | Professional Appointments">

    {{-- ================================================================
    GLOBAL PAGE STYLES — inlined here so they take priority over
    any compiled Tailwind / app.css rules that were fighting us.
    ================================================================ --}}
    <style>
        /* ── Background ──────────────────────────────────────────────── */
        .bv-page {
            background: linear-gradient(180deg, #0a0f2c 0%, #0d1333 100%);
            min-height: 100vh;
        }

        /* ── Hero ────────────────────────────────────────────────────── */
        .bv-hero {
            position: relative;
            padding: 120px 24px 80px;
            overflow: hidden;
        }

        .bv-hero-glow-1 {
            position: absolute;
            top: 0;
            left: 25%;
            width: 500px;
            height: 500px;
            background: rgba(255, 109, 0, .08);
            border-radius: 50%;
            filter: blur(120px);
            pointer-events: none;
        }

        .bv-hero-glow-2 {
            position: absolute;
            bottom: 0;
            right: 25%;
            width: 600px;
            height: 600px;
            background: rgba(255, 109, 0, .04);
            border-radius: 50%;
            filter: blur(150px);
            pointer-events: none;
        }

        /* ── Search Bar ──────────────────────────────────────────────── */
        .bv-search-wrap {
            max-width: 940px;
            margin: 0 auto 40px;
        }

        .bv-search-bar {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 16px;
            backdrop-filter: blur(22px);
            padding: 8px;
            gap: 0;
            position: relative;
            z-index: 90;
        }

        @media(max-width: 600px) {
            .bv-search-bar {
                flex-direction: column;
                gap: 0;
                padding: 12px;
                background: rgba(255, 255, 255, 0.08);
                border-radius: 24px;
            }
            .bv-search-form {
                flex-direction: column;
                width: 100%;
                gap: 10px;
            }
             .bv-hero {
                padding: 120px 24px 20px;
            }
        }

        .bv-search-form {
            display: flex;
            width: 100%;
            align-items: center;
        }

        .bv-search-field {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            padding: 12px 20px;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        @media(max-width: 600px) {
            .bv-search-field {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                padding: 10px 8px;
            }
        }

        .bv-search-field:last-of-type {
            border-right: none;
        }

        @media(max-width: 600px) {
            .bv-search-field:last-of-type {
                border-bottom: none;
            }
        }

        .bv-search-field svg {
            flex-shrink: 0;
            color: rgba(255, 140, 66, .8);
        }

        .bv-search-input {
            background: transparent;
            border: none;
            outline: none;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            width: 100%;
            line-height: 1.4;
        }

        .bv-search-input::placeholder {
            color: rgba(255, 255, 255, .38);
            font-weight: 400;
            font-size: 14px;
        }

        .bv-search-input:focus {
            outline: none;
        }

        .bv-search-caret {
            flex-shrink: 0;
            opacity: .4;
            transition: transform 0.3s ease;
        }

        .custom-dropdown-wrap.open .bv-search-caret {
            transform: translateY(-50%) rotate(180deg) !important;
        }

        .custom-dropdown-menu {
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            width: max-content;
            min-width: 100%;
            background: rgba(13, 19, 51, 0.98);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 100;
        }

        .custom-dropdown-wrap.open .custom-dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .custom-dropdown-item {
            padding: 10px 16px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s, color 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
        }

        .custom-dropdown-item:hover {
            background: rgba(255, 109, 0, 0.15);
            color: #fff;
            transform: translateX(4px);
        }

        .custom-dropdown-item.selected {
            background: linear-gradient(135deg, rgba(255, 109, 0, 0.2), rgba(255, 171, 64, 0.2));
            border-left: 3px solid #ff6d00;
            color: #ffab40;
            border-radius: 8px;
        }

        .custom-dropdown-trigger {
            display: flex;
            align-items: center;
            width: 100%;
            height: 100%;
            cursor: pointer;
            user-select: none;
        }
        
        .custom-dropdown-label {
            font-size: 15px;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding-right: 28px;
        }

        /* ── Near Me (use-my-location) ─────────────────────────────────── */
        .bv-nearme {
            gap: 10px;
            border-radius: 12px;
            background: rgba(255, 109, 0, 0.12);
            box-shadow: inset 0 0 0 1px rgba(255, 140, 66, 0.30);
            transition: background .25s ease, box-shadow .25s ease;
        }

        .bv-nearme:hover {
            background: rgba(255, 109, 0, 0.20);
            box-shadow: inset 0 0 0 1px rgba(255, 140, 66, 0.55);
        }

        .bv-nearme:active {
            background: rgba(255, 109, 0, 0.26);
        }

        .bv-nearme.is-locating {
            background: rgba(255, 109, 0, 0.18);
        }

        .bv-nearme-arrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            margin-left: auto;
            flex-shrink: 0;
            border-radius: 50%;
            color: #fff;
            background: rgba(255, 140, 66, 0.14);
            transition: transform .25s ease, background .25s ease;
        }

        .bv-nearme-arrow svg {
            color: #fff;
        }

        .bv-nearme:hover .bv-nearme-arrow {
            transform: translateX(3px);
            background: rgba(255, 140, 66, 0.24);
        }

        .bv-nearme-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            flex-shrink: 0;
            background: linear-gradient(135deg, rgba(255, 109, 0, 0.20), rgba(255, 171, 64, 0.18));
            box-shadow: inset 0 0 0 1px rgba(255, 140, 66, 0.35);
        }

        .bv-nearme-icon svg {
            color: #ff8c42;
        }

        .bv-nearme-label {
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
            letter-spacing: .01em;
        }

        .bv-search-btn {
            flex-shrink: 0;
            width: auto;
            margin-left: 8px;
            background: linear-gradient(135deg, #ff6d00, #ffab40);
            color: #fff;
            font-weight: 800;
            font-size: 14px;
            border: none;
            border-radius: 12px;
            padding: 16px 32px;
            cursor: pointer;
            transition: all .3s ease;
            letter-spacing: .04em;
            transition: filter .2s, transform .2s;
            box-shadow: 0 6px 20px rgba(255, 109, 0, .4);
        }

        .bv-reset-btn {
            flex-shrink: 0;
            width: auto;
            margin-left: 8px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #fff;
            font-weight: 800;
            font-size: 14px;
            border-radius: 12px;
            padding: 16px 24px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all .3s ease;
            letter-spacing: .04em;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .bv-reset-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: scale(1.02);
            color: #fff;
        }

        @media(max-width: 600px) {
            .bv-reset-btn {
                width: 100% !important;
                margin-left: 0 !important;
                margin-top: 8px !important;
                padding: 18px !important;
                font-size: 16px !important;
                border-radius: 14px;
            }
            .bv-search-wrap {
                margin: 0 10px 40px;
                width: calc(100% - 20px);
            }
            .bv-search-bar {
                flex-direction: column;
                gap: 12px;
                padding: 16px;
                background: rgba(255, 255, 255, 0.08);
                border-radius: 20px;
            }
            .bv-search-field {
                width: 100%;
                border-right: none !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                padding: 12px 10px;
            }
            .bv-search-btn {
                width: 100% !important;
                margin-left: 0 !important;
                margin-top: 8px !important;
                padding: 18px !important;
                font-size: 16px !important;
                border-radius: 14px;
            }
        }

        /* Icon-only submit that lives inside the text field (mobile only) */
        .bv-search-icon-btn {
            display: none;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            color: #fff;
            background: linear-gradient(135deg, #ff6d00, #ffab40);
            box-shadow: 0 4px 14px rgba(255, 109, 0, .35);
        }

        .bv-search-icon-btn svg {
            color: #fff;
        }

        /* ── Mobile search: Near Me on top, single input + search icon ──── */
        @media(max-width: 600px) {
            .bv-search-form {
                gap: 12px;
            }

            /* Near Me lives in the header (pin icon beside the hamburger) on mobile */
            .bv-search-form .bv-nearme {
                display: none !important;
            }

            /* No category dropdown on mobile */
            .bv-search-form .custom-dropdown-wrap {
                display: none !important;
            }

            /* Just the input + the search icon on its right */
            .bv-search-field-text {
                order: 0;
                border-bottom: none !important;
                gap: 10px;
                padding: 6px 8px 6px 16px;
                border-radius: 14px;
                background: rgba(255, 255, 255, 0.06);
                box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.10);
            }

            .bv-search-field-text .bv-search-user-icon {
                display: none;
            }

            .bv-search-icon-btn {
                display: inline-flex;
            }

            /* The full-width text button is replaced by the icon */
            .bv-search-btn {
                display: none !important;
            }

            .bv-reset-btn {
                order: 1;
            }
        }

        .bv-search-btn:hover {
            filter: brightness(1.1);
            transform: scale(1.02);
        }

        /* ── Category Pills ──────────────────────────────────────────── */
        .bv-cat-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 32px;
        }

        .bv-cat-scroll-wrap {
            position: relative;
            margin-top: 32px;
        }

        .bv-cat-scroll-btn {
            display: none;
        }

        @media(max-width: 600px) {
            .bv-cat-scroll-wrap {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .bv-cat-scroll-btn {
                display: flex;
                flex-shrink: 0;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                border-radius: 50%;
                background: rgba(255,255,255,0.12);
                border: 1.5px solid rgba(255,255,255,0.2);
                color: #fff;
                cursor: pointer;
                transition: background .2s;
                z-index: 2;
            }
            .bv-cat-scroll-btn:hover {
                background: rgba(255,255,255,0.22);
            }
            .bv-cat-row {
                flex-wrap: nowrap;
                justify-content: flex-start;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
                padding-bottom: 10px;
                margin-top: 0;
            }
            .bv-cat-row::-webkit-scrollbar {
                display: none;
            }
        }

        .bv-cat-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.04);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 999px;
            padding: 7px 16px 7px 7px;
            text-decoration: none;
            transition: background .2s, border-color .2s, box-shadow .2s, transform .2s;
            cursor: pointer;
        }

        .bv-cat-pill:hover {
            background: rgba(var(--cr), var(--cg), var(--cb), 0.15);
            border-color: rgba(var(--cr), var(--cg), var(--cb), 0.5);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(var(--cr), var(--cg), var(--cb), 0.25);
        }

        .bv-cat-pill.active {
            background: rgba(var(--cr), var(--cg), var(--cb), 0.18);
            border-color: rgba(var(--cr), var(--cg), var(--cb), 0.75);
            box-shadow: 0 0 0 3px rgba(var(--cr), var(--cg), var(--cb), 0.18), 0 6px 20px rgba(var(--cr), var(--cg), var(--cb), .3);
        }

        .bv-cat-icon {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .35);
        }

        .bv-cat-text {
            display: flex;
            flex-direction: column;
        }

        .bv-cat-name {
            font-size: 13px;
            font-weight: 800;
            color: #fff;
            line-height: 1;
        }

        .bv-cat-sub {
            font-size: 10px;
            line-height: 1.3;
            margin-top: 2px;
            color: rgba(var(--cr), var(--cg), var(--cb), 0.9);
        }
        /* Desktop container visible on desktop, hidden on mobile */
        .bv-cat-desktop-wrap {
            display: block;
        }
        
        /* Mobile container hidden on desktop, visible on mobile */
        .bv-cat-mobile-container {
            display: none;
        }

        @media(max-width: 600px) {
            .bv-cat-desktop-wrap {
                display: none !important;
            }
            .bv-cat-mobile-container {
                display: block !important;
                margin-top: 24px;
            }
            
            .bv-cat-mobile-wrap {
                display: flex;
                align-items: stretch;
                gap: 0;
                padding: 0;
            }

            /* Arrows removed on mobile — categories scroll 3-up */
            .bv-cat-mobile-btn {
                display: none !important;
            }

            .bv-cat-mobile-row {
                display: flex;
                align-items: stretch;
                gap: 10px;
                overflow-x: auto;
                scroll-snap-type: x proximity;
                scroll-behavior: smooth;
                scrollbar-width: none;
                flex: 1 1 0;
                min-width: 0;
                padding: 8px 2px;
            }

            .bv-cat-mobile-row::-webkit-scrollbar {
                display: none;
            }

            .bv-cat-mobile-pill {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 8px;
                background: rgba(255, 255, 255, 0.05);
                border: 2px solid rgba(255, 255, 255, 0.1);
                border-radius: 18px;
                padding: 14px 6px;
                text-decoration: none;
                flex: 0 0 calc((100% - 20px) / 3);
                transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
                box-shadow: 0 6px 18px rgba(0, 0, 0, 0.28);
                scroll-snap-align: start;
                box-sizing: border-box;
            }

            .bv-cat-mobile-pill.active {
                background: rgba(var(--cr), var(--cg), var(--cb), 0.15);
                border-color: rgba(var(--cr), var(--cg), var(--cb), 0.75);
                box-shadow: 0 0 0 3px rgba(var(--cr), var(--cg), var(--cb), 0.15), 0 8px 25px rgba(var(--cr), var(--cg), var(--cb), 0.35);
            }

            .bv-cat-mobile-icon {
                width: 42px;
                height: 42px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
                flex-shrink: 0;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);
            }

            .bv-cat-mobile-text {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                width: 100%;
            }

            .bv-cat-mobile-name {
                font-size: 12px;
                font-weight: 800;
                color: #fff;
                line-height: 1.15;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 100%;
            }

            .bv-cat-mobile-sub {
                font-size: 9px;
                line-height: 1.25;
                margin-top: 3px;
                font-weight: 600;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 100%;
            }

            /* Pagination dots not needed with 3-up scroll */
            .bv-cat-mobile-dots {
                display: none !important;
            }
        }

        /* ── Stats ───────────────────────────────────────────────────── */
        .bv-stats {
            display: flex;
            justify-content: center;
            gap: 60px;
            margin-top: 56px;
            padding-top: 40px;
            border-top: 1px solid rgba(255, 255, 255, .07);
            text-align: center;
        }

        @media(max-width: 768px) {
            .bv-stats {
                gap: 20px;
                flex-wrap: wrap;
                margin-top: 40px;
                padding-top: 30px;
            }
        }

        /* Stats live in the hero on desktop; on mobile they move below the steps */
        .bv-stats-mobile {
            display: none;
        }

        @media(max-width: 600px) {
            .bv-stats-desktop {
                display: none !important;
            }
            .bv-stats-mobile {
                display: flex !important;
                margin-top: 48px;
            }
        }

        .bv-stat-num {
            font-size: 2.4rem;
            font-weight: 900;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        @media(max-width: 600px) {
            .bv-stat-num {
                font-size: 1.4rem;
            }
        }

        .bv-stat-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .18em;
            color: rgba(255, 255, 255, .3);
            margin-top: 4px;
        }

        /* ── Section header ──────────────────────────────────────────── */
        .bv-section {
            padding: 80px 24px;
        }

        .bv-section-title {
            font-size: clamp(1.8rem, 5vw, 2.4rem);
            font-weight: 900;
            color: #fff;
            text-align: center;
            margin-bottom: 8px;
        }

        .bv-section-accent {
            color: transparent;
            background: linear-gradient(135deg, #ff8c42, #ffab40);
            -webkit-background-clip: text;
            background-clip: text;
            font-style: italic;
        }

        .bv-section-bar {
            width: 72px;
            height: 4px;
            background: linear-gradient(90deg, #ff6d00, #ffab40);
            border-radius: 4px;
            margin: 12px auto 8px;
        }

        .bv-section-sub {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .35em;
            color: rgba(255, 255, 255, .25);
            text-align: center;
        }

        /* ── Vendor Grid ─────────────────────────────────────────────── */
        .bv-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
            max-width: 1100px;
            margin: 48px auto 0;
        }

        @media(max-width:992px) {
            .bv-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width:600px) {
            .bv-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
                margin-top: 26px;
            }
            .bv-section {
                padding: 30px 16px;
            }

            /* ── Compact 2-up recommended cards (mobile only) ──
               Two per row rather than three, so every tile gets roughly half
               again the width it had; the type scale below is stepped up to
               match. !important needed: the base .bv-card-sports rules are
               declared later in the stylesheet, so they'd otherwise win the
               cascade. */
            .bv-card-sports {
                height: 210px !important;
                border-width: 2px !important;
                border-radius: 14px !important;
                box-shadow: 0 4px 16px rgba(var(--cr), var(--cg), var(--cb), 0.3) !important;
            }
            .bv-card-sports-overlay {
                padding: 8px !important;
                background: linear-gradient(to top, rgba(0, 0, 0, 0.96) 0%, rgba(0, 0, 0, 0.5) 45%, rgba(0, 0, 0, 0) 100%) !important;
            }
            .bv-rc-rating {
                top: 7px !important;
                right: 7px !important;
                padding: 3px 7px !important;
                font-size: 10px !important;
                gap: 3px !important;
            }
            .bv-rc-badge {
                font-size: 7px !important;
                padding: 3px 6px !important;
                margin-bottom: 6px !important;
                border-radius: 5px !important;
            }
            .bv-rc-name {
                font-size: 14px !important;
                margin: 0 0 6px !important;
                line-height: 1.15 !important;
                display: -webkit-box !important;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            .bv-rc-name svg {
                display: none !important;
            }
            .bv-rc-loc {
                margin-bottom: 10px !important;
                font-size: 11px !important;
            }
            /* Still a narrow tile even at 2-up (~150px of usable width on a
               390px phone): the address plus pin plus distance chip will not
               sit on one line, so the address stays out and the distance —
               the more useful of the two — keeps the row. */
            .bv-rc-loc-pin,
            .bv-rc-addr {
                display: none !important;
            }
            .bv-rc-dist {
                margin-left: 0 !important;
                color: #fff !important;
                font-size: 10px !important;
                background: rgba(0, 0, 0, 0.55);
                padding: 3px 7px;
                border-radius: 6px;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.55);
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.9);
            }
            .bv-rc-pricebar {
                padding: 9px 10px !important;
                border-radius: 11px !important;
            }
            .bv-rc-price-label {
                font-size: 9px !important;
            }
            .bv-rc-price {
                font-size: 11px !important;
            }
            /* Live-queue / arrow column doesn't fit the narrow tile */
            .bv-rc-status {
                display: none !important;
            }
        }

        /* ── Vendor Card ─────────────────────────────────────────────── */
        .bv-card {
            position: relative;
            border-radius: 18px;
            overflow: hidden;
            background: rgba(14, 18, 40, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: inherit;
            transition: transform .3s cubic-bezier(.16, 1, .3, 1), box-shadow .3s;
        }

        /* Gradient border glow on hover */
        .bv-card::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 18px;
            padding: 1px;
            background: linear-gradient(135deg, var(--c1, #2979ff), var(--c2, #00b0ff));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity .3s;
        }

        .bv-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 60px rgba(0, 0, 0, .5);
        }

        .bv-card:hover::after {
            opacity: 1;
        }

        .bv-card-img {
            position: relative;
        }

        .bv-card-img img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .bv-card-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: linear-gradient(135deg, var(--c1, #2979ff), var(--c2, #00b0ff));
            color: #fff;
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .07em;
            padding: 4px 10px;
            border-radius: 6px;
        }

        .bv-card-rating {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, .15);
            border-radius: 999px;
            padding: 4px 10px;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 800;
            color: #fff;
        }

        .bv-card-body {
            padding: 16px 16px 52px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .bv-card-name {
            font-size: 17px;
            font-weight: 800;
            color: #fff;
            margin: 0 0 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .bv-card-verified {
            color: #38bdf8;
            flex-shrink: 0;
        }

        .bv-card-loc {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: rgba(255, 255, 255, .4);
            margin-bottom: 16px;
            overflow: hidden;
        }

        .bv-card-loc span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .bv-card-divider {
            height: 1px;
            background: rgba(255, 255, 255, .07);
            margin-bottom: 14px;
        }

        .bv-card-price-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: rgba(255, 255, 255, .35);
            margin-bottom: 2px;
        }

        .bv-card-price {
            font-size: 20px;
            font-weight: 900;
            color: #fff;
        }

        .bv-card-cta {
            position: absolute;
            bottom: 14px;
            right: 14px;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #ff6d00, #ffab40);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-decoration: none;
            border: none;
            transition: transform .2s;
        }

        .bv-card-cta:hover {
            transform: scale(1.1);
        }

        /* ── Category-Specific Cards ─────────────────────────────────── */
        /* Global Card Base */
        .bv-dynamic-card {
            position: relative;
            text-decoration: none;
            color: inherit;
            transition: transform .4s cubic-bezier(.16, 1, .3, 1), box-shadow .4s cubic-bezier(.16, 1, .3, 1), border-color .4s;
            display: flex;
            flex-direction: column;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 20px;
        }

        /* 1. Barber Card */
        .bv-card-barber {
            height: 380px;
            border: 1px solid rgba(var(--cr), var(--cg), var(--cb), 0.25);
            background: rgba(10, 15, 30, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            overflow: hidden;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.05), 0 8px 30px rgba(0, 0, 0, 0.4);
        }

        .bv-card-barber:hover {
            transform: translateY(-8px) scale(1.02);
            border-color: rgba(var(--cr), var(--cg), var(--cb), 0.6);
            box-shadow: inset 0 0 20px rgba(var(--cr), var(--cg), var(--cb), 0.15), 0 15px 40px rgba(var(--cr), var(--cg), var(--cb), 0.3);
        }

        .bv-card-barber-img-wrap {
            height: 80%;
            position: relative;
            overflow: hidden;
            border-radius: 19px 19px 0 0;
        }

        .bv-card-barber-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(.16, 1, .3, 1), filter 0.6s;
        }

        .bv-card-barber:hover .bv-card-barber-img-wrap img {
            transform: scale(1.08);
            filter: brightness(0.7);
        }

        .bv-card-barber-hover-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.95) 0%, rgba(0, 0, 0, 0.4) 50%, transparent 100%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 24px;
        }

        /* Optional desc reveals on hover */
        .bv-card-barber-desc {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            margin: 0;
            transition: max-height 0.3s ease, opacity 0.3s ease, margin 0.3s ease;
        }

        .bv-card-barber:hover .bv-card-barber-desc {
            max-height: 40px;
            opacity: 1;
            margin-top: 8px;
        }

        .bv-card-barber-price-bar {
            height: 20%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            background: rgba(14, 18, 40, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            z-index: 2;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* 2. Consultant Card */
        .bv-card-consultant {
            height: 380px;
            border: 1px solid rgba(var(--cr), var(--cg), var(--cb), 0.25);
            background: rgba(14, 18, 40, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
        }

        .bv-card-consultant:hover {
            transform: translateY(-8px) scale(1.02);
            border-color: rgba(var(--cr), var(--cg), var(--cb), 0.6);
            box-shadow: 0 15px 40px rgba(var(--cr), var(--cg), var(--cb), 0.3);
        }

        .bv-card-consultant-img-wrap {
            height: 70%;
            position: relative;
            padding: 20px 20px 10px;
        }

        .bv-card-consultant-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 24px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
            transition: transform 0.6s cubic-bezier(.16, 1, .3, 1), filter 0.6s;
        }

        .bv-card-consultant:hover .bv-card-consultant-img-wrap img {
            transform: scale(1.05);
            filter: brightness(0.8);
        }

        .bv-card-consultant-overlay {
            position: absolute;
            inset: 20px 20px 10px;
            border-radius: 24px;
            overflow: hidden;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.95) 0%, rgba(0, 0, 0, 0) 60%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 20px;
        }

        .bv-card-consultant-price-bar {
            height: 30%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            background: rgba(14, 18, 40, 0.85);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* 3. Sports Card */
        .bv-card-sports {
            height: 380px;
            padding: 0;
            overflow: hidden;
            border: 4px solid rgba(var(--cr), var(--cg), var(--cb), 0.6);
            box-shadow: 0 0 50px rgba(var(--cr), var(--cg), var(--cb), 0.4);
            background: #000;
        }

        .bv-card-sports:hover {
            transform: translateY(-8px);
            border-color: rgba(var(--cr), var(--cg), var(--cb), 0.8);
            box-shadow: 0 0 60px rgba(var(--cr), var(--cg), var(--cb), 0.5);
        }

        .bv-card-sports img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transition: transform 0.6s cubic-bezier(.16, 1, .3, 1), opacity 0.4s;
            opacity: 0.8;
        }

        .bv-card-sports:hover img {
            transform: scale(1.08);
            opacity: 1;
        }

        .bv-card-sports-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.95) 0%, rgba(0, 0, 0, 0.4) 40%, rgba(0, 0, 0, 0) 100%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 24px;
            z-index: 1;
        }

        /* 4. Doctor Card */
        .bv-card-doctor {
            height: 380px;
            background: linear-gradient(135deg, rgba(20, 30, 50, 0.9) 0%, rgba(10, 15, 30, 0.95) 100%);
            color: #f8fafc;
            border: 1px solid rgba(var(--cr), var(--cg), var(--cb), 0.3);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
        }

        .bv-card-doctor:hover {
            transform: translateY(-6px);
            border-color: var(--c1);
            box-shadow: 0 12px 30px rgba(var(--cr), var(--cg), var(--cb), 0.25);
        }

        .bv-card-doctor-img-wrap {
            height: 70%;
            position: relative;
            padding: 20px 20px 10px;
        }

        .bv-card-doctor-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 24px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
            transition: transform 0.6s cubic-bezier(.16, 1, .3, 1), filter 0.6s;
        }

        .bv-card-doctor:hover .bv-card-doctor-img-wrap img {
            transform: scale(1.05);
            filter: brightness(0.85);
        }

        .bv-card-doctor-overlay {
            position: absolute;
            inset: 20px 20px 10px;
            border-radius: 24px;
            overflow: hidden;
            background: linear-gradient(to top, rgba(10, 15, 30, 0.95) 0%, rgba(0, 0, 0, 0) 60%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 20px;
        }

        .bv-card-doctor-price-bar {
            height: 30%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* 5. Training Card */
        .bv-card-training {
            height: 380px;
            border: 1px solid rgba(var(--cr), var(--cg), var(--cb), 0.25);
            background: rgba(14, 18, 40, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
        }

        .bv-card-training:hover {
            transform: translateY(-8px) scale(1.02);
            border-color: rgba(var(--cr), var(--cg), var(--cb), 0.6);
            box-shadow: 0 15px 40px rgba(var(--cr), var(--cg), var(--cb), 0.3);
        }

        .bv-card-training-img-wrap {
            height: 70%;
            position: relative;
            padding: 16px 16px 10px;
        }

        .bv-card-training-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
            transition: transform 0.6s cubic-bezier(.16, 1, .3, 1), filter 0.6s;
        }

        .bv-card-training:hover .bv-card-training-img-wrap img {
            transform: scale(1.05);
            filter: brightness(0.85);
        }

        .bv-card-training-overlay {
            position: absolute;
            inset: 16px 16px 10px;
            border-radius: 20px;
            overflow: hidden;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.95) 0%, rgba(0, 0, 0, 0) 60%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 20px;
        }

        .bv-card-training-price-bar {
            height: 30%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            background: rgba(14, 18, 40, 0.85);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* 6. General / Vendor */
        .bv-card-general {
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            align-items: center;
            text-align: center;
            background: rgba(14, 18, 40, 0.85);
        }

        .bv-card-general:hover {
            transform: translateY(-5px);
            border-color: var(--c1);
            box-shadow: 0 10px 30px rgba(var(--cr), var(--cg), var(--cb), 0.2);
        }

        .bv-card-general img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            margin-bottom: 16px;
            border-radius: 10px;
        }

        /* ── Grayscale for closed vendors ────────────────────────────── */
        .bv-closed {
            filter: grayscale(1);
            opacity: .55;
        }

        /* ── Steps Section ───────────────────────────────────────────── */
        .bv-steps-section {
            padding: 80px 24px 100px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(180deg, rgba(10, 15, 44, 0.3) 0%, rgba(8, 12, 35, 0.8) 100%);
        }

        .bv-steps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            max-width: 860px;
            margin: 0 auto;
            position: relative;
        }

        .bv-steps-grid::before {
            content: "";
            position: absolute;
            top: 40px;
            left: 16.66%;
            right: 16.66%;
            height: 40px;
            border-bottom: 2px dashed rgba(255, 255, 255, .2);
            border-radius: 0 0 50% 50% / 0 0 100% 100%;
            z-index: 0;
        }

        @media(max-width:768px) {
            .bv-steps-grid {
                grid-template-columns: 1fr;
                gap: 60px; /* Increase gap to give room for the curved connecting line */
                padding: 0 10px;
            }

            .bv-steps-grid::before {
                display: none;
            }

            /* Draw the line ONLY in the gap below the card */
            .bv-step-card:not(:last-child)::after {
                content: "";
                position: absolute;
                top: 100%; /* Starts exactly at the bottom of the card */
                height: 100px; /* Spans the 60px gap and reaches 20px into the next icon */
                z-index: -1;
            }

            /* Gentle curve to the right */
            .bv-step-card:nth-child(odd):not(:last-child)::after {
                left: 40%;
                width: 20%; /* Box is 40% to 60%. Center is 50%. */
                border-right: 2px dashed rgba(255, 255, 255, .3);
                border-radius: 50%;
            }

            /* Gentle curve to the left */
            .bv-step-card:nth-child(even):not(:last-child)::after {
                right: 40%;
                width: 20%; /* Box is 40% to 60%. Center is 50%. */
                border-left: 2px dashed rgba(255, 255, 255, .3);
                border-radius: 50%;
            }

        }
        @media(max-width:600px) {
            .bv-steps-section {
               padding: 50px 24px 100px;
            }
        }

        .bv-step-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 0 24px 28px;
            /* no top padding — icon overflows above */
            text-align: center;
            position: relative;
            z-index: 1;
            /* CRITICAL: allow icon to overflow above card edges */
            overflow: visible;
            margin-top: 90px;
            /* reserve space for the overflowing icon */
            transition: transform .3s cubic-bezier(.16, 1, .3, 1), box-shadow .3s;
        }

        .bv-step-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 24px 60px rgba(0, 0, 0, .5);
            border-color: rgba(255, 109, 0, .3);
        }

        .bv-step-icon-wrap {
            width: 180px;
            height: 180px;
            /* Pull icon UP: half of height = 90px above card top edge (aligned half inside) */
            margin: -90px auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2;
        }

        /* 3D floating image style with strong drop-shadow for floating look */
        .bv-step-icon-wrap img {
            width: 200px;
            height: 200px;
            object-fit: contain;
            filter: drop-shadow(0 20px 32px rgba(0, 0, 0, 0.65));
            transition: transform .4s cubic-bezier(.16, 1, .3, 1);
        }

        .bv-step-card:hover .bv-step-icon-wrap img {
            transform: scale(1.08) translateY(-8px);
        }

        .bv-step-num {
            position: absolute;
            top: -6px;
            right: -6px;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff6d00, #ffab40);
            color: #fff;
            font-size: 11px;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(255, 109, 0, .5);
        }

        .bv-step-title {
            font-size: 13px;
            font-weight: 900;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: .12em;
            margin: 0 0 10px;
        }

        .bv-step-desc {
            font-size: 12px;
            color: rgba(255, 255, 255, .35);
            line-height: 1.7;
            margin: 0;
        }

        @media(max-width:768px) {
            /* Move step number inside the card on mobile */
            .bv-step-num {
                top: auto;
                bottom: -15px;
                right: auto;
                left: 50%;
                transform: translateX(-50%);
            }
        }

        /* ── CTA section ─────────────────────────────────────────────── */
        .bv-cta-section {
            padding: 100px 24px;
            text-align: center;
            background: linear-gradient(180deg, #0d1333, #070c24);
            position: relative;
            overflow: hidden;
        }

        .bv-cta-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 800px;
            height: 800px;
            border-radius: 50%;
            background: rgba(255, 109, 0, .09);
            filter: blur(120px);
            pointer-events: none;
        }

        .bv-cta-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 999px;
            padding: 6px 16px;
            font-size: 10px;
            font-weight: 800;
            color: rgba(255, 255, 255, .6);
            text-transform: uppercase;
            letter-spacing: .2em;
            margin-bottom: 28px;
        }

        .bv-cta-title {
            font-size: clamp(2.5rem, 6vw, 5rem);
            font-weight: 900;
            color: #fff;
            line-height: 1.05;
            letter-spacing: -.02em;
            margin: 0 0 20px;
        }

        .bv-cta-accent {
            color: transparent;
            background: linear-gradient(135deg, #ff6d00, #ffab40);
            -webkit-background-clip: text;
            background-clip: text;
            font-style: italic;
            padding-right: 0.12em;
            display: inline-block;
        }

        .bv-cta-desc {
            color: rgba(255, 255, 255, .4);
            font-size: 17px;
            max-width: 540px;
            margin: 0 auto 40px;
            line-height: 1.7;
        }

        .bv-cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #ff6d00, #ffab40);
            color: #fff;
            font-weight: 800;
            font-size: 14px;
            padding: 16px 36px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            letter-spacing: .05em;
            text-transform: uppercase;
            box-shadow: 0 14px 40px rgba(255, 109, 0, .4);
            transition: filter .2s, transform .2s;
        }

        .bv-cta-btn:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
        }

        /* ══════════════════════════════════════════════════════════════
           MOBILE POLISH (≤600px) — discovery page. Declared last so it wins
           the cascade cleanly. Desktop/tablet untouched.
           ══════════════════════════════════════════════════════════════ */
        @media (max-width: 600px) {
            /* Hero: tighter top gap + smaller display type */
            .bv-hero {
                padding: 96px 20px 8px;
            }
            .bv-hero h1 {
                font-size: clamp(1.7rem, 8vw, 2.1rem) !important;
                margin-bottom: 12px !important;
            }
            .bv-hero > div > p,
            .bv-hero p[style*="1.1rem"] {
                font-size: 0.95rem !important;
                margin-bottom: 26px !important;
            }

            /* Section titles ("Recommended Professionals") */
            .bv-section-title {
                font-size: 1.5rem;
            }

            /* "Book in 3 Easy Steps" — smaller, clearer cards */
            .bv-steps-section {
                padding: 44px 20px 56px;
            }
            .bv-steps-head {
                margin-bottom: 34px !important;
            }
            .bv-steps-head h2 {
                font-size: 1.55rem !important;
            }
            .bv-steps-grid {
                /* Single column on mobile: this `gap` is the VERTICAL space between
                   stacked cards. Keep it generous so the dashed connector arrows
                   between steps have room to draw (the ≤768 rule relies on this). */
                gap: 54px;
            }
            .bv-step-card {
                margin-top: 56px;
                padding: 0 16px 20px;
                border-radius: 16px;
            }
            .bv-step-icon-wrap {
                width: 112px;
                height: 112px;
                /* Bottom margin gives the number badge (positioned bottom:-15px)
                   clearance so it doesn't overlap the step title below it. */
                margin: -56px auto 28px;
            }
            .bv-step-icon-wrap img {
                width: 120px;
                height: 120px;
            }
            .bv-step-title {
                font-size: 12px;
                margin-bottom: 6px;
            }
            .bv-step-desc {
                font-size: 11px;
                line-height: 1.55;
            }

            /* Grow-your-business / "Join With …" CTA */
            .bv-cta-section {
                padding: 56px 22px;
            }
            .bv-cta-badge {
                font-size: 9px;
                margin-bottom: 18px;
                letter-spacing: .16em;
            }
            .bv-cta-title {
                font-size: clamp(1.5rem, 6.6vw, 1.95rem);
                line-height: 1.12;
                margin-bottom: 16px;
            }
            .bv-cta-desc {
                font-size: 13.5px;
                margin-bottom: 24px;
                max-width: 340px;
            }
            .bv-cta-btn {
                font-size: 12px;
                padding: 13px 24px;
            }
        }

        /* ══════════════════════════════════════════════════════════════
           CURVED / LAYERED THEME — ALL WIDTHS

           Structure: the *look* (colours, gradients, masks, stacking) is
           declared once as shared base rules; only the *dimensions* are
           re-stated per breakpoint below, because a 780px glow orb or a 230px
           arc sized for a 1440px canvas is meaningless on a 390px phone.

           These rules sit at the end of the stylesheet, so where they overlap
           an earlier max-width:600px rule they intentionally win. Every such
           overlap is restated explicitly in the phone block further down —
           nothing is left to chance in the cascade.
           ══════════════════════════════════════════════════════════════ */

        /* Cool teal rim lighting replacing the original warm orange orbs. */
        .bv-hero-glow-1 {
            background: radial-gradient(circle, rgba(16, 185, 166, .22) 0%, rgba(16, 185, 166, 0) 70%);
        }

        .bv-hero-glow-2 {
            bottom: auto;
            background: radial-gradient(circle, rgba(8, 168, 200, .20) 0%, rgba(8, 168, 200, 0) 70%);
        }

        /* Geometric mesh grid. Sits ABOVE the header artwork (z-index 2 vs 1) so
           the lines rule across the scene the way they do in the reference art,
           rather than being buried under an opaque image.

           The radial mask is inverted from the usual centre-strong pattern: the
           grid is faintest over the middle, where the figures and the brightest
           rim lights are, and strongest out at the edges — so it frames the
           composition instead of drawing lines over faces. The arc (z-index 6)
           is opaque and covers whatever reaches the bottom. */
        .bv-hero-grid {
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255, 255, 255, .07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .07) 1px, transparent 1px);
            -webkit-mask-image: radial-gradient(ellipse 80% 72% at 50% 46%, transparent 0%, rgba(0, 0, 0, .4) 50%, rgba(0, 0, 0, .85) 76%, #000 100%);
            mask-image: radial-gradient(ellipse 80% 72% at 50% 46%, transparent 0%, rgba(0, 0, 0, .4) 50%, rgba(0, 0, 0, .85) 76%, #000 100%);
        }

        /* Header artwork — the professional line-up, used full-bleed as the
           hero backdrop (it replaces the old flat SVG silhouette strip).

           Two background layers in one element: a navy scrim over the photo so
           white display type stays legible across the bright neon rim lights,
           and the artwork itself. The scrim is deliberately light through the
           middle band — that is where the neon rim lighting lives, and burying
           it defeats the point of the artwork. The mask dissolves the whole
           layer before the arc, so the glow orbs underneath blend back through
           the bottom of the hero instead of the image ending on a hard edge.
           The mesh grid is layered on top of this, not under it.

           WEIGHT: the artwork ships as AVIF / WebP / JPEG derivatives at three
           widths (see public/images/hero/), picked per breakpoint below. The
           original 1.38 MB PNG is no longer referenced by any rule — the widest
           AVIF replacing it is 29 KB.

           Each breakpoint declares background-image twice on purpose: the first
           is a plain url() every browser understands, the second overwrites it
           with image-set() so modern browsers pick AVIF, then WebP, then JPEG.
           Anything that cannot parse image-set() simply ignores the second
           declaration and keeps the JPEG. The scrim is hoisted into a custom
           property so restating the pair stays readable. */
        .bv-hero-crowd {
            position: absolute;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            --bv-hero-scrim:
                linear-gradient(180deg,
                    rgba(8, 13, 38, .62) 0%,
                    rgba(8, 13, 38, .26) 34%,
                    rgba(8, 13, 38, .46) 72%,
                    rgba(8, 13, 38, .80) 100%);
            background-repeat: no-repeat;
            background-position: center top;
            background-size: auto, cover;
            /* Default tier — 1280w. Laptops and tablets land here; the two
               media queries further down swap in a lighter or heavier file. */
            background-image:
                var(--bv-hero-scrim),
                url('{{ asset('images/hero/hero-1280.jpg') }}');
            background-image:
                var(--bv-hero-scrim),
                image-set(
                    url('{{ asset('images/hero/hero-1280.avif') }}') type('image/avif'),
                    url('{{ asset('images/hero/hero-1280.webp') }}') type('image/webp'),
                    url('{{ asset('images/hero/hero-1280.jpg') }}') type('image/jpeg'));
            -webkit-mask-image: linear-gradient(180deg, #000 0%, #000 66%, transparent 96%);
            mask-image: linear-gradient(180deg, #000 0%, #000 66%, transparent 96%);
        }

        /* Large desktops get the full 1717w master (29 KB as AVIF). */
        @media (min-width: 1280px) {
            .bv-hero-crowd {
                background-image:
                    var(--bv-hero-scrim),
                    url('{{ asset('images/hero/hero-1717.jpg') }}');
                background-image:
                    var(--bv-hero-scrim),
                    image-set(
                        url('{{ asset('images/hero/hero-1717.avif') }}') type('image/avif'),
                        url('{{ asset('images/hero/hero-1717.webp') }}') type('image/webp'),
                        url('{{ asset('images/hero/hero-1717.jpg') }}') type('image/jpeg'));
            }
        }

        /* Curved bottom edge of the hero. Sits above the backdrop layers but
           below the hero content (z-index 10), so the stat tiles ride on the
           arc exactly as in the reference. */
        .bv-hero-curve {
            position: absolute;
            bottom: -1px;
            z-index: 6;
            pointer-events: none;
            background: #080d26;
            border-top: 1px solid rgba(255, 255, 255, .14);
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
            box-shadow: 0 -18px 50px -20px rgba(8, 168, 200, .35);
        }

        /* Matching tone below the arc so the curve is a single clean edge. */
        .bv-section {
            background: #080d26;
        }

        /* ── CTA: curved wireframe meshes flanking the headline ──────────── */
        .bv-cta-section::before,
        .bv-cta-section::after {
            content: "";
            position: absolute;
            z-index: 1;
            pointer-events: none;
            /* Concentric rings + radial spokes = the curved polar grid. */
            background:
                repeating-radial-gradient(circle at 50% 50%, transparent 0 44px, rgba(255, 140, 66, .22) 44px 45px),
                repeating-conic-gradient(from 0deg at 50% 50%, transparent 0 7deg, rgba(255, 140, 66, .18) 7deg 7.4deg);
            -webkit-mask-image: radial-gradient(circle at 50% 50%, #000 16%, rgba(0, 0, 0, .5) 44%, transparent 66%);
            mask-image: radial-gradient(circle at 50% 50%, #000 16%, rgba(0, 0, 0, .5) 44%, transparent 66%);
        }

        /* ── Sizing: desktop + tablet ────────────────────────────────────── */
        @media (min-width: 601px) {
            .bv-hero { padding-bottom: 170px; }

            .bv-hero-glow-1 { top: -140px; left: -240px; width: 780px; height: 660px; filter: blur(90px); }
            .bv-hero-glow-2 { top: -140px; right: -240px; width: 800px; height: 680px; filter: blur(100px); }

            .bv-hero-grid { background-size: 60px 60px; }

            /* Wide canvas: the artwork's own aspect is close to the hero's, so
               cover crops only a sliver off each side. */
            .bv-hero-crowd {
                background-position: center top;
                background-size: auto, cover;
            }

            .bv-hero-curve {
                left: -14%;
                right: -14%;
                height: 230px;
            }

            .bv-cta-section::before,
            .bv-cta-section::after {
                bottom: -300px;
                width: 900px;
                height: 900px;
            }
            .bv-cta-section::before { left: -340px; }
            .bv-cta-section::after { right: -340px; }
        }

        /* ── Sizing: phones (≤600px) ─────────────────────────────────────────
           Same theme, scaled to the canvas. The hero gains bottom padding it
           did not have before — the arc needs somewhere to live — and the
           silhouette artwork is dropped to roughly half scale so individual
           figures stay legible rather than becoming one dark smear. */
        @media (max-width: 600px) {
            .bv-hero { padding-bottom: 92px; }

            .bv-hero-glow-1 { top: -90px; left: -150px; width: 420px; height: 380px; filter: blur(70px); }
            .bv-hero-glow-2 { top: -70px; right: -150px; width: 420px; height: 380px; filter: blur(70px); }

            .bv-hero-grid { background-size: 34px 34px; }

            /* Phone: cover on a portrait hero would crop the line-up down to
               one torso, so the artwork is laid in as a wide band across the
               top instead — a few figures stay legible either side of the
               headline. The scrim is heavier here because the display type sits
               directly over the brightest part of the image. */
            .bv-hero-crowd {
                /* Heavier scrim than the desktop tier — the display type sits
                   directly over the brightest part of the image here. */
                --bv-hero-scrim:
                    linear-gradient(180deg,
                        rgba(8, 13, 38, .80) 0%,
                        rgba(8, 13, 38, .58) 40%,
                        rgba(8, 13, 38, .74) 100%);
                /* 960w: the band renders at ~740 CSS px on a 390px phone, so
                   this still has headroom on a 2x screen at 11 KB as AVIF. */
                background-image:
                    var(--bv-hero-scrim),
                    url('{{ asset('images/hero/hero-960.jpg') }}');
                background-image:
                    var(--bv-hero-scrim),
                    image-set(
                        url('{{ asset('images/hero/hero-960.avif') }}') type('image/avif'),
                        url('{{ asset('images/hero/hero-960.webp') }}') type('image/webp'),
                        url('{{ asset('images/hero/hero-960.jpg') }}') type('image/jpeg'));
                background-position: 50% 8%;
                background-size: auto, 190% auto;
                -webkit-mask-image: linear-gradient(180deg, #000 0%, #000 52%, transparent 88%);
                mask-image: linear-gradient(180deg, #000 0%, #000 52%, transparent 88%);
            }

            .bv-hero-curve {
                left: -24%;
                right: -24%;
                height: 96px;
                box-shadow: 0 -12px 30px -14px rgba(8, 168, 200, .35);
            }

            .bv-cta-section::before,
            .bv-cta-section::after {
                bottom: -170px;
                width: 470px;
                height: 470px;
            }
            .bv-cta-section::before { left: -190px; }
            .bv-cta-section::after { right: -190px; }
        }

        /* ══════════════════════════════════════════════════════════════
           STAT TILES — TABLET + DESKTOP (≥601px)

           Tile appearance starts at 601px so the 601–768px range is styled
           too. The arc transform itself is held back to ≥769px: below that
           .bv-stats still wraps (its own max-width:768px rule), and rotating
           tiles that have wrapped onto two rows reads as a mistake, not a
           curve.
           ══════════════════════════════════════════════════════════════ */
        @media (min-width: 601px) {
            .bv-stats-desktop {
                gap: 20px;
                align-items: flex-start;
                justify-content: center;
                border-top: none;
                padding-top: 0;
                margin-top: 56px;
                position: relative;
                z-index: 10;
            }

            .bv-stats-desktop > div {
                padding: 20px 26px;
                border-radius: 20px;
                border: 1px solid rgba(255, 255, 255, .10);
                background: linear-gradient(180deg, rgba(255, 255, 255, .09), rgba(255, 255, 255, .035));
                backdrop-filter: blur(20px) saturate(150%);
                -webkit-backdrop-filter: blur(20px) saturate(150%);
                box-shadow:
                    inset 0 1px 0 rgba(255, 255, 255, .12),
                    0 20px 44px -20px rgba(0, 0, 0, .8);
            }
        }

        /* Arc: peak at the centre, shoulders dropping away on both sides. */
        @media (min-width: 769px) {
            .bv-stats-desktop > div:nth-child(1) { transform: translateY(24px) rotate(-6deg); }
            .bv-stats-desktop > div:nth-child(2) { transform: translateY(0) rotate(-2deg); }
            .bv-stats-desktop > div:nth-child(3) { transform: translateY(0) rotate(2deg); }
            .bv-stats-desktop > div:nth-child(4) { transform: translateY(24px) rotate(6deg); }
        }

        /* ══════════════════════════════════════════════════════════════
           ARCED STAT TILES — PHONES (≤600px)

           On phones the counters render from .bv-stats-mobile, which lives
           below the steps section and wraps two-per-row. A four-across arc
           does not fit at this width, so each ROW is curved instead: the two
           tiles tilt away from each other, outer edges dropping, which reads
           as the same arc motif at phone scale.
           ══════════════════════════════════════════════════════════════ */
        @media (max-width: 600px) {
            .bv-stats-mobile {
                border-top: none;
                padding-top: 0;
                gap: 12px;
                margin-top: 40px;
            }

            .bv-stats-mobile > div {
                flex: 1 1 calc(50% - 12px);
                padding: 14px 8px;
                border-radius: 16px;
                border: 1px solid rgba(255, 255, 255, .10);
                background: linear-gradient(180deg, rgba(255, 255, 255, .09), rgba(255, 255, 255, .035));
                backdrop-filter: blur(16px) saturate(150%);
                -webkit-backdrop-filter: blur(16px) saturate(150%);
                box-shadow:
                    inset 0 1px 0 rgba(255, 255, 255, .12),
                    0 14px 30px -16px rgba(0, 0, 0, .8);
            }

            .bv-stats-mobile > div:nth-child(1) { transform: rotate(-5deg); }
            .bv-stats-mobile > div:nth-child(2) { transform: rotate(5deg); }
            .bv-stats-mobile > div:nth-child(3) { transform: rotate(-4deg); }
            .bv-stats-mobile > div:nth-child(4) { transform: rotate(4deg); }
        }
    </style>

    <div class="bv-page">

        {{-- ═══════════════════════════════════════════════════════
        HERO + SEARCH + CATEGORIES
        ═══════════════════════════════════════════════════════ --}}
        <section class="bv-hero">
            {{-- Backdrop layers, decorative only (no hit area, hidden from AT).
                 All of these are display:none below 601px so the phone hero is
                 byte-for-byte the layout it was before. --}}
            {{-- Source order matches paint order: glows, then the header
                 artwork, then the mesh grid ruled across the top of it. --}}
            <div class="bv-hero-glow-1" aria-hidden="true"></div>
            <div class="bv-hero-glow-2" aria-hidden="true"></div>
            <div class="bv-hero-crowd" aria-hidden="true"></div>
            <div class="bv-hero-grid" aria-hidden="true"></div>

            <div style="position:relative; z-index:10; text-align:center;">

                {{-- Badge --}}
                {{-- <div
                    style="display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:999px; padding:8px 20px; font-size:10px; font-weight:800; color:rgba(255,255,255,.7); text-transform:uppercase; letter-spacing:.25em; margin-bottom:32px;">
                    <span
                        style="width:8px; height:8px; border-radius:50%; background:#ff6d00; display:inline-block; box-shadow:0 0 10px rgba(255,109,0,.6);"></span>
                    TRUSTED BOOKING PLATFORM
                </div> --}}

                {{-- H1 --}}
                <h1
                    style="font-size:clamp(2rem,7vw,5rem); font-weight:900; color:#fff; line-height:1.05; letter-spacing:-.03em; margin:0 0 18px;">
                    Find Trusted <span
                        style="color:transparent; background:linear-gradient(135deg,#ff8c42,#ffab40); -webkit-background-clip:text; background-clip:text; font-style:italic; padding-right:0.12em; display:inline-block;">Professionals</span><br>
                    Near You
                </h1>

                {{-- Subheading --}}
                <p
                    style="color:rgba(255,255,255,.45); font-size:1.1rem; max-width:520px; margin:0 auto 48px; line-height:1.7;">
                    Book trusted local services quickly and easily.
                </p>

                {{-- ── Search Bar ── --}}
                <div class="bv-search-wrap">
                    <div class="bv-search-bar">
                        <form action="{{ route('home') }}" method="GET" class="bv-search-form">
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
                                    placeholder="Service or Professional">
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
                                            $selectedCategory = request('type');
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
                                    <div class="custom-dropdown-item {{ !request('type') ? 'selected' : '' }}" data-value="">All Categories</div>
                                    @foreach($allThemes as $key => $t)
                                        <div class="custom-dropdown-item {{ request('type') == $key ? 'selected' : '' }}" data-value="{{ $key }}">
                                            {{ $t['emoji'] ?? '✨' }} {{ $t['label'] ?? ucfirst($key) }}
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="type" id="specialty-input" value="{{ request('type') }}">
                            </div>

                            {{-- Near Me (location) --}}
                            <div class="bv-search-field bv-nearme" style="border-right:none; cursor:pointer;" title="Find experts near you"
                                :class="{ 'is-locating': locating }"
                                x-data="{
                                    locating: false,
                                    useGPS() {
                                        if (!('geolocation' in navigator)) {
                                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'GPS not supported by this browser.', type: 'error' } }));
                                            return;
                                        }
                                        this.locating = true;
                                        navigator.geolocation.getCurrentPosition((position) => {
                                            const lat = position.coords.latitude, lng = position.coords.longitude;
                                            window.resolvePlaceName(lat, lng).then((place) => {
                                                window.writeLocationCookies(lat, lng, place.state, place.city);
                                                $el.closest('form').submit();
                                            });
                                        }, (error) => {
                                            this.locating = false;
                                            console.warn('Geolocation failed', error);
                                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Could not get your location. Please allow access and retry.', type: 'error' } }));
                                        }, { timeout: 10000 });
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
                                <span class="bv-nearme-label" x-text="locating ? 'Locating…' : 'Near Me'"></span>
                                <span class="bv-nearme-arrow" aria-hidden="true">
                                    <svg width="17" height="17" fill="currentColor" stroke="none"
                                        viewBox="0 0 24 24">
                                        <path d="M21 3L3 10.53v.98l6.84 2.65L12.48 21h.98L21 3z" />
                                    </svg>
                                </span>
                            </div>

                            <button class="bv-search-btn" type="submit">Search Services</button>
                            @if(request('search') || request('type') || request('location'))
                                <a href="{{ route('home') }}" class="bv-reset-btn">Reset</a>
                            @endif
                        </form>
                    </div>

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
                    
                    // First item: All Services
                    $categoriesList[] = [
                        'key' => '',
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
                    $currentType = request('type', '');
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
                                $isActive = ($cat['key'] === '' && !request('type')) || (request('type') === $cat['key']);
                                @endphp
                                <a href="{{ request()->fullUrlWithQuery(['type'=>$cat['key']]) }}"
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
                                $isActive = ($cat['key'] === '' && !request('type')) || (request('type') === $cat['key']);
                                @endphp
                                <a href="{{ request()->fullUrlWithQuery(['type'=>$cat['key']]) }}" 
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

                {{-- ── Stats ── --}}
                @php
                    // All five come from the controller (cached aggregates) —
                    // nothing on this page is a placeholder figure any more.
                    $totalClients      = $stats['clients'];
                    $totalCities       = $stats['cities'];
                    $totalAppointments = $stats['appointments'];
                    $avgRating         = $stats['rating'];
                    $hasRatings        = $stats['reviews'] > 0;
                @endphp
                @if($totalClients > 0 || $totalCities > 0 || $totalAppointments > 0)
                <div class="bv-stats bv-stats-desktop">
                    <div>
                        <div class="bv-stat-num"><span data-counter data-target="{{ $totalClients }}" data-suffix="+">0</span></div>
                        <div class="bv-stat-label">Happy Clients</div>
                    </div>
                    <div>
                        <div class="bv-stat-num"><span data-counter data-target="{{ $totalCities }}" data-suffix="+">0</span></div>
                        <div class="bv-stat-label">Cities Reach</div>
                    </div>
                    <div>
                        <div class="bv-stat-num"><span data-counter data-target="{{ $totalAppointments }}" data-suffix="+" data-decimals="0">0</span></div>
                        <div class="bv-stat-label">Appointments</div>
                    </div>
                    {{-- Only shown once there is at least one real review to
                         average — better a three-tile row than a made-up score. --}}
                    @if($hasRatings)
                    <div>
                        <div class="bv-stat-num">
                            <span data-counter data-target="{{ number_format($avgRating, 1) }}" data-decimals="1">0</span>
                            <span style="color:#ffab40; font-size:1.6rem;">★</span>
                        </div>
                        <div class="bv-stat-label">User Rating</div>
                    </div>
                    @endif
                </div>
                @endif

            </div>

            {{-- Curved bottom edge — the section below adopts the same tone so
                 this reads as one continuous arc, not a seam. --}}
            <div class="bv-hero-curve" aria-hidden="true"></div>
        </section>

        {{-- ═══════════════════════════════════════════════════════
        RECOMMENDED PROFESSIONALS
        ═══════════════════════════════════════════════════════ --}}
        <section class="bv-section">
            <div style="max-width:1100px; margin:0 auto;">
                <div>
                    <h2 class="bv-section-title">
                        Recommended <span class="bv-section-accent">Professionals</span>
                    </h2>
                    <div class="bv-section-bar"></div>
                    <p class="bv-section-sub">Handpicked specialists for your premium experience</p>
                </div>

                <div class="bv-grid">
                    @forelse($vendors as $vendor)
                    @php
                    $vType = $vendor->category?->slug ?? 'consultant';
                    // Use the already-loaded $allThemes array instead of calling ThemeService::getTheme() per card
                    $vTheme = array_merge([
                        'primary'      => '#2979ff',
                        'primary_dark' => '#00b0ff',
                        'label'        => ucfirst($vType),
                        'emoji'        => '✨',
                    ], $allThemes[$vType] ?? ($allThemes['consultant'] ?? []));
                    $isOpen = $vendor->is_bookable_now ?? false;
                    
                    $c1 = $vTheme['primary'];
                    $c2 = $vTheme['primary_dark'];
                    $rgbStr = match($vType) {
                        'health','doctor' => '0,200,83',
                        'beauty','barber' => '255,109,0',
                        'sports','activity'=> '255,214,0',
                        'consultant' => '41,121,255',
                        'training' => '124,58,237',
                        default => '26,35,126'
                    };
                    [$cr,$cg,$cb] = explode(',', $rgbStr);

                    if ($vendor->shop_photo) {
                    $img = asset('storage/' . $vendor->shop_photo);
                    } elseif (in_array($vType,['health','doctor'])) {
                    $img = asset('images/placeholders/health.svg');
                    } elseif (in_array($vType,['beauty','barber'])) {
                    $img = asset('images/placeholders/beauty.svg');
                    } elseif (in_array($vType,['sports','activity'])) {
                    $img = asset('images/placeholders/sports.svg');
                    } elseif ($vType === 'training') {
                    $img = asset('images/placeholders/training.svg');
                    } else {
                    $img = asset('images/placeholders/default.svg');
                    }

                    $catCode = 'general';
                    if (in_array($vType, ['health','doctor'])) $catCode = 'doctor';
                    elseif (in_array($vType, ['beauty','barber'])) $catCode = 'barber';
                    elseif (in_array($vType, ['sports','activity'])) $catCode = 'sports';
                    elseif ($vType === 'consultant') $catCode = 'consultant';
                    elseif ($vType === 'training') $catCode = 'training';

                    $routeUrl = route('vendor.show', $vendor->slug);
                    $priceStr = '₹' . number_format($vendor->starting_fee);
                    $name = $vendor->business_name;
                    // No invented fallback — a vendor who has not filled in an
                    // address simply shows none (the row below is skipped).
                    $address = trim((string) $vendor->address);
                    $catLabel = $vTheme['label'] ?? ucfirst($vType);
                    @endphp

                    @php
                    $priceLabel = match($catCode) {
                        'doctor' => 'Consultation',
                        'barber' => 'Starts From',
                        'consultant' => 'Session',
                        'training' => 'Session',
                        'sports' => 'Entry / Pass',
                        default => 'Starts From'
                    };
                    @endphp
                    <a href="{{ $routeUrl }}" class="bv-dynamic-card bv-card-sports {{ $isOpen ? '' : 'bv-closed pointer-events-none' }}"
                        style="--c1:{{ $c1 }};--c2:{{ $c2 }};--cr:{{ $cr }};--cg:{{ $cg }};--cb:{{ $cb }};">
                        <img src="{{ $img }}" alt="{{ $name }}" loading="{{ $loop->iteration <= 6 ? 'eager' : 'lazy' }}">
                        {{-- Real average from vendor_reviews (aggregated in the
                             listing query, so no per-card AVG). A vendor with no
                             reviews yet gets no badge at all — showing a default
                             score would be inventing reputation. --}}
                        @if($vendor->isSubscriptionActive() && ($vendor->reviews_count ?? 0) > 0)
                        <div class="bv-rc-rating" title="{{ $vendor->reviews_count }} {{ Str::plural('review', $vendor->reviews_count) }}" style="position:absolute; top:12px; right:12px; background:rgba(0,0,0,0.75); backdrop-filter:blur(8px); padding:4px 10px; border-radius:999px; color:#fff; font-size:12px; font-weight:800; display:flex; gap:5px; border:1px solid rgba(255,255,255,0.15); z-index: 2;">
                            <span style="color:#ffab40;">★</span> {{ number_format((float) $vendor->avg_rating, 1) }}
                            <span style="color:rgba(255,255,255,0.55); font-weight:700;">({{ $vendor->reviews_count }})</span>
                        </div>
                        @endif
                        <div class="bv-card-sports-overlay">
                            {{-- <span class="bv-rc-badge"
                                style="display:inline-block; background:rgba(var(--cr),var(--cg),var(--cb),0.9); backdrop-filter:blur(4px); color:#000; font-size:10px; font-weight:900; padding:6px 12px; border-radius:8px; align-self:flex-start; margin-bottom:12px; text-transform:uppercase; letter-spacing:.05em;">
                                {{ $catLabel }}
                            </span>--}}
                            <h3 class="bv-rc-name" style="color:#fff; font-size:24px; font-weight:900; margin:0 0 8px; line-height:1.1; display:flex; align-items:center; gap:8px;">
                                {{ $name }}
                                @if($vendor->is_verified)
                                <svg style="color:#38bdf8; flex-shrink:0; width:20px; height:20px;" viewBox="0 0 24 24" fill="currentColor" title="Verified">
                                    <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"></path>
                                </svg>
                                @endif
                            </h3>
                            <div class="bv-rc-loc"
                                style="display:flex; align-items:center; gap:6px; font-size:13px; color:rgba(255,255,255,0.8); margin-bottom:20px;">
                                @if($address !== '')
                                <svg class="bv-rc-loc-pin" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="bv-rc-addr" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">{{ $address }}</span>
                                @endif
                                {{-- Straight-line distance from the customer's stored
                                     coordinates. Absent whenever either the customer
                                     or the vendor has no coordinates on file. --}}
                                @if($vendor->isSubscriptionActive() && $vendor->distance_km !== null)
                                <span class="bv-rc-dist" style="margin-left:auto; font-weight:700; color:rgba(var(--cr),var(--cg),var(--cb),0.9); font-size:11px; text-transform:uppercase; letter-spacing:0.05em;">{{ $vendor->distance_km < 1 ? round($vendor->distance_km * 1000) . ' m' : '~' . number_format($vendor->distance_km, 1) . ' km' }}</span>
                                @endif
                            </div>
                            <div class="bv-rc-pricebar"
                                style="display:flex; align-items:center; justify-content:space-between; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); padding:14px; border-radius:14px;">
                                <div>
                                    <div class="bv-rc-price-label"
                                        style="font-size:11px; font-weight:800; text-transform:uppercase; color:rgba(255,255,255,0.6); letter-spacing:.05em;">
                                        {{ $priceLabel }}</div>
                                    <div class="bv-rc-price" style="font-size:20px; font-weight:900; color:#fff;">{{ $priceStr }} {{ $vendor->starting_fee > 0 ? 'onwards' : '' }}</div>
                                </div>
                                @if($vendor->isSubscriptionActive())
                                <div class="bv-rc-status" style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                                    @if(!$isOpen)
                                        <div style="font-size: 9px; font-weight: 900; color: #ffab40; text-transform: uppercase;">Closed</div>
                                        <div style="font-size: 11px; font-weight: 700; color: #fff;">Opens At: {{ \Carbon\Carbon::parse($vendor->global_opening_time)->format('h:i A') }}</div>
                                    @else
                                        {{-- Backed by live_queue_count from the listing
                                             query: confirmed bookings still to be served
                                             today. Falls back to the bare label rather
                                             than advertising a queue of zero. --}}
                                        <div style="font-size: 9px; font-weight: 900; color: #4ade80; text-transform: uppercase; display: flex; align-items: center; gap: 4px;">
                                            <span style="width:6px; height:6px; border-radius:50%; background:#4ade80; display:inline-block; box-shadow:0 0 8px #4ade80;"></span>
                                            @if(($vendor->live_queue_count ?? 0) > 0)
                                                {{ $vendor->live_queue_count }} In Queue
                                            @else
                                                Live Queue
                                            @endif
                                        </div>
                                        <div
                                            style="width:36px; height:36px; background:var(--c1); border-radius:50%; display:flex; align-items:center; justify-content:center; color:#000; box-shadow:0 6px 16px rgba(var(--cr),var(--cg),var(--cb),0.4);">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="3"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                @else
                                <div class="bv-rc-status" style="display: flex; align-items: center; justify-content: center;">
                                    <div style="width:36px; height:36px; background:rgba(255,255,255,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff;">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </a>

                    @empty
                    <div style="grid-column:1/-1; padding:80px 0; text-align:center;">
                        <div style="font-size:5rem; opacity:.2;">📭</div>
                        <h3 style="color:#fff; font-size:2rem; margin:24px 0 12px;">No Experts Found</h3>
                        <p style="color:rgba(255,255,255,.4);">Try adjusting your search or filters.</p>
                        <a href="{{ route('home') }}"
                            style="display:inline-block; margin-top:28px; background:linear-gradient(135deg,#ff6d00,#ffab40); color:#fff; font-weight:800; padding:14px 32px; border-radius:12px; text-decoration:none;">Reset
                            Search</a>
                    </div>
                    @endforelse
                </div>

                @if($vendors->hasPages())
                <div style="margin-top:60px;">{{ $vendors->links() }}</div>
                @endif
            </div>
        </section>

        {{-- ═══════════════════════════════════════════════════════
        BOOK IN 3 EASY STEPS
        ═══════════════════════════════════════════════════════ --}}
        <section class="bv-steps-section">
            <div class="bv-steps-head" style="text-align:center; margin-bottom:60px;">
                <h2 style="font-size:2.4rem; font-weight:900; color:#fff; letter-spacing:-.02em; margin:0 0 10px;">
                    Book in <span style="color:#ff8c42; font-style:italic;">3 Easy Steps</span>
                </h2>
                <p
                    style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.35em; color:rgba(255,255,255,.25);">
                    Your professional journey starts here</p>
            </div>

            <div class="bv-steps-grid">

                {{-- Step 1: Find & Filter — map+magnifier 3D illustration --}}
                <div class="bv-step-card">
                    <div class="bv-step-icon-wrap">
                        <div class="bv-step-num">1</div>
                        {{-- 3D Map + Magnifier Image --}}
                        <img src="{{ asset('images/steps/step1.png') }}" alt="Find & Filter" loading="lazy">
                    </div>
                    <h3 class="bv-step-title">Find &amp; Filter</h3>
                    <p class="bv-step-desc">Search for top-tier professionals in your area that fulfill your specific
                        needs.</p>
                </div>

                {{-- Step 2: Choose Easy — calendar + checkmark 3D illustration --}}
                <div class="bv-step-card">
                    <div class="bv-step-icon-wrap">
                        <div class="bv-step-num">2</div>
                        {{-- 3D Calendar Image --}}
                        <img src="{{ asset('images/steps/step2.png') }}" alt="Choose Easy" loading="lazy">
                    </div>
                    <h3 class="bv-step-title">Choose Easy</h3>
                    <p class="bv-step-desc">See detailed ratings and reviews, then book the best expert instantly.</p>
                </div>

                {{-- Step 3: Confirm & Go — ticket/pass 3D illustration --}}
                <div class="bv-step-card">
                    <div class="bv-step-icon-wrap">
                        <div class="bv-step-num">3</div>
                        {{-- 3D Ticket Image --}}
                        <img src="{{ asset('images/steps/step3.png') }}" alt="Confirm & Go" loading="lazy">
                    </div>
                    <h3 class="bv-step-title">Confirm &amp; Go</h3>
                    <p class="bv-step-desc">Get instant confirmation and reminders for your professional appointment.
                    </p>
                </div>
            </div>

            {{-- Ambient glow --}}
            <div
                style="position:absolute; top:20%; right:-10%; width:500px; height:500px; background:rgba(255,109,0,.05); border-radius:50%; filter:blur(100px); pointer-events:none;">
            </div>

            {{-- Stats — repositioned below the steps on mobile only --}}
            @if($totalClients > 0 || $totalCities > 0 || $totalAppointments > 0)
            <div class="bv-stats bv-stats-mobile">
                <div>
                    <div class="bv-stat-num"><span data-counter data-target="{{ $totalClients }}" data-suffix="+">0</span></div>
                    <div class="bv-stat-label">Happy Clients</div>
                </div>
                <div>
                    <div class="bv-stat-num"><span data-counter data-target="{{ $totalCities }}" data-suffix="+">0</span></div>
                    <div class="bv-stat-label">Cities Reach</div>
                </div>
                <div>
                    <div class="bv-stat-num"><span data-counter data-target="{{ $totalAppointments }}" data-suffix="+" data-decimals="0">0</span></div>
                    <div class="bv-stat-label">Appointments</div>
                </div>
                @if($hasRatings)
                <div>
                    <div class="bv-stat-num">
                        <span data-counter data-target="{{ number_format($avgRating, 1) }}" data-decimals="1">0</span>
                        <span style="color:#ffab40; font-size:1.6rem;">★</span>
                    </div>
                    <div class="bv-stat-label">User Rating</div>
                </div>
                @endif
            </div>
            @endif
        </section>

        {{-- ═══════════════════════════════════════════════════════
        GROW YOUR BUSINESS CTA
        ═══════════════════════════════════════════════════════ --}}
        <section class="bv-cta-section">
            <div class="bv-cta-glow"></div>
            <div style="position:relative; z-index:2;">
                <div class="bv-cta-badge">
                    <span
                        style="width:6px;height:6px;border-radius:50%;background:#ff6d00;display:inline-block;"></span>
                    Join With {{ config('brand.platform') }}
                </div>
                <h2 class="bv-cta-title">
                    GROW YOUR <br><span class="bv-cta-accent">BUSINESS</span> WITH US
                </h2>
                <p class="bv-cta-desc">
                    Are you a professional? Join us to get more bookings and grow your client base with our advanced
                    tools.
                </p>
                <a href="/register/vendor" class="bv-cta-btn">
                    Join as a Professional
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </a>
            </div>
            {{-- Decorative star --}}
            <div style="position:absolute; bottom:40px; right:60px; width:60px; height:60px; opacity:.15;">
                <svg viewBox="0 0 100 100" fill="white">
                    <polygon points="50,5 61,35 95,35 68,57 79,91 50,70 21,91 32,57 5,35 39,35" />
                </svg>
            </div>
        </section>

    </div>{{-- .bv-page --}}

    {{-- ── Counter animation script ─────────────────────────────── --}}
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
            
            if (rowMobile && dotsMobile.length && pillsMobile.length) {
                const pillWidth = 232; // 220px width + 12px gap

                window.scrollMobileCategories = function(direction) {
                    rowMobile.scrollBy({ left: direction * pillWidth, behavior: 'smooth' });
                };

                window.scrollToMobileCategory = function(index) {
                    if (pillsMobile[index]) {
                        pillsMobile[index].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                    }
                };

                // Scroll the active category pill into view on page load
                setTimeout(() => {
                    const activePill = rowMobile.querySelector('.bv-cat-mobile-pill.active');
                    if (activePill) {
                        activePill.scrollIntoView({ behavior: 'auto', block: 'nearest', inline: 'center' });
                    }
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

</x-app-layout>