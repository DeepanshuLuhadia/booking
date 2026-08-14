@props([
    'title',
    'accent' => '',
    'badge' => 'Legal',
    'intro' => null,
    'effectiveDate' => null,
])

{{--
    Shared shell for the Terms and Privacy pages: identical hero, typography and
    document chrome, so the two documents can never drift apart visually. The
    document body is plain HTML in the slot, styled by the rules below.
--}}
<x-app-layout :page-title="$title . ' | ' . (\App\Models\SiteSetting::get('company_name') ?? config('app.name'))">
    <style>
        .legal-doc { color: rgba(255,255,255,0.62); font-size: 0.975rem; line-height: 1.85; font-weight: 500; }
        .legal-doc > section { scroll-margin-top: 7rem; padding-bottom: 2.5rem; }
        .legal-doc > section + section { border-top: 1px solid rgba(255,255,255,0.06); padding-top: 2.5rem; }
        .legal-doc h2 {
            color: #fff; font-size: 1.35rem; font-weight: 900; font-style: italic;
            letter-spacing: -0.02em; margin-bottom: 1.1rem; line-height: 1.3;
        }
        .legal-doc h3 {
            color: rgba(255,255,255,0.9); font-size: 1rem; font-weight: 800;
            margin: 1.75rem 0 0.65rem; letter-spacing: -0.01em;
        }
        .legal-doc p + p { margin-top: 1rem; }
        .legal-doc ul, .legal-doc ol { margin: 1rem 0 0; padding-left: 1.35rem; }
        .legal-doc ul { list-style: disc; }
        .legal-doc ol { list-style: decimal; }
        .legal-doc li { margin-bottom: 0.7rem; padding-left: 0.35rem; }
        .legal-doc li::marker { color: var(--theme-primary, #ff8c42); }
        .legal-doc strong { color: rgba(255,255,255,0.92); font-weight: 800; }
        .legal-doc a { color: rgba(255,255,255,0.9); text-decoration: underline; text-underline-offset: 4px; }
        .legal-doc a:hover { color: var(--theme-primary, #ff8c42); }
        .legal-doc .callout {
            background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 1.25rem; padding: 1.35rem 1.5rem; margin-top: 1.25rem;
        }
        .legal-toc a {
            display: block; padding: 0.55rem 0.9rem; border-radius: 0.75rem;
            font-size: 0.8rem; font-weight: 700; color: rgba(255,255,255,0.5);
            text-decoration: none; transition: all .2s;
        }
        .legal-toc a:hover { background: rgba(255,255,255,0.06); color: #fff; }
    </style>

    <!-- Hero -->
    <section class="relative pt-36 pb-16 md:pt-44 md:pb-20 overflow-hidden" style="background: linear-gradient(180deg,#0a0f2c 0%,#0d1333 100%);">
        <div style="position:absolute; top:-10%; left:25%; width:500px; height:500px; background:rgba(255,109,0,.07); border-radius:50%; filter:blur(130px); pointer-events:none;"></div>
        <div class="absolute inset-0 z-0 bg-dot-pattern opacity-25"></div>

        <div class="relative z-10 container mx-auto px-4 md:px-8">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 border border-white/20 rounded-full text-white/70 text-[9px] font-black uppercase tracking-widest mb-8">
                    <span class="w-1.5 h-1.5 rounded-full theme-gradient-bg"></span>
                    {{ $badge }}
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-black text-white tracking-tighter leading-[1.05] italic mb-6">
                    {{ $title }}@if($accent) <span class="theme-gradient-text">{{ $accent }}</span>@endif
                </h1>
                @if($intro)
                    <p class="text-base md:text-lg font-medium text-white/65 leading-relaxed">{{ $intro }}</p>
                @endif
                @if($effectiveDate)
                    <p class="mt-8 text-[10px] font-black uppercase tracking-[0.25em] text-white/30">Effective from {{ $effectiveDate }}</p>
                @endif
            </div>
        </div>
    </section>

    <section class="pb-24 bg-[#0a0f2c]">
        <div class="container mx-auto px-4 md:px-8">
            <div class="grid lg:grid-cols-12 gap-8">
                @isset($toc)
                    <aside class="lg:col-span-3 hidden lg:block">
                        <div class="sticky top-28 glass-card p-4 legal-toc max-h-[calc(100vh-9rem)] overflow-y-auto no-scrollbar">
                            <div class="text-[9px] font-black uppercase tracking-[0.25em] text-white/30 px-3 py-2 mb-1">On this page</div>
                            {{ $toc }}
                        </div>
                    </aside>
                @endisset

                <div class="{{ isset($toc) ? 'lg:col-span-9' : 'lg:col-span-12 max-w-4xl' }}">
                    <div class="glass-card p-6 md:p-12">
                        <div class="legal-doc">
                            {{ $slot }}
                        </div>
                    </div>

                    <div class="mt-8 glass-card p-6 md:p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
                        <div>
                            <p class="text-white font-black italic text-lg">Still unclear about something?</p>
                            <p class="text-sm text-white/45 font-medium mt-1">Ask us — we would rather explain it than have you guess.</p>
                        </div>
                        <a href="{{ route('contact') }}" class="theme-btn px-8 py-4 rounded-2xl text-[10px] shrink-0">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
