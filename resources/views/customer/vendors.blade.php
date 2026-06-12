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

        @media(max-width: 600px) {
            .bv-search-btn {
                width: 100%;
                margin-left: 0;
                margin-top: 4px;
                padding: 14px;
                font-size: 13px;
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
                grid-template-columns: 1fr;
            }
             .bv-section {
                padding: 30px 24px ;
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
    </style>

    <div class="bv-page">

        {{-- ═══════════════════════════════════════════════════════
        HERO + SEARCH + CATEGORIES
        ═══════════════════════════════════════════════════════ --}}
        <section class="bv-hero">
            <div class="bv-hero-glow-1"></div>
            <div class="bv-hero-glow-2"></div>

            <div style="position:relative; z-index:10; text-align:center;">

                {{-- Badge --}}
                <div
                    style="display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:999px; padding:8px 20px; font-size:10px; font-weight:800; color:rgba(255,255,255,.7); text-transform:uppercase; letter-spacing:.25em; margin-bottom:32px;">
                    <span
                        style="width:8px; height:8px; border-radius:50%; background:#ff6d00; display:inline-block; box-shadow:0 0 10px rgba(255,109,0,.6);"></span>
                    TRUSTED BOOKING PLATFORM
                </div>

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
                            @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif

                            {{-- Expert Name --}}
                            <div class="bv-search-field">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <input class="bv-search-input" type="text" name="search" value="{{ request('search') }}"
                                    placeholder="Service or Professional">
                            </div>

                            {{-- Specialty --}}
                            <div class="bv-search-field" style="position: relative; overflow: visible;">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24" style="flex-shrink: 0;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <select class="bv-search-input" name="specialty" style="background: transparent; border: none; color: #fff; outline: none; cursor: pointer; padding-right: 28px; -webkit-appearance: none; -moz-appearance: none; appearance: none; width: 100%; font-size: 15px; font-weight: 600;">
                                    <option value="" style="background: #0d1333; color: #fff;">All Categories</option>
                                    @foreach($allThemes as $key => $t)
                                        <option value="{{ $key }}" {{ request('specialty') == $key ? 'selected' : '' }} style="background: #0d1333; color: #fff;">
                                            {{ $t['emoji'] ?? '✨' }} {{ $t['label'] ?? ucfirst($key) }}
                                        </option>
                                    @endforeach
                                </select>
                                <svg class="bv-search-caret" width="14" height="14" fill="none" stroke="white"
                                    stroke-width="2" viewBox="0 0 24 24" style="pointer-events: none; position: absolute; right: 20px; top: 50%; transform: translateY(-50%); opacity: 0.4;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>

                            {{-- Location --}}
                            <div class="bv-search-field" style="border-right:none;">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <input class="bv-search-input" type="text" name="location"
                                    value="{{ request('location') }}" placeholder="Enter City">
                                {{-- <svg class="bv-search-caret" width="14" height="14" fill="none" stroke="white"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg> --}}
                            </div>

                            <button class="bv-search-btn" type="submit">Search Services</button>
                        </form>
                    </div>

                    {{-- ── Category Pills ── --}}
                    <div class="bv-cat-scroll-wrap">
                        <button class="bv-cat-scroll-btn" id="cat-prev" aria-label="Previous" onclick="document.getElementById('catRow').scrollBy({left:-200,behavior:'smooth'})">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <div class="bv-cat-row" id="catRow">
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
                        // Split the "All" pill RGB
                        $allRgb = '255,109,0';
                        @endphp

                        {{-- All Services --}}
                        <a href="{{ request()->fullUrlWithQuery(['type'=>'']) }}"
                            class="bv-cat-pill {{ !request('type') ? 'active' : '' }}"
                            style="--cr:255;--cg:109;--cb:0;">
                            <div class="bv-cat-icon" style="background:linear-gradient(135deg,#ff6d00,#ffab40);">⭐</div>
                            <div class="bv-cat-text">
                                <span class="bv-cat-name">All</span>
                                <span class="bv-cat-sub" style="color:rgba(255,171,64,0.9);">Services</span>
                            </div>
                        </a>

                        @foreach($allThemes as $key => $t)
                        @php
                        $cm = $catMeta[$key] ?? $catMeta['default'];
                        $g = $cm['g'];
                        $rgb = $cm['rgb'];
                        // parse rgb to individual r,g,b for CSS vars
                        [$cr,$cg,$cb] = explode(',', $rgb);
                        $iconStyle = "background:linear-gradient(135deg,{$g[0]},{$g[1]});";
                        @endphp
                        <a href="{{ request()->fullUrlWithQuery(['type'=>$key]) }}"
                            class="bv-cat-pill {{ request('type')==$key ? 'active':'' }}"
                            style="--cr:{{ trim($cr) }};--cg:{{ trim($cg) }};--cb:{{ trim($cb) }};">
                            <div class="bv-cat-icon" style="{{ $iconStyle }}">{{ $t['emoji'] ?? '✨' }}</div>
                            <div class="bv-cat-text">
                                <span class="bv-cat-name">{{ $t['label'] }}</span>
                                <span class="bv-cat-sub">{{ $cm['sub'] }}</span>
                            </div>
                        </a>
                        @endforeach
                        </div>{{-- /bv-cat-row --}}
                        <button class="bv-cat-scroll-btn" id="cat-next" aria-label="Next" onclick="document.getElementById('catRow').scrollBy({left:200,behavior:'smooth'})">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>{{-- /bv-cat-scroll-wrap --}}
                </div>

                {{-- ── Stats ── --}}
                <div class="bv-stats">
                    <div>
                        <div class="bv-stat-num"><span data-counter data-target="80" data-suffix="K+">0</span></div>
                        <div class="bv-stat-label">Happy Clients</div>
                    </div>
                    <div>
                        <div class="bv-stat-num"><span data-counter data-target="500" data-suffix="+">0</span></div>
                        <div class="bv-stat-label">Cities Reach</div>
                    </div>
                    <div>
                        <div class="bv-stat-num"><span data-counter data-target="1.2" data-suffix="M"
                                data-decimals="1">0</span></div>
                        <div class="bv-stat-label">Appointments</div>
                    </div>
                    <div>
                        <div class="bv-stat-num">
                            <span data-counter data-target="4.9" data-decimals="1">0</span>
                            <span style="color:#ffab40; font-size:1.6rem;">★</span>
                        </div>
                        <div class="bv-stat-label">User Rating</div>
                    </div>
                </div>

            </div>
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
                    $isOpen = $vendor->is_currently_open;
                    
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
                    $img = 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?q=80&w=600&auto=format&fit=crop';
                    } elseif (in_array($vType,['beauty','barber'])) {
                    $img = 'https://images.unsplash.com/photo-1560066984-138dadb4c035?q=80&w=600&auto=format&fit=crop';
                    } elseif (in_array($vType,['sports','activity'])) {
                    $img =
                    'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=600&auto=format&fit=crop';
                    } elseif ($vType === 'training') {
                    $img =
                    'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=600&auto=format&fit=crop';
                    } else {
                    $img = 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=600&auto=format&fit=crop';
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
                    $address = $vendor->address ?? 'Premium Location, City Center';
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
                        <div class="bv-card-sports-overlay">
                            <span
                                style="display:inline-block; background:rgba(var(--cr),var(--cg),var(--cb),0.9); backdrop-filter:blur(4px); color:#000; font-size:10px; font-weight:900; padding:6px 12px; border-radius:8px; align-self:flex-start; margin-bottom:12px; text-transform:uppercase; letter-spacing:.05em;">
                                {{ $catLabel }}
                            </span>
                            <h3 style="color:#fff; font-size:24px; font-weight:900; margin:0 0 8px; line-height:1.1;">{{
                                $name }}</h3>
                            <div
                                style="display:flex; align-items:center; gap:6px; font-size:13px; color:rgba(255,255,255,0.8); margin-bottom:20px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>{{ $address }}</span>
                            </div>
                            <div
                                style="display:flex; align-items:center; justify-content:space-between; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); padding:14px; border-radius:14px;">
                                <div>
                                    <div
                                        style="font-size:11px; font-weight:800; text-transform:uppercase; color:rgba(255,255,255,0.6); letter-spacing:.05em;">
                                        {{ $priceLabel }}</div>
                                    <div style="font-size:20px; font-weight:900; color:#fff;">{{ $priceStr }} {{ $vendor->starting_fee > 0 ? 'onwards' : '' }}</div>
                                </div>
                                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                                    @if(!$isOpen)
                                        <div style="font-size: 9px; font-weight: 900; color: #ffab40; text-transform: uppercase;">Closed</div>
                                        <div style="font-size: 11px; font-weight: 700; color: #fff;">Opens At: {{ \Carbon\Carbon::parse($vendor->global_opening_time)->format('h:i A') }}</div>
                                    @else
                                        <div
                                            style="width:40px; height:40px; background:var(--c1); border-radius:50%; display:flex; align-items:center; justify-content:center; color:#000; box-shadow:0 6px 16px rgba(var(--cr),var(--cg),var(--cb),0.4);">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="3"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
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
            <div style="text-align:center; margin-bottom:60px;">
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
                    Join With Bookai Platform
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
        });
    </script>

</x-app-layout>