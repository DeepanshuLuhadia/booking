@php
    $companyName = $settings['company_name'] ?? config('app.name');
    $subjects = [
        'Help with a booking',
        'List my business on the platform',
        'Payments, fees or refunds',
        'Report a problem or abuse',
        'Partnership or press',
        'Feedback and suggestions',
        'Something else',
    ];
    $socials = array_filter([
        'Instagram' => $settings['social_instagram'] ?? '',
        'Facebook'  => $settings['social_facebook'] ?? '',
        'X'         => $settings['social_twitter'] ?? '',
        'LinkedIn'  => $settings['social_linkedin'] ?? '',
    ]);
@endphp

<x-app-layout page-title="Contact Us | {{ $companyName }}">
    <section class="relative pt-36 pb-20 md:pt-44 md:pb-24 overflow-hidden" style="background: linear-gradient(180deg,#0a0f2c 0%,#0d1333 100%);">
        <div style="position:absolute; top:-5%; left:15%; width:520px; height:520px; background:rgba(255,109,0,.08); border-radius:50%; filter:blur(130px); pointer-events:none;"></div>
        <div class="absolute inset-0 z-0 bg-dot-pattern opacity-30"></div>

        <div class="relative z-10 container mx-auto px-4 md:px-8">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 border border-white/20 rounded-full text-white/70 text-[9px] font-black uppercase tracking-widest mb-8">
                    <span class="w-1.5 h-1.5 rounded-full theme-gradient-bg"></span>
                    Contact
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-black text-white tracking-tighter leading-[1.05] italic mb-6">
                    Talk to <span class="theme-gradient-text">a human.</span>
                </h1>
                <p class="text-lg font-medium text-white/70 leading-relaxed">{{ $settings['contact_intro'] }}</p>
            </div>
        </div>
    </section>

    <section class="pb-24 bg-[#0a0f2c]">
        <div class="container mx-auto px-4 md:px-8">
            <div class="grid lg:grid-cols-12 gap-6 lg:gap-8">

                <!-- Form -->
                <div class="lg:col-span-7">
                    <div class="glass-card p-6 md:p-10">
                        @if(session('success'))
                            <div class="mb-8 p-5 rounded-2xl bg-emerald-500/10 border border-emerald-500/25 flex gap-4">
                                <svg class="w-6 h-6 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-sm font-bold text-emerald-300 leading-relaxed">{{ session('success') }}</p>
                            </div>
                        @endif

                        <h2 class="text-2xl font-black text-white italic tracking-tight mb-2">Send us a message</h2>
                        <p class="text-sm text-white/40 font-medium mb-8">{{ $settings['contact_response_time'] }}</p>

                        <form method="POST" action="{{ route('contact.store') }}" class="space-y-6">
                            @csrf

                            {{-- Honeypot: hidden from people, irresistible to bots. --}}
                            <div style="position:absolute;left:-9999px;" aria-hidden="true">
                                <label>Website</label>
                                <input type="text" name="website" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="grid sm:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label for="name" class="block text-[10px] font-black uppercase tracking-[0.2em] text-white/50 ml-1">Your Name <span class="text-rose-400">*</span></label>
                                    <input id="name" type="text" name="name" required maxlength="120" value="{{ old('name', auth()->user()->name ?? '') }}"
                                        class="premium-input w-full h-14 px-5 bg-white/5 border border-white/10 rounded-2xl font-bold text-base placeholder:text-white/30 text-white focus:bg-white/10"
                                        placeholder="Full name">
                                    @error('name')<p class="text-rose-400 text-[10px] font-black uppercase tracking-widest ml-1">{{ $message }}</p>@enderror
                                </div>

                                <div class="space-y-2">
                                    <label for="email" class="block text-[10px] font-black uppercase tracking-[0.2em] text-white/50 ml-1">Email <span class="text-rose-400">*</span></label>
                                    <input id="email" type="email" name="email" required maxlength="190" value="{{ old('email', auth()->user()->email ?? '') }}"
                                        class="premium-input w-full h-14 px-5 bg-white/5 border border-white/10 rounded-2xl font-bold text-base placeholder:text-white/30 text-white focus:bg-white/10"
                                        placeholder="you@example.com">
                                    @error('email')<p class="text-rose-400 text-[10px] font-black uppercase tracking-widest ml-1">{{ $message }}</p>@enderror
                                </div>

                                <div class="space-y-2">
                                    <label for="phone" class="block text-[10px] font-black uppercase tracking-[0.2em] text-white/50 ml-1">Phone <span class="text-white/25">(optional)</span></label>
                                    <input id="phone" type="tel" name="phone" maxlength="20" value="{{ old('phone', auth()->user()->mobile ?? '') }}"
                                        class="premium-input w-full h-14 px-5 bg-white/5 border border-white/10 rounded-2xl font-bold text-base placeholder:text-white/30 text-white focus:bg-white/10"
                                        placeholder="+91 00000 00000">
                                    @error('phone')<p class="text-rose-400 text-[10px] font-black uppercase tracking-widest ml-1">{{ $message }}</p>@enderror
                                </div>

                                <div class="space-y-2">
                                    <label for="subject" class="block text-[10px] font-black uppercase tracking-[0.2em] text-white/50 ml-1">Subject <span class="text-rose-400">*</span></label>
                                    <select id="subject" name="subject" required
                                        class="premium-input w-full h-14 px-5 bg-white/5 border border-white/10 rounded-2xl font-bold text-base text-white focus:bg-white/10 appearance-none">
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject }}" class="bg-[#0d1333]" @selected(old('subject') === $subject)>{{ $subject }}</option>
                                        @endforeach
                                    </select>
                                    @error('subject')<p class="text-rose-400 text-[10px] font-black uppercase tracking-widest ml-1">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="message" class="block text-[10px] font-black uppercase tracking-[0.2em] text-white/50 ml-1">Message <span class="text-rose-400">*</span></label>
                                <textarea id="message" name="message" rows="6" required minlength="10" maxlength="3000"
                                    class="premium-input w-full p-5 bg-white/5 border border-white/10 rounded-2xl font-medium text-base placeholder:text-white/30 text-white focus:bg-white/10 leading-relaxed"
                                    placeholder="Tell us what happened, and include your booking token or business name if it helps.">{{ old('message') }}</textarea>
                                @error('message')<p class="text-rose-400 text-[10px] font-black uppercase tracking-widest ml-1">{{ $message }}</p>@enderror
                            </div>

                            <p class="text-[11px] text-white/30 font-medium leading-relaxed">
                                By sending this message you agree to our
                                <a href="{{ route('privacy') }}" class="text-white/60 underline underline-offset-4 hover:text-white">Privacy Policy</a>.
                                We use your details only to answer you.
                            </p>

                            <button type="submit" class="btn-premium w-full !rounded-2xl !text-sm justify-center">
                                SEND MESSAGE
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Details -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="glass-card p-8">
                        <h2 class="text-xl font-black text-white italic tracking-tight mb-8">Reach us directly</h2>

                        <div class="space-y-7">
                            @if(!empty($settings['company_support_email']))
                                <div class="flex gap-4">
                                    <div class="w-11 h-11 rounded-2xl theme-gradient-bg flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-[9px] font-black uppercase tracking-[0.25em] text-white/30 mb-1">Email Us</div>
                                        <a href="mailto:{{ $settings['company_support_email'] }}" class="text-white font-bold hover:text-[var(--theme-primary)] transition-colors break-all">{{ $settings['company_support_email'] }}</a>
                                    </div>
                                </div>
                            @endif

                            @if(!empty($settings['company_phone']))
                                <div class="flex gap-4">
                                    <div class="w-11 h-11 rounded-2xl bg-white/10 border border-white/10 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-[9px] font-black uppercase tracking-[0.25em] text-white/30 mb-1">Call Us</div>
                                        <a href="tel:{{ $settings['company_phone'] }}" class="text-white font-bold hover:text-[var(--theme-primary)] transition-colors">{{ $settings['company_phone'] }}</a>
                                    </div>
                                </div>
                            @endif

                            @if(!empty($settings['company_whatsapp']))
                                <div class="flex gap-4">
                                    <div class="w-11 h-11 rounded-2xl bg-white/10 border border-white/10 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 21l1.9-3.8A7.9 7.9 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-[9px] font-black uppercase tracking-[0.25em] text-white/30 mb-1">WhatsApp</div>
                                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', $settings['company_whatsapp']) }}" target="_blank" rel="noopener" class="text-white font-bold hover:text-[var(--theme-primary)] transition-colors">{{ $settings['company_whatsapp'] }}</a>
                                    </div>
                                </div>
                            @endif

                            <div class="flex gap-4">
                                <div class="w-11 h-11 rounded-2xl bg-white/10 border border-white/10 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[9px] font-black uppercase tracking-[0.25em] text-white/30 mb-1">Office</div>
                                    <p class="text-white/70 font-medium leading-relaxed">{{ \App\Models\SiteSetting::fullAddress() }}</p>
                                </div>
                            </div>

                            @if(!empty($settings['support_hours']))
                                <div class="flex gap-4">
                                    <div class="w-11 h-11 rounded-2xl bg-white/10 border border-white/10 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-[9px] font-black uppercase tracking-[0.25em] text-white/30 mb-1">Support Hours</div>
                                        <p class="text-white/70 font-medium leading-relaxed">{{ $settings['support_hours'] }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if(!empty($socials))
                            <div class="mt-8 pt-8 border-t border-white/10">
                                <div class="text-[9px] font-black uppercase tracking-[0.25em] text-white/30 mb-4">Follow Us</div>
                                <div class="flex flex-wrap gap-3">
                                    @foreach($socials as $label => $url)
                                        <a href="{{ $url }}" target="_blank" rel="noopener"
                                           class="px-5 py-2.5 rounded-xl bg-white/5 border border-white/10 text-[10px] font-black uppercase tracking-widest text-white/70 hover:text-white hover:bg-white/10 transition-all">{{ $label }}</a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Self-serve answers, so the obvious questions never need an email -->
                    <div class="glass-card p-8">
                        <h3 class="text-lg font-black text-white italic tracking-tight mb-6">Quick answers</h3>
                        <div class="space-y-5">
                            @foreach ([
                                ['q' => 'Where is my booking?', 'a' => 'Every token you hold is listed on the My Bookings page, on the device you booked from.', 'href' => route('bookings.mine'), 'cta' => 'Open My Bookings'],
                                ['q' => 'I want to list my business.', 'a' => 'Registration takes a few minutes and your profile goes live once our team approves it.', 'href' => '/register/vendor', 'cta' => 'Register A Business'],
                                ['q' => 'Can I cancel a booking?', 'a' => 'Yes — open My Bookings and cancel from there. The queue closes the gap automatically.', 'href' => route('bookings.mine'), 'cta' => 'Manage Bookings'],
                            ] as $faq)
                                <div class="pb-5 border-b border-white/5 last:border-0 last:pb-0">
                                    <p class="text-sm font-black text-white mb-1.5">{{ $faq['q'] }}</p>
                                    <p class="text-sm text-white/50 font-medium leading-relaxed mb-3">{{ $faq['a'] }}</p>
                                    <a href="{{ $faq['href'] }}" class="text-[10px] font-black uppercase tracking-widest theme-gradient-text hover:brightness-125">{{ $faq['cta'] }} &rarr;</a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            @if(!empty($settings['contact_map_embed_url']))
                <div class="mt-8 glass-card overflow-hidden">
                    <iframe src="{{ $settings['contact_map_embed_url'] }}" class="w-full h-[400px] border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Office location"></iframe>
                </div>
            @endif
        </div>
    </section>
</x-app-layout>
