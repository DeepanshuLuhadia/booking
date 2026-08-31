@php
    $companyName = $settings['company_name'] ?? config('app.name');
    // The values list is one item per line in the settings screen, and each line
    // may use "Heading — body" so the cards get a title without a second field.
    $values = collect(preg_split('/\r\n|\r|\n/', (string) ($settings['about_values'] ?? '')))
        ->map(fn ($line) => trim($line))
        ->filter()
        ->map(function ($line) {
            $parts = preg_split('/\s+[—–-]\s+/u', $line, 2);
            return [
                'title' => $parts[0] ?? $line,
                'body'  => $parts[1] ?? '',
            ];
        })
        ->values();
@endphp

<x-app-layout page-title="About Us | {{ $companyName }}">
    <style>
        .about-para { color: rgba(255,255,255,0.65); font-size: 1rem; line-height: 1.9; font-weight: 500; }
        .about-para + .about-para { margin-top: 1.25rem; }
    </style>

    <!-- Hero -->
    <section class="relative pt-36 pb-20 md:pt-44 md:pb-28 overflow-hidden" style="background: linear-gradient(180deg,#0a0f2c 0%,#0d1333 100%);">
        <div style="position:absolute; top:-10%; left:20%; width:520px; height:520px; background:rgba(255,109,0,.09); border-radius:50%; filter:blur(130px); pointer-events:none;"></div>
        <div style="position:absolute; bottom:-20%; right:15%; width:600px; height:600px; background:rgba(255,109,0,.05); border-radius:50%; filter:blur(150px); pointer-events:none;"></div>
        <div class="absolute inset-0 z-0 bg-dot-pattern opacity-30"></div>

        <div class="relative z-10 container mx-auto px-4 md:px-8">
            <div class="max-w-4xl">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 border border-white/20 rounded-full text-white/70 text-[9px] font-black uppercase tracking-widest mb-8">
                    <span class="w-1.5 h-1.5 rounded-full theme-gradient-bg"></span>
                    About {{ $companyName }}
                </div>

                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-[4.5rem] font-black text-white tracking-tighter leading-[1.05] md:leading-[0.95] italic mb-8">
                    {{ $settings['about_hero_title'] }}
                </h1>

                <p class="text-lg md:text-xl font-medium text-white/70 max-w-2xl leading-relaxed">
                    {{ $settings['about_hero_subtitle'] }}
                </p>

                <div class="flex flex-wrap gap-4 mt-12">
                    <a href="{{ route('home') }}" class="theme-btn px-8 py-4 rounded-2xl text-[10px]">Explore Businesses</a>
                    <a href="{{ route('contact') }}" class="px-8 py-4 rounded-2xl bg-white/5 border border-white/10 text-white text-[10px] font-black uppercase tracking-widest hover:bg-white/10 transition-all">Talk To Us</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Live platform numbers -->
    <section class="py-14 border-y border-white/5 bg-[#0a0f2c]">
        <div class="container mx-auto px-4 md:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ([
                    ['label' => 'Businesses Listed',   'value' => $stats['vendors']],
                    ['label' => 'Specialists Onboard', 'value' => $stats['specialists']],
                    ['label' => 'Bookings Handled',    'value' => $stats['bookings']],
                    ['label' => 'Service Categories',  'value' => $stats['categories']],
                ] as $stat)
                    <div class="glass-card p-6 md:p-8 text-center">
                        <div class="text-3xl md:text-4xl font-black theme-gradient-text tracking-tighter">
                            {{ number_format($stat['value']) }}{{ $stat['value'] > 0 ? '+' : '' }}
                        </div>
                        <div class="mt-2 text-[9px] font-black uppercase tracking-[0.2em] text-white/40">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Who we are -->
    <section class="py-20 md:py-28 bg-[#0a0f2c]">
        <div class="container mx-auto px-4 md:px-8">
            <div class="grid lg:grid-cols-12 gap-12 lg:gap-16 items-start">
                <div class="lg:col-span-5">
                    <h2 class="text-3xl md:text-4xl font-black text-white tracking-tighter italic leading-tight">
                        Who <span class="theme-gradient-text">we are.</span>
                    </h2>
                    <div class="w-16 h-1 rounded-full theme-gradient-bg mt-6"></div>
                </div>
                <div class="lg:col-span-7">
                    @foreach (preg_split('/\n{2,}/', trim((string) $settings['about_intro'])) as $paragraph)
                        <p class="about-para">{{ trim($paragraph) }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Mission / Vision -->
    <section class="pb-20 md:pb-28 bg-[#0a0f2c]">
        <div class="container mx-auto px-4 md:px-8">
            <div class="grid md:grid-cols-2 gap-6">
                <div class="glass-card p-8 md:p-10">
                    <div class="w-12 h-12 rounded-2xl theme-gradient-bg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-[10px] font-black uppercase tracking-[0.25em] text-white/40 mb-4">Our Mission</h3>
                    <p class="text-lg md:text-xl font-bold text-white leading-relaxed italic">{{ $settings['about_mission'] }}</p>
                </div>
                <div class="glass-card p-8 md:p-10">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 border border-white/10 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </div>
                    <h3 class="text-[10px] font-black uppercase tracking-[0.25em] text-white/40 mb-4">Our Vision</h3>
                    <p class="text-lg md:text-xl font-bold text-white leading-relaxed italic">{{ $settings['about_vision'] }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- What we actually do -->
    <section class="py-20 md:py-24 border-t border-white/5 bg-[#0a0f2c]">
        <div class="container mx-auto px-4 md:px-8">
            <div class="max-w-2xl mb-14">
                <h2 class="text-3xl md:text-4xl font-black text-white tracking-tighter italic leading-tight">
                    What the platform <span class="theme-gradient-text">actually does.</span>
                </h2>
                <p class="about-para mt-5">Two sides, one queue. Here is the whole product in plain language.</p>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div class="glass-card p-8 md:p-10">
                    <h3 class="text-xl font-black text-white italic mb-6">For customers</h3>
                    <ul class="space-y-5">
                        @foreach ([
                            'Find a business near you — by category, distance, rating or who is open right now.',
                            'Book a token or a time slot in seconds, using just your name and phone number. No account, no password.',
                            'Watch your position in the live queue and a realistic estimate of when your turn arrives.',
                            'Get notified on your phone as your turn approaches, and cancel from the same screen if plans change.',
                            'Leave an honest review after the visit — with photos, if that tells the story better.',
                        ] as $item)
                            <li class="flex gap-4">
                                <svg class="w-5 h-5 shrink-0 mt-0.5 theme-gradient-text" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-white/65 font-medium leading-relaxed">{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="glass-card p-8 md:p-10">
                    <h3 class="text-xl font-black text-white italic mb-6">For businesses</h3>
                    <ul class="space-y-5">
                        @foreach ([
                            'A live dashboard that runs the floor: call the next token, mark a visit done, pause bookings when the room is full.',
                            'A profile page and a QR code your walk-in customers can scan at the counter to join the queue themselves.',
                            'Individual logins for your specialists, each with their own queue, timings and break status.',
                            'Pricing, service fees and working hours that stay entirely under your control.',
                            'Booking reports you can filter and export, so you know what a week really looked like.',
                        ] as $item)
                            <li class="flex gap-4">
                                <svg class="w-5 h-5 shrink-0 mt-0.5 theme-gradient-text" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-white/65 font-medium leading-relaxed">{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Story -->
    <section class="py-20 md:py-28 border-t border-white/5 bg-[#0a0f2c]">
        <div class="container mx-auto px-4 md:px-8">
            <div class="grid lg:grid-cols-12 gap-12 lg:gap-16 items-start">
                <div class="lg:col-span-5">
                    <h2 class="text-3xl md:text-4xl font-black text-white tracking-tighter italic leading-tight">
                        How it <span class="theme-gradient-text">started.</span>
                    </h2>
                    <div class="w-16 h-1 rounded-full theme-gradient-bg mt-6"></div>
                    @if(!empty($settings['company_founded_year']))
                        <p class="mt-6 text-[10px] font-black uppercase tracking-[0.25em] text-white/30">Since {{ $settings['company_founded_year'] }}</p>
                    @endif
                </div>
                <div class="lg:col-span-7">
                    @foreach (preg_split('/\n{2,}/', trim((string) $settings['about_story'])) as $paragraph)
                        <p class="about-para">{{ trim($paragraph) }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Values -->
    @if($values->isNotEmpty())
        <section class="pb-20 md:pb-28 bg-[#0a0f2c]">
            <div class="container mx-auto px-4 md:px-8">
                <h2 class="text-3xl md:text-4xl font-black text-white tracking-tighter italic leading-tight mb-12">
                    What we <span class="theme-gradient-text">stand by.</span>
                </h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($values as $index => $value)
                        <div class="glass-card p-7 h-full">
                            <div class="text-[10px] font-black uppercase tracking-[0.25em] text-white/25 mb-4">0{{ $index + 1 }}</div>
                            <h3 class="text-base font-black text-white italic leading-snug mb-3">{{ $value['title'] }}</h3>
                            @if($value['body'])
                                <p class="text-sm text-white/50 font-medium leading-relaxed">{{ $value['body'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Where to find us + CTA -->
    <section class="pb-24 bg-[#0a0f2c]">
        <div class="container mx-auto px-4 md:px-8">
            <div class="glass-card p-8 md:p-14">
                <div class="grid lg:grid-cols-2 gap-12">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-black text-white tracking-tighter italic mb-6">Where to find us</h2>
                        <div class="space-y-5">
                            <div>
                                <div class="text-[9px] font-black uppercase tracking-[0.25em] text-white/30 mb-1.5">Registered Name</div>
                                <div class="text-white font-bold">{{ $settings['company_legal_name'] ?: $companyName }}</div>
                            </div>
                            <div>
                                <div class="text-[9px] font-black uppercase tracking-[0.25em] text-white/30 mb-1.5">Address</div>
                                <div class="text-white/70 font-medium leading-relaxed">{{ \App\Models\SiteSetting::fullAddress() }}</div>
                            </div>
                            @if(!empty($settings['company_email']))
                                <div>
                                    <div class="text-[9px] font-black uppercase tracking-[0.25em] text-white/30 mb-1.5">Email</div>
                                    <a href="mailto:{{ $settings['company_email'] }}" class="text-white/70 font-medium hover:text-[var(--theme-primary)] transition-colors break-all">{{ $settings['company_email'] }}</a>
                                </div>
                            @endif
                            @if(!empty($settings['company_phone']))
                                <div>
                                    <div class="text-[9px] font-black uppercase tracking-[0.25em] text-white/30 mb-1.5">Phone</div>
                                    <a href="tel:{{ $settings['company_phone'] }}" class="text-white/70 font-medium hover:text-[var(--theme-primary)] transition-colors">{{ $settings['company_phone'] }}</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="lg:border-l lg:border-white/10 lg:pl-12 flex flex-col justify-center">
                        <h3 class="text-2xl md:text-3xl font-black text-white tracking-tighter italic mb-4">Run a business that keeps people waiting?</h3>
                        <p class="about-para mb-8">List it in a few minutes, hand your customers a live queue instead of a plastic chair, and get your counter back.</p>
                        <div class="flex flex-wrap gap-4">
                            <a href="/register/vendor" class="theme-btn px-8 py-4 rounded-2xl text-[10px]">Become A Provider</a>
                            <a href="{{ route('contact') }}" class="px-8 py-4 rounded-2xl bg-white/5 border border-white/10 text-white text-[10px] font-black uppercase tracking-widest hover:bg-white/10 transition-all">Contact Support</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
