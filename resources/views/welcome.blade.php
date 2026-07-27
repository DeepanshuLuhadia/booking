<x-app-layout>
    <!-- Hero Section -->
    <section class="relative pt-20 pb-32 px-6 overflow-hidden">
        <div class="container mx-auto relative z-10 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white text-xs font-bold uppercase tracking-widest mb-8 animate-fade-in">
                <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                Multi-Vendor Appointment Platform
            </div>

            @php
                $hour = now()->hour;
                $isWeekend = now()->isWeekend();
                
                if ($hour >= 5 && $hour < 12) {
                    $greeting = "Good Morning!";
                    $action = "Need a fresh start today?";
                } elseif ($hour >= 12 && $hour < 17) {
                    $greeting = "Good Afternoon!";
                    $action = "Take some time for yourself.";
                } elseif ($hour >= 17 && $hour < 22) {
                    $greeting = "Good Evening!";
                    $action = "Unwind with top professionals.";
                } else {
                    $greeting = "Discover & Book";
                    $action = "Connect with trusted experts.";
                }
                
                if ($isWeekend) {
                    $greeting = "Happy Weekend!";
                    $action = "Perfect time for self-care and sports.";
                }
            @endphp

            <h1 class="text-4xl sm:text-5xl md:text-7xl font-black text-white leading-[1.1] mb-6 tracking-tighter">
                {{ $greeting }}<br class="hidden sm:block">
                Book Verified <span class="text-orange-500 italic">Experts</span>
            </h1>

            <p class="text-white/60 text-lg md:text-xl max-w-2xl mx-auto mb-12 font-medium">
                {{ $action }} The personalized platform to find top-rated professionals in your city.
            </p>

            <!-- Premium Search Matrix -->
            <div class="max-w-4xl mx-auto mb-16">
                <div class="premium-search-container p-2 md:p-3 bg-white/10 backdrop-blur-2xl border border-white/20 rounded-3xl md:rounded-[2.5rem] shadow-2xl">
                    <div class="flex flex-col md:flex-row items-center w-full gap-2">
                        <div class="w-full flex items-center gap-3 md:gap-4 px-4 md:px-6 py-2 md:py-3 min-w-0">
                            <svg class="w-5 h-5 md:w-6 md:h-6 text-orange-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" placeholder="Search experts..." 
                                   class="bg-transparent border-none text-white placeholder-white/40 focus:ring-0 w-full text-base md:text-lg font-medium">
                        </div>
                        <div class="h-10 w-px bg-white/10 hidden md:block"></div>
                        <div class="w-full md:w-auto px-4 md:px-6 py-2 md:py-0 flex items-center gap-3 shrink-0 border-t border-white/5 md:border-none">
                            <svg class="w-5 h-5 text-orange-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="text-white font-bold text-sm md:text-base truncate">New York, NY</span>
                        </div>
                        <button class="w-full md:w-auto bg-orange-500 hover:bg-orange-600 text-white font-black px-10 py-4 rounded-2xl md:rounded-[2rem] transition-all transform hover:scale-105 active:scale-95 shadow-xl shadow-orange-500/20 text-sm md:text-base">
                            Search Now
                        </button>
                    </div>
                </div>

                <!-- Quick Categories -->
                <div class="flex flex-wrap justify-center gap-2 md:gap-4 mt-8">
                    @foreach(\App\Services\ThemeService::getAllThemes() as $key => $theme)
                        <a href="{{ route('home', ['type' => $key]) }}" 
                           class="flex items-center gap-2 md:gap-3 px-4 md:px-6 py-2 md:py-3 rounded-full bg-white/5 border border-white/10 text-white hover:bg-white/10 transition-colors group">
                            <span class="text-lg md:text-xl shrink-0 group-hover:scale-125 transition-transform">{{ $theme['emoji'] ?? $theme['icon'] }}</span>
                            <span class="text-[11px] md:text-sm font-bold">{{ $theme['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Stats Bar -->
            @php
                $totalClients = \App\Models\Booking::distinct('customer_id')->count('customer_id');
                $totalCities = \App\Models\Vendor::distinct('address')->count('address');
                $totalAppointments = \App\Models\Booking::count();
                $avgRating = 4.9; // rating system not yet implemented
            @endphp
            @if($totalClients > 0 || $totalCities > 0 || $totalAppointments > 0)
            <div class="grid grid-cols-2 md:grid-cols-5 gap-8 max-w-5xl mx-auto pt-12 border-t border-white/5">
                <div class="stat-pill">
                    <span class="stat-value" data-counter data-target="{{ $totalClients }}" data-suffix="+">0</span>
                    <span class="stat-label">Happy Clients</span>
                </div>
                <div class="stat-pill">
                    <span class="stat-value" data-counter data-target="{{ $totalCities }}" data-suffix="+">0</span>
                    <span class="stat-label">Cities</span>
                </div>
                <div class="stat-pill">
                    <span class="stat-value" data-counter data-target="{{ $totalAppointments }}" data-suffix="+" data-decimals="0">0</span>
                    <span class="stat-label">Appointments</span>
                </div>
                <div class="stat-pill">
                    <span class="stat-value"><span data-counter data-target="{{ number_format($avgRating, 1) }}" data-decimals="1">0</span><svg class="w-4 h-4 inline ml-1 text-orange-500 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg></span>
                    <span class="stat-label">Average Rated</span>
                </div>
                <div class="stat-pill">
                    <span class="stat-value"><span data-counter data-target="100" data-decimals="0" data-suffix="%">0</span><svg class="w-4 h-4 inline ml-1 text-orange-500 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg></span>
                    <span class="stat-label">Top Experts</span>
                </div>
            </div>
            @endif
        </div>

        <!-- Abstract Background Glows -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[600px] bg-theme-primary/10 blur-[120px] rounded-full pointer-events-none"></div>
    </section>

    <!-- Recommended Section -->
    <section class="py-24 px-6 bg-slate-900/40">
        <div class="container mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-black text-white mb-4">Recommended <span class="text-orange-500">Professionals</span></h2>
                <p class="text-white/40 font-bold uppercase tracking-widest text-xs">Handpicked specialists for your needs</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($vendors as $vendor)
                    <div class="glass-card group h-full flex flex-col overflow-hidden bg-white/5 border-white/10">
                        <div class="h-48 bg-gradient-to-br from-theme-primary/20 to-theme-accent/20 relative overflow-hidden">
                            <div class="absolute inset-0 flex items-center justify-center text-6xl group-hover:scale-110 transition-transform duration-500">
                                {{ $vendor->themeConfig['icon'] }}
                            </div>
                            <div class="absolute top-4 right-4 bg-orange-500 text-white text-[10px] font-black px-3 py-1 rounded-full shadow-lg">
                                TOP RATED
                            </div>
                        </div>
                        <div class="p-8 flex-grow">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-white mb-1">{{ $vendor->name }}</h3>
                                    <p class="text-xs font-bold text-orange-500 uppercase tracking-widest">{{ $vendor->themeConfig['label'] }}</p>
                                </div>
                                <div class="px-3 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 text-[10px] font-black border border-emerald-500/20">
                                    AVAILABLE
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2 mb-6 text-white/40 text-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $vendor->address ?? 'Main Branch, Downtown' }}</span>
                            </div>

                            <a href="{{ route('vendor.details', $vendor->slug) }}" 
                               class="btn-premium w-full justify-center py-4 rounded-2xl group-hover:bg-orange-600 transition-colors">
                                Book Instantly
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-16 text-center">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 text-white/40 hover:text-white font-black uppercase tracking-widest text-xs transition-colors group">
                    View All Experts
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- 3 Steps Section -->
    <section class="py-24 px-6 relative overflow-hidden">
        <div class="container mx-auto relative z-10">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-black text-white mb-4">Book in <span class="text-orange-500">3 Easy Steps</span></h2>
                <p class="text-white/40 font-bold uppercase tracking-widest text-xs">Simple. Fast. Reliable.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <div class="step-card">
                    <div class="w-16 h-16 rounded-2xl bg-orange-500/10 text-orange-500 flex items-center justify-center mx-auto mb-8 text-3xl font-black border border-orange-500/20">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4 uppercase tracking-tighter">Find & Filter</h3>
                    <p class="text-white/40 text-sm font-medium leading-relaxed">Search for professionals in your area that fullfill your needs.</p>
                </div>

                <div class="step-card">
                    <div class="w-16 h-16 rounded-2xl bg-orange-500/10 text-orange-500 flex items-center justify-center mx-auto mb-8 text-3xl font-black border border-orange-500/20">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.921-.755 1.688-1.54 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.784.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4 uppercase tracking-tighter">Choose Easy</h3>
                    <p class="text-white/40 text-sm font-medium leading-relaxed">See detailed ratings and reviews, Book the best instantly.</p>
                </div>

                <div class="step-card">
                    <div class="w-16 h-16 rounded-2xl bg-orange-500/10 text-orange-500 flex items-center justify-center mx-auto mb-8 text-3xl font-black border border-orange-500/20">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4 uppercase tracking-tighter">Confirm & Go</h3>
                    <p class="text-white/40 text-sm font-medium leading-relaxed">Get instant confirmation and reminders for your appointment.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Business CTA -->
    <section class="py-24 px-6 bg-theme-primary/10">
        <div class="container mx-auto text-center">
            <p class="text-orange-500 font-black uppercase tracking-[0.2em] text-xs mb-4">Partner with us</p>
            <h2 class="text-4xl md:text-5xl font-black text-white mb-8 tracking-tighter">
                GROW YOUR <span class="italic text-orange-500">BUSINESS</span> WITH US
            </h2>
            <p class="text-white/60 mb-12 max-w-xl mx-auto font-medium">Are you a professional? Join us to get more bookings and grow your client base.</p>
            <a href="/register/vendor" class="btn-premium px-12 py-5 rounded-2xl text-base">
                Join as a Professional
                <svg class="w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </a>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const counters = document.querySelectorAll('[data-counter]');
            
            const animateCounter = (el) => {
                const target = parseFloat(el.dataset.target);
                const duration = 2000; // 2 seconds
                const decimals = parseInt(el.dataset.decimals) || 0;
                const suffix = el.dataset.suffix || '';
                const startTime = performance.now();

                function update(now) {
                    const elapsed = now - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    
                    // Easing function: easeOutQuart
                    const easeProgress = 1 - Math.pow(1 - progress, 4);
                    
                    const current = easeProgress * target;
                    el.innerText = current.toFixed(decimals) + suffix;

                    if (progress < 1) {
                        requestAnimationFrame(update);
                    } else {
                        el.innerText = target.toFixed(decimals) + suffix;
                    }
                }
                requestAnimationFrame(update);
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { 
                threshold: 0.1, 
                rootMargin: '0px 0px -50px 0px' 
            });

            counters.forEach(counter => observer.observe(counter));
        });
    </script>
</x-app-layout>
