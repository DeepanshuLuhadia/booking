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

        /* ── Resolved location (suburb + wider area) ────────────────────
           Replaces the "Near Me" wording once we actually know where the
           visitor is, so the control states a fact instead of asking a
           question. Two stacked lines, hence the column layout. */
        .bv-nearme-place {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 1px;
            min-width: 0;
            line-height: 1.15;
        }

        .bv-nearme-eyebrow {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .18em;
            color: rgba(255, 171, 64, .85);
        }

        .bv-nearme-place .bv-nearme-label {
            font-size: 14px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .bv-nearme-context {
            font-size: 10px;
            font-weight: 600;
            color: rgba(255, 255, 255, .45);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* A known location reads as settled rather than as a prompt. */
        .bv-nearme.has-location {
            background: rgba(255, 109, 0, 0.10);
            box-shadow: inset 0 0 0 1px rgba(255, 140, 66, 0.22);
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

        /* Clear-search "×". Lives inside the text field, directly against the
           search icon, so resetting never costs a button of its own. */
        .bv-reset-btn {
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            /* The field's own 12px gap does the spacing — no extra margin, so
               the × stays tight against the search icon. */
            margin: 0;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            color: rgba(255, 255, 255, 0.55);
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all .25s ease;
        }

        .bv-reset-btn:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
        }

        @media(max-width: 600px) {
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

            /* Location lives in the header on mobile (the pin beside the
               hamburger) and is not repeated here — one statement of where the
               visitor is, not two, and the search bar stays a single row. */
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

            /* Trailing gutter: without it the row runs out of scroll before the
               last category (Consultant) reaches the middle, so that one pill
               alone sat jammed against the right edge with its active glow
               clipped. Half a row minus half a pill is exactly the slack needed
               to centre it. A margin, not padding — padding would shrink the
               content box the pills size themselves against. */
            .bv-cat-mobile-row .bv-cat-mobile-pill:last-child {
                margin-right: calc(50% - (100% - 20px) / 6);
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
                padding: 16px 16px 30px;
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
            .bv-hero { padding-bottom: 60px; }

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
                height: 80px;
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
