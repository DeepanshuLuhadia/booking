<x-app-layout page-title="Book Verified Experts | Professional Appointments">
    <!-- Hero Section -->
    <section class="relative pt-20 pb-32 px-6 overflow-hidden">
        <!-- Floating Backdrop Elements -->
        <div class="hero-floating-elements">
            <span class="floating-icon animate-float-1 animate-drift-1" style="top: 15%; left: 5%;">🏥</span>
            <span class="floating-icon animate-float-2 animate-drift-2" style="top: 25%; left: 85%;">💇</span>
            <span class="floating-icon animate-float-3 animate-drift-1" style="top: 65%; left: 12%;">⚡</span>
            <span class="floating-icon animate-float-1 animate-drift-2" style="top: 75%; left: 78%;">💼</span>
            <span class="floating-icon animate-float-2 animate-drift-1" style="top: 45%; left: 92%;">🎓</span>
            <span class="floating-icon animate-float-3 animate-drift-2" style="top: 10%; left: 40%;">✨</span>
            <span class="floating-icon animate-float-1 animate-drift-1" style="top: 85%; left: 35%;">🏆</span>
            <span class="floating-icon animate-float-2 animate-drift-2" style="top: 55%; left: 55%;">⚕️</span>
            <span class="floating-icon animate-float-3 animate-drift-1" style="top: 20%; left: 65%;">📘</span>
            <span class="floating-icon animate-float-1 animate-drift-2" style="top: 40%; left: 15%;">🖊️</span>
            
            <!-- Secondary smaller icons -->
            <span class="floating-icon animate-float-2 animate-drift-1 opacity-5 scale-75" style="top: 30%; left: 25%;">🏥</span>
            <span class="floating-icon animate-float-3 animate-drift-2 opacity-5 scale-75" style="top: 70%; left: 60%;">💇</span>
            <span class="floating-icon animate-float-1 animate-drift-1 opacity-5 scale-75" style="top: 10%; left: 80%;">⚡</span>
            <span class="floating-icon animate-float-2 animate-drift-2 opacity-5 scale-75" style="top: 90%; left: 10%;">💼</span>
            <span class="floating-icon animate-float-3 animate-drift-1 opacity-5 scale-75" style="top: 50%; left: 3%;">🎓</span>
        </div>

        <div class="container mx-auto relative z-10 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white text-xs font-bold uppercase tracking-widest mb-8 animate-fade-in">
                <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                Multi-Vendor Appointment Platform
            </div>

            <h1 class="text-5xl md:text-7xl font-black text-white leading-tight mb-6 tracking-tighter">
                Book Verified <span class="text-orange-500 italic">Experts</span><br>
                In Your City
            </h1>

            <p class="text-white/60 text-lg md:text-xl max-w-2xl mx-auto mb-12 font-medium">
                Personalized platform to find top-rated professionals near you.
            </p>

            <!-- Premium Search Matrix -->
            <div class="max-w-4xl mx-auto mb-16 px-4">
                <div class="premium-search-container p-2 md:p-3 bg-white/10 backdrop-blur-2xl border border-white/20 rounded-[2.5rem] shadow-2xl">
                    <form action="{{ route('home') }}" method="GET" class="flex flex-col md:flex-row items-center w-full gap-2">
                        @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
                        <div class="flex-grow flex items-center gap-4 px-6 py-3 min-w-0">
                            <svg class="w-6 h-6 text-orange-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Search by name, address, or specialist..." 
                                   class="bg-transparent border-none text-white placeholder-white/40 focus:ring-0 w-full text-lg font-medium outline-none">
                        </div>
                        <div class="h-10 w-px bg-white/10 hidden md:block"></div>
                        <div class="px-4 flex items-center gap-3 shrink-0">
                            <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <select name="filter" class="bg-transparent border-none text-white font-bold cursor-pointer focus:ring-0 outline-none w-32 appearance-none">
                                <option value="" class="text-slate-900 font-bold">All Status</option>
                                <option value="open_now" class="text-slate-900 font-bold" {{ request('filter') == 'open_now' ? 'selected' : '' }}>Open Now</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full md:w-auto bg-orange-500 hover:bg-orange-600 text-white font-black px-10 py-4 rounded-[2rem] transition-all transform hover:scale-105 active:scale-95 shadow-xl shadow-orange-500/20">
                            Search Now
                        </button>
                    </form>
                </div>

                <!-- Domain Chips -->
                <div class="flex flex-wrap items-center justify-center gap-4 mt-8">
                    <a href="{{ request()->fullUrlWithQuery(['type' => '', 'filter' => request('filter')]) }}" 
                       class="px-6 py-3 rounded-full {{ !request('type') ? 'bg-orange-500 text-white' : 'bg-white/5 border border-white/10 text-white hover:bg-white/10' }} transition-colors text-xs font-bold uppercase tracking-widest">
                        All Services
                    </a>
                    @foreach($allThemes as $key => $t)
                        <a href="{{ request()->fullUrlWithQuery(['type' => $key, 'filter' => request('filter')]) }}" 
                           class="flex items-center gap-3 px-6 py-3 rounded-full {{ request('type') == $key ? 'bg-orange-500 text-white' : 'bg-white/5 border border-white/10 text-white hover:bg-white/10' }} transition-colors group">
                            <span class="text-lg shrink-0 group-hover:scale-125 transition-transform">{{ $t['emoji'] ?? $t['icon'] }}</span>
                            <span class="text-xs font-bold uppercase tracking-widest">{{ $t['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-8 max-w-5xl mx-auto pt-16 border-t border-white/5">
                <div class="stat-pill">
                    <span class="stat-value">80k+</span>
                    <span class="stat-label">Happy Clients</span>
                </div>
                <div class="stat-pill">
                    <span class="stat-value">500+</span>
                    <span class="stat-label">Cities</span>
                </div>
                <div class="stat-pill">
                    <span class="stat-value">1.2M</span>
                    <span class="stat-label">Appointments</span>
                </div>
                <div class="stat-pill">
                    <span class="stat-value">4.9<span class="text-orange-500 text-sm ml-1">★</span></span>
                    <span class="stat-label">Average Rated</span>
                </div>
                <div class="stat-pill">
                    <span class="stat-value">4.9<span class="text-orange-500 text-sm ml-1">★</span></span>
                    <span class="stat-label">Top Experts</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Recommended Section -->
    <section class="py-24 px-6 bg-slate-900/40">
        <div class="container mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-black text-white mb-4">Recommended <span class="text-orange-500">Professionals</span></h2>
                <p class="text-white/40 font-bold uppercase tracking-widest text-[10px]">Handpicked specialists for your needs</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                @forelse($vendors as $vendor)
                    @php 
                        $vType = $vendor->category?->slug ?? 'consultant';
                        $vTheme = $allThemes[$vType] ?? $allThemes['consultant'];
                        $isOpen = $vendor->is_currently_open;
                    @endphp
                        <div class="glass-card group h-full flex flex-col overflow-hidden border-white/10 rounded-[2rem] p-3 shadow-2xl transition-all duration-300 {{ $isOpen ? 'bg-white/5 hover:border-white/30' : 'bg-black/40 grayscale opacity-60' }}">
                            <div class="h-56 bg-gradient-to-br from-theme-primary/10 to-theme-accent/5 rounded-[1.5rem] relative overflow-hidden">
                                <div class="absolute inset-0 flex items-center justify-center text-7xl group-hover:scale-110 transition-transform duration-700">
                                    {{ $vTheme['icon'] }}
                                </div>
                                <div class="absolute top-5 right-5 {{ $isOpen ? 'bg-emerald-500' : 'bg-slate-500' }} text-white text-[9px] font-black px-4 py-1.5 rounded-full shadow-lg">
                                    {{ $isOpen ? 'OPEN NOW' : 'CLOSED' }}
                                </div>
                                <div class="absolute bottom-5 left-5">
                                    <span class="px-4 py-1.5 bg-white/20 backdrop-blur-md border border-white/30 rounded-lg text-[9px] font-black uppercase tracking-widest text-white">
                                        {{ $vTheme['label'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-6 flex-grow flex flex-col">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="w-full">
                                        <h3 class="text-xl font-black text-white mb-2 tracking-tighter truncate">{{ $vendor->business_name }}</h3>
                                        <!-- Highlighted Speciality -->
                                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg {{ $isOpen ? 'bg-orange-500/10 text-orange-500 border-orange-500/20' : 'bg-slate-500/10 text-slate-400 border-slate-500/20' }} text-[9px] font-black uppercase tracking-widest border">
                                            @if($isOpen)
                                            <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                                            @endif
                                            {{ $vTheme['label'] }} Specialist
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2 mb-8 text-white/40 text-[11px] font-medium tracking-tight">
                                    <svg class="w-4 h-4 {{ $isOpen ? 'text-orange-500' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span class="truncate">{{ $vendor->address ?? 'Main Branch, Downtown' }}</span>
                                </div>

                                <div class="mt-auto pt-6 border-t border-white/5 flex items-center justify-between">
                                    <div class="flex flex-col">
                                        <span class="text-[9px] font-black uppercase tracking-widest text-white/30 mb-0.5">Appointment Rate</span>
                                        <span class="text-xl font-black text-white italic">₹{{ number_format($vendor->service_fee) }}</span>
                                    </div>
                                    <a href="{{ route('vendor.show', $vendor->slug) }}" 
                                       class="btn-premium w-14 h-14 rounded-2xl flex items-center justify-center p-0 hover:scale-110 active:scale-95 shadow-lg {{ $isOpen ? 'shadow-orange-500/20' : '' }}">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                @empty
                    <div class="col-span-full py-40 text-center">
                        <div class="text-8xl mb-8 opacity-20">📭</div>
                        <h3 class="text-3xl font-black text-white mb-4 italic tracking-tighter uppercase">No Experts Cataloged</h3>
                        <p class="text-white/40 font-medium tracking-tight">Try adjusting your filtration criteria or reset the search matrix.</p>
                        <a href="{{ route('home') }}" class="btn-premium px-12 h-16 rounded-2xl mt-12">RESET PARAMETERS</a>
                    </div>
                @endforelse
            </div>
            
            @if($vendors->hasPages())
                <div class="mt-20">
                    {{ $vendors->links() }}
                </div>
            @endif
        </div>
    </section>

    <!-- 3 Steps Section -->
    <section class="py-32 px-6">
        <div class="container mx-auto">
            <div class="text-center mb-24">
                <h2 class="text-5xl font-black text-white mb-6 tracking-tighter">Book in <span class="text-orange-500 italic">3 Easy Steps</span></h2>
                <p class="text-white/30 font-bold uppercase tracking-widest text-[10px]">Simple. Fast. Professional.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-16 relative">
                <!-- Abstract lines for flow hint -->
                <div class="hidden md:block absolute top-1/2 left-0 w-full h-px bg-white/5 -translate-y-1/2 z-0"></div>

                <div class="step-card relative z-10">
                    <div class="w-20 h-20 rounded-3xl bg-orange-500/10 text-orange-500 flex items-center justify-center mx-auto mb-10 text-4xl border border-orange-500/20 shadow-2xl shadow-orange-500/10">
                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <h3 class="text-2xl font-black text-white mb-4 uppercase tracking-tighter italic">FIND & FILTER</h3>
                    <p class="text-white/40 text-sm font-medium leading-relaxed max-w-[240px] mx-auto">Search for top-tier professionals in your area that fullfill your needs.</p>
                </div>

                <div class="step-card relative z-10">
                    <div class="w-20 h-20 rounded-3xl bg-orange-500/10 text-orange-500 flex items-center justify-center mx-auto mb-10 text-4xl border border-orange-500/20 shadow-2xl shadow-orange-500/10">
                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.921-.755 1.688-1.54 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.784.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </div>
                    <h3 class="text-2xl font-black text-white mb-4 uppercase tracking-tighter italic">CHOOSE EASY</h3>
                    <p class="text-white/40 text-sm font-medium leading-relaxed max-w-[240px] mx-auto">See detailed ratings and reviews, Then Book the best instantly.</p>
                </div>

                <div class="step-card relative z-10">
                    <div class="w-20 h-20 rounded-3xl bg-orange-500/10 text-orange-500 flex items-center justify-center mx-auto mb-10 text-4xl border border-orange-500/20 shadow-2xl shadow-orange-500/10">
                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-2xl font-black text-white mb-4 uppercase tracking-tighter italic">CONFIRM GO</h3>
                    <p class="text-white/40 text-sm font-medium leading-relaxed max-w-[240px] mx-auto">Get instant confirmation and reminders for your professional appointment.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Business CTA -->
    <section class="py-32 px-6 bg-orange-500/5 relative overflow-hidden">
        <div class="container mx-auto text-center relative z-10">
            <p class="text-orange-500 font-black uppercase tracking-[0.4em] text-[10px] mb-6">Partner with global platform</p>
            <h2 class="text-5xl md:text-7xl font-black text-white mb-10 tracking-tighter leading-tight">
                GROW YOUR <span class="italic text-orange-500">BUSINESS</span> WITH US
            </h2>
            <p class="text-white/50 mb-16 max-w-2xl mx-auto font-medium text-lg">Are you a professional? Join us to get more bookings and grow your client base with our advanced tools.</p>
            <a href="/register/vendor" class="btn-premium px-16 py-6 rounded-3xl text-sm shadow-2xl shadow-orange-500/30">
                Join as a Professional
                <svg class="w-5 h-5 ml-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            </a>
        </div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-orange-500/5 blur-[120px] rounded-full"></div>
    </section>
</x-app-layout>
