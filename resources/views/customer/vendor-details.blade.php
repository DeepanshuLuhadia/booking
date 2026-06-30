<x-app-layout :vendor-theme="$theme" :page-title="$vendor->business_name">
    <div x-data="bookingSystem()"
        class="relative min-h-screen text-white vendor-theme--{{ strtolower(str_replace(' ', '-', $theme['label'] ?? 'default')) }}">

        <!-- PROFILE HERO -->
        <section class="relative z-10 pt-28 pb-10 px-5 md:pt-32 md:pb-16 md:px-6">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center gap-6 md:gap-12">
                <!-- Left: Profile Avatar -->
                <div class="relative group shrink-0">
                    <div
                        class="w-40 h-40 sm:w-56 sm:h-56 md:w-80 md:h-80 rounded-[2rem] md:rounded-[3rem] overflow-hidden theme-glow-border transition-transform duration-1000 group-hover:scale-105 mx-auto">
                        @php
                            $vType = $vendor->category?->slug ?? 'consultant';
                            if ($vendor->shop_photo) {
                                $img = asset('storage/' . $vendor->shop_photo);
                            } elseif (in_array($vType, ['health', 'doctor'])) {
                                $img = asset('images/placeholders/health.svg');
                            } elseif (in_array($vType, ['beauty', 'barber'])) {
                                $img = asset('images/placeholders/beauty.svg');
                            } elseif (in_array($vType, ['sports', 'activity'])) {
                                $img = asset('images/placeholders/sports.svg');
                            } elseif ($vType === 'training') {
                                $img = asset('images/placeholders/training.svg');
                            } else {
                                $img = asset('images/placeholders/default.svg');
                            }
                        @endphp
                        <img src="{{ $img }}"
                            class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110"
                            alt="{{ $vendor->business_name }}">
                    </div>
                </div>

                <!-- Right: Business Credentials -->
                <div class="flex-grow w-full min-w-0 text-center md:text-left animate-text-reveal">
                    <div
                        class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 border border-white/20 backdrop-blur-xl rounded-full text-white/50 text-[9px] font-black uppercase tracking-widest mb-4 md:mb-6">
                        <span class="w-2 h-2 rounded-full theme-gradient-bg animate-pulse"></span> Verified Appointment
                        Registry
                    </div>

                    <h1
                        class="text-4xl sm:text-5xl md:text-[5.5rem] font-black text-white mb-4 md:mb-6 tracking-tighter leading-[0.95] md:leading-[0.9] italic break-words">
                        {{ $vendor->business_name }}
                        @if($vendor->is_verified)
                        <span class="inline-flex items-center gap-1 align-middle text-[10px] not-italic font-black uppercase tracking-widest text-sky-300 bg-sky-500/10 border border-sky-400/20 px-2.5 py-1 rounded-full">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"></path></svg>
                            Verified
                        </span>
                        @endif
                    </h1>

                    <div class="flex flex-col md:flex-row md:flex-wrap items-stretch md:items-center justify-center md:justify-start gap-3 md:gap-8 mb-6 md:mb-10">
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($vendor->address ?? 'Professional District') }}"
                            target="_blank" rel="noopener noreferrer"
                            class="flex items-center gap-3 group/address transition-all hover:scale-[1.02] w-full md:w-auto justify-center md:justify-start px-4 py-3 md:p-0 bg-white/5 md:bg-transparent rounded-2xl md:rounded-none border border-white/10 md:border-0">
                            <div
                                class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-xl flex items-center justify-center theme-gradient-text border border-white/10 group-hover/address:border-white/30 group-hover/address:bg-white/20 transition-all">
                                <svg class="w-5 h-5 text-current" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                            </div>
                            <span
                                class="text-base font-bold text-white/60 italic group-hover/address:text-white transition-colors underline underline-offset-4">{{
    $vendor->address ?? 'Professional District' }}</span>
                        </a>
                        <div class="flex items-center gap-3 w-full md:w-auto justify-center md:justify-start px-4 py-3 md:p-0 bg-white/5 md:bg-transparent rounded-2xl md:rounded-none border border-white/10 md:border-0">
                            <div
                                class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-xl flex items-center justify-center theme-gradient-text border border-white/10 shrink-0">
                                <svg class="w-5 h-5 text-current" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-2xl md:text-3xl font-black text-white tracking-tighter italic">₹{{ number_format($vendor->employees->where('is_active', true)->where('service_fee_override', '>', 0)->min('service_fee_override') ?? $vendor->service_fee) }} onwards <span
                                    class="tracking-widest text-[10px] font-black uppercase text-white/10 ml-1">Professional
                                    Fee</span></span>
                        </div>
                    </div>

                    <div class="hidden md:flex flex-wrap gap-2 justify-start">
                        <span
                            class="theme-pill theme-gradient-bg border-transparent text-white font-black uppercase tracking-widest">{{
    strtoupper($theme['label']) }} EXPERT</span>
                        @foreach($theme['services'] as $service)
                                            <span class="theme-pill text-[9px] font-black uppercase tracking-widest text-white/60">{{
                            $service }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        @if($isSubscriptionExpired)
        <div class="max-w-7xl mx-auto px-6 mb-12 relative z-10">
            <div class="glass-card p-8 bg-red-500/10 border-red-500/20 backdrop-blur-3xl rounded-[2.5rem] flex flex-col md:flex-row items-center gap-6 shadow-2xl relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-red-500/10 to-transparent"></div>
                <div class="w-16 h-16 rounded-2xl bg-red-500/20 flex items-center justify-center shrink-0 border border-red-500/30">
                    <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-grow text-center md:text-left relative z-10">
                    <h3 class="text-2xl font-black text-white italic tracking-tighter uppercase mb-1">Online Booking Suspended</h3>
                    <p class="text-white/60 font-medium text-sm">This business's subscription has expired. Online booking features are temporarily disabled.</p>
                </div>
            </div>
        </div>
        @endif

        <!-- APPOINTMENT SELECTION MATRIX -->
        <div class="max-w-7xl mx-auto px-5 md:px-6 grid grid-cols-1 xl:grid-cols-12 gap-10 xl:gap-16 relative z-10 pb-24 md:pb-40">

            <!-- STEP 1: Service Selection -->
            <div class="order-1 xl:order-none xl:col-span-7 xl:col-start-1 xl:row-start-1">
                <div class="mb-8 md:mb-12">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-10 h-1 theme-gradient-bg rounded-full"></span>
                        <span
                            class="theme-gradient-text font-black text-[10px] uppercase tracking-widest italic">Booking
                            Step 01</span>
                    </div>
                    <h2 class="text-2xl md:text-4xl font-black text-white tracking-tighter uppercase italic">Select {{
    $theme['employee_label'] }}</h2>
                </div>

                <div class="space-y-4">
                    @forelse($vendor->employees as $employee)
                        <button
                            @click="fetchSlots({{ $employee->id }}, {{ $employee->service_fee_override ?? $vendor->service_fee }})"
                            :disabled="!{{ $employee->is_available ? 'true' : 'false' }} || isSubscriptionExpired"
                            class="w-full p-4 md:p-6 flex items-center gap-4 md:gap-6 text-left transition-all duration-500 rounded-[2rem] md:rounded-[2.5rem] border-2 group relative overflow-hidden glass-card shadow-xl shadow-black/20 backdrop-blur-3xl"
                            :class="isSubscriptionExpired ? 'bg-white/5 border-transparent opacity-20 grayscale pointer-events-none' : (selectedEmployee === {{ $employee->id }} ? 'bg-white/5 theme-glow-border scale-[1.02] opacity-100 z-10' : (!{{ $employee->is_available ? 'true' : 'false' }} ? 'bg-white/5 border-transparent opacity-20 grayscale pointer-events-none' : 'bg-white/5 border-transparent opacity-50 hover:opacity-80 hover:border-white/20'))">

                            <div
                                class="w-14 h-14 md:w-20 md:h-20 shrink-0 rounded-2xl md:rounded-[1.5rem] bg-white/10 flex items-center justify-center overflow-hidden border border-white/10 group-hover:scale-105 transition-transform shadow-inner">
                                @if($employee->photo)
                                    <img src="{{ asset('storage/' . $employee->photo) }}" loading="lazy" decoding="async" class="w-full h-full object-cover">
                                @else
                                                    <span class="text-2xl md:text-3xl font-black text-white italic opacity-30">{{ substr($employee->name, 0, 1) }}</span>
                                @endif
                            </div>

                            <div class="flex-grow min-w-0">
                                <h4 class="text-lg md:text-2xl font-black italic transition-colors truncate"
                                    :class="selectedEmployee === {{ $employee->id }} ? 'theme-gradient-text' : 'text-white'">
                                    {{ $employee->name }}
                                </h4>
                                <div class="flex items-center flex-wrap gap-2 md:gap-4 mt-2">
                                    <span
                                        class="text-[9px] font-black text-white/30 uppercase tracking-widest italic leading-none">{{ $employee->is_available ? 'Operational Now' : 'Unavailable' }}</span>
                                    @if($employee->service_fee_override)
                                        <span
                                            class="px-3 py-1 theme-gradient-bg text-white rounded-lg text-[8px] font-black uppercase tracking-widest shadow-sm">Premium
                                            Talent</span>
                                    @endif
                                </div>
                            </div>

                            <div class="w-10 h-10 md:w-12 md:h-12 shrink-0 rounded-xl border flex items-center justify-center transition-all transform shadow-sm group-hover:rotate-12"
                                :class="selectedEmployee === {{ $employee->id }} ? 'theme-gradient-bg text-white border-transparent rotate-12 scale-110 shadow-lg' : 'bg-white/10 border-white/10 text-white group-hover:bg-white/20'">
                                <svg class="w-5 h-5 transition-transform" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </div>
                        </button>
                    @empty
                        <div
                            class="py-20 text-center border-4 border-dashed border-white/5 rounded-[3rem] opacity-20 italic">
                            <span class="text-6xl block mb-6 grayscale text-white">Offline</span>
                            <p class="font-black uppercase tracking-widest text-white">No Specialists Available</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- OVERVIEW + REVIEWS (sits after both steps on mobile, under Step 1 on desktop) -->
            <div class="order-3 xl:order-none xl:col-span-7 xl:col-start-1 xl:row-start-2">
                <!-- Professional Overview -->
                <div class="mt-12 pt-12 md:mt-24 md:pt-24 border-t border-white/5">
                    <h3 class="text-3xl font-black text-white tracking-tighter uppercase italic mb-8">Establishment
                        Overview</h3>
                    <div class="glass-card shadow-xl shadow-black/20 bg-white/5 backdrop-blur-3xl border border-white/10 p-10 text-lg font-medium text-white/60 leading-relaxed italic"
                        style="padding: 24px; border-radius: 16px;">
                        @if($vendor->description)
                            {!! nl2br(e($vendor->description)) !!}
                        @else
                                            The premier {{ strtolower($theme['label']) }} destination at <strong class="text-white">{{
                            $vendor->business_name }}</strong>. Experience unrivaled professional standards in a
                                            world-class environment.
                        @endif
                    </div>
                </div>

                <!-- REVIEWS & RATINGS -->
                <div x-data="reviewSystem()" class="mt-24 pt-24 border-t border-white/5">
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-10">
                        <div>
                            <div class="flex items-center gap-3 mb-4">
                                <span class="w-10 h-1 theme-gradient-bg rounded-full"></span>
                                <span class="theme-gradient-text font-black text-[10px] uppercase tracking-widest italic">Client Sentiment</span>
                            </div>
                            <h3 class="text-3xl font-black text-white tracking-tighter uppercase italic">Reviews &amp; Ratings</h3>
                        </div>
                        <button @click="openModal()"
                            class="theme-btn h-14 px-8 rounded-2xl text-sm font-black italic uppercase tracking-widest flex items-center justify-center gap-2 group shrink-0">
                            <svg class="w-5 h-5 transition-transform group-hover:rotate-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                            Write a Review
                        </button>
                    </div>

                    <!-- Aggregate Rating Summary -->
                    <div class="glass-card shadow-xl shadow-black/20 bg-white/5 backdrop-blur-3xl border border-white/10 p-8 sm:p-10 mb-8 rounded-[2.5rem] flex flex-col sm:flex-row items-center gap-10">
                        <div class="text-center shrink-0">
                            <div class="text-7xl font-black theme-gradient-text italic tracking-tighter leading-none" x-text="averageRating > 0 ? averageRating.toFixed(1) : '—'"></div>
                            <div class="flex items-center justify-center gap-1 mt-3">
                                <template x-for="star in 5" :key="star">
                                    <svg class="w-4 h-4 transition-colors" :class="star <= Math.round(averageRating) ? 'text-amber-400' : 'text-white/15'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                </template>
                            </div>
                            <p class="text-[9px] font-black text-white/40 uppercase tracking-widest mt-3 italic"><span x-text="reviewsCount"></span> Review<span x-show="reviewsCount !== 1">s</span></p>
                        </div>
                        <div class="hidden sm:block w-px self-stretch bg-white/10"></div>
                        <div class="flex-grow w-full">
                            <template x-if="reviewsCount > 0">
                                <div class="space-y-2">
                                    <template x-for="n in [5,4,3,2,1]" :key="n">
                                        <div class="flex items-center gap-3">
                                            <span class="text-[10px] font-black text-white/40 w-3" x-text="n"></span>
                                            <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                            <div class="flex-grow h-2 rounded-full bg-white/5 overflow-hidden">
                                                <div class="h-full theme-gradient-bg rounded-full transition-all duration-700" :style="`width: ${ratingPercent(n)}%`"></div>
                                            </div>
                                            <span class="text-[10px] font-black text-white/30 w-6 text-right" x-text="ratingCount(n)"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="reviewsCount === 0">
                                <p class="text-white/40 italic font-medium text-sm">No reviews yet — be the first to share your experience with this establishment.</p>
                            </template>
                        </div>
                    </div>

                    <!-- Individual Reviews -->
                    <div class="space-y-4">
                        <template x-for="(review, idx) in reviews" :key="idx">
                            <div class="glass-card bg-white/5 backdrop-blur-3xl border border-white/10 p-6 rounded-[2rem] animate-reveal">
                                <div class="flex items-start justify-between gap-4 mb-3">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl theme-gradient-bg flex items-center justify-center text-white text-lg font-black italic shrink-0 shadow-lg" x-text="review.name ? review.name.charAt(0).toUpperCase() : '?'"></div>
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <h4 class="text-base font-black text-white italic leading-tight" x-text="review.name"></h4>
                                                <span x-show="review.verified" title="Verified Google account" class="inline-flex items-center shrink-0">
                                                    <svg class="w-4 h-4 text-sky-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1 mt-1">
                                                <template x-for="star in 5" :key="star">
                                                    <svg class="w-3.5 h-3.5" :class="star <= review.rating ? 'text-amber-400' : 'text-white/15'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-[9px] font-black text-white/30 uppercase tracking-widest italic shrink-0" x-text="review.created_human"></span>
                                </div>
                                <p x-show="review.comment" class="text-white/60 text-sm font-medium italic leading-relaxed pl-16" x-text="review.comment"></p>
                                <div x-show="review.images && review.images.length" class="flex flex-wrap gap-2 mt-3 pl-16">
                                    <template x-for="(img, i) in review.images" :key="i">
                                        <a :href="img" target="_blank" rel="noopener" class="block">
                                            <img :src="img" loading="lazy" class="w-16 h-16 object-cover rounded-xl border border-white/10 hover:scale-105 transition-transform">
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <div x-show="reviews.length === 0" class="py-16 text-center border-2 border-dashed border-white/5 rounded-[2.5rem] opacity-40 italic">
                            <p class="font-black uppercase tracking-widest text-white text-sm">No Reviews Yet</p>
                        </div>
                    </div>

                    <!-- REVIEW MODAL (teleported to body so it sits above the sticky Step 2 column) -->
                    <template x-teleport="body">
                    <div x-show="showModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-6" x-cloak x-transition>
                        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-3xl" @click="showModal = false"></div>
                        <div class="relative bg-[#0a0f2c] text-white rounded-[2rem] sm:rounded-[3rem] p-6 sm:p-10 w-full max-w-xl max-h-[90vh] overflow-y-auto no-scrollbar shadow-[0_100px_200px_-50px_rgba(0,0,0,0.8)] border border-white/10"
                            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90 translate-y-8" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                            <div class="text-center mb-8">
                                <span class="inline-block px-4 py-1 theme-gradient-bg text-white border theme-border rounded-full text-[9px] font-black uppercase tracking-widest italic mb-5">Share Your Experience</span>
                                <h2 class="text-3xl font-black italic tracking-tighter uppercase mb-2">Rate {{ $vendor->business_name }}</h2>
                                <p class="text-white/40 font-medium text-sm">Your honest feedback helps others choose with confidence.</p>
                            </div>

                            <!-- Star Picker (rendered statically so each button's click binds reliably) -->
                            <div class="flex items-center justify-center gap-2 mb-8">
                                @for($s = 1; $s <= 5; $s++)
                                    <button type="button" @click="rating = {{ $s }}" @mouseenter="hoverRating = {{ $s }}" @mouseleave="hoverRating = 0"
                                        class="transition-transform duration-200 hover:scale-125 focus:outline-none">
                                        <svg class="w-10 h-10 transition-colors duration-150" :class="{{ $s }} <= (hoverRating || rating) ? 'text-amber-400 drop-shadow-[0_0_8px_rgba(251,191,36,0.5)]' : 'text-white/15'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    </button>
                                @endfor
                            </div>

                            <div class="space-y-5 text-left mb-8">
                                <div class="space-y-2">
                                    <label class="text-xs font-black uppercase tracking-[0.2em] text-white/70 ml-2 block">Your Name <span class="text-white/30 normal-case">(optional)</span></label>
                                    <input type="text" x-model="name" maxlength="60" :readonly="!!googleUser"
                                        class="premium-input w-full h-14 px-5 text-base bg-white/5 border-white/10 text-white placeholder-white/20 theme-focus-border read-only:opacity-60 read-only:cursor-not-allowed"
                                        placeholder="e.g. Aarav Sharma">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-black uppercase tracking-[0.2em] text-white/70 ml-2 block">Phone <span class="text-white/30 normal-case">(optional)</span></label>
                                    <input type="tel" x-model="phone" maxlength="10" :readonly="!!googleUser" @input="phone = phone.replace(/[^0-9]/g, '')"
                                        class="premium-input w-full h-14 px-5 text-base bg-white/5 border-white/10 text-white placeholder-white/20 theme-focus-border read-only:opacity-60 read-only:cursor-not-allowed"
                                        placeholder="10 digit number">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-black uppercase tracking-[0.2em] text-white/70 ml-2 block">Review <span class="text-white/30 normal-case">(optional)</span></label>
                                    <textarea x-model="comment" maxlength="1000" rows="4"
                                        class="premium-input w-full px-5 py-4 text-base bg-white/5 border-white/10 text-white placeholder-white/20 theme-focus-border resize-none"
                                        placeholder="Tell us about your visit..."></textarea>
                                </div>

                                <!-- Mandatory photo evidence for low (under 2-star) ratings -->
                                <div x-show="requiresImages" x-cloak x-transition class="space-y-2 rounded-2xl border border-amber-500/30 bg-amber-500/5 p-4">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                        <label class="text-xs font-black uppercase tracking-[0.2em] text-amber-400 block">Photo Proof <span class="text-amber-400/70">(required)</span></label>
                                    </div>
                                    <p class="text-amber-100/60 text-[11px] font-medium italic">A rating under 2 stars must include at least one supporting photo.</p>
                                    <label class="flex items-center justify-center gap-2 h-14 rounded-xl border-2 border-dashed border-amber-500/30 text-amber-300/80 text-xs font-black uppercase tracking-widest cursor-pointer hover:bg-amber-500/10 transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        <span x-text="images.length ? images.length + ' photo' + (images.length > 1 ? 's' : '') + ' selected' : 'Tap to add photos'"></span>
                                        <input type="file" accept="image/*" multiple class="hidden" @change="handleFiles($event)">
                                    </label>
                                    <div x-show="previews.length" class="flex flex-wrap gap-2 pt-1">
                                        <template x-for="(src, i) in previews" :key="i">
                                            <div class="relative group">
                                                <img :src="src" class="w-16 h-16 object-cover rounded-xl border border-white/10">
                                                <button type="button" @click="removeImage(i)" class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-rose-500 text-white text-xs font-black flex items-center justify-center shadow-lg hover:scale-110 transition-transform">&times;</button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <p x-show="error" x-text="error" class="text-rose-400 text-xs font-black uppercase tracking-widest italic text-center mb-4" style="display:none;"></p>

                            <button @click="submit()" :disabled="submitting"
                                class="theme-btn w-full h-16 text-lg rounded-2xl group shadow-lg disabled:opacity-50 disabled:pointer-events-none">
                                <span x-show="!submitting">Post Review</span>
                                <span x-show="submitting" style="display:none;">Posting...</span>
                            </button>

                            @if(config('services.google.client_id'))
                                <!-- Optional: auto-fill name & verify identity with Google -->
                                <div class="mt-6">
                                    <div class="flex items-center gap-4 mb-4">
                                        <span class="flex-grow h-px bg-white/10"></span>
                                        <span class="text-[9px] font-black uppercase tracking-widest text-white/30">or</span>
                                        <span class="flex-grow h-px bg-white/10"></span>
                                    </div>

                                    <!-- Signed-out: render the Google button -->
                                    <div x-show="!googleUser">
                                        <div x-ref="googleBtn" class="flex justify-center min-h-[44px]"></div>
                                        <p class="text-center text-white/30 text-[11px] font-medium italic mt-3">Sign in with Google to auto-fill your details and post as a verified reviewer.</p>
                                    </div>

                                    <!-- Signed-in chip -->
                                    <div x-show="googleUser" x-cloak class="flex items-center gap-3 rounded-2xl border border-sky-500/30 bg-sky-500/5 p-3">
                                        <img :src="googleUser?.picture" x-show="googleUser?.picture" referrerpolicy="no-referrer" class="w-10 h-10 rounded-full border border-white/10 shrink-0" alt="">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-black text-white truncate" x-text="googleUser?.name"></span>
                                                <svg class="w-4 h-4 text-sky-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.5 7.5a1 1 0 01-1.42 0l-3.5-3.5a1 1 0 011.42-1.42l2.79 2.79 6.79-6.79a1 1 0 011.42 0z" clip-rule="evenodd"/></svg>
                                            </div>
                                            <span class="text-[10px] font-black uppercase tracking-widest text-sky-400 truncate block" x-text="googleUser?.email"></span>
                                        </div>
                                        <button type="button" @click="signOutGoogle()" class="text-[9px] font-black uppercase tracking-widest text-white/40 hover:text-white shrink-0">Use a name instead</button>
                                    </div>
                                </div>
                            @endif

                            <button @click="showModal = false"
                                class="mt-6 w-full text-[9px] font-black uppercase tracking-widest text-white/30 hover:text-white transition-colors">Cancel</button>
                        </div>
                    </div>
                    </template>
                </div>
            </div>

            <!-- STEP 2: Time Allocation Section -->
            <div class="order-2 xl:order-none xl:col-span-5 xl:col-start-8 xl:row-start-1 xl:row-span-2 relative">
                <div class="sticky top-32">
                    <div
                        class="glass-card shadow-2xl shadow-black/20 bg-white/10 backdrop-blur-3xl border-white/10 p-2 overflow-hidden rounded-[3rem]">
                        <div class="p-8 pb-4 flex items-center justify-between">
                            <div>
                                <span
                                    class="theme-gradient-text font-black text-[10px] uppercase tracking-widest italic block mb-2">Step
                                    02</span>
                                <h3 class="text-3xl font-black text-white tracking-tighter italic"
                                    x-text="isTokenEnabled ? 'Choose Token & Wait Time' : 'Choose Time'"></h3>
                            </div>
                            <div class="px-4 py-2 bg-emerald-500/10 text-emerald-400 rounded-xl text-[9px] font-black uppercase tracking-widest border border-emerald-500/20 flex items-center gap-2"
                                x-show="!isOffline && !isSubscriptionExpired">
                                <span class="open-pulse bg-emerald-500"></span>
                                Online Now
                            </div>
                            <div class="px-4 py-2 bg-slate-500/10 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest border border-slate-500/20 flex items-center gap-2"
                                x-show="isOffline && !isSubscriptionExpired" style="display: none;">
                                Closed
                            </div>
                            <div class="px-4 py-2 bg-red-500/10 text-red-400 rounded-xl text-[9px] font-black uppercase tracking-widest border border-red-500/20 flex items-center gap-2"
                                x-show="isSubscriptionExpired" style="display: none;">
                                Suspended
                            </div>
                        </div>

                        <div class="p-4 pt-0">
                            <!-- Loading Interface -->
                            <div x-show="loading && !isSubscriptionExpired" class="py-32 flex flex-col items-center justify-center gap-6">
                                <div
                                    class="w-10 h-10 border-4 border-white/10 border-t-orange-500 rounded-full animate-spin">
                                </div>
                                <span class="text-[9px] font-black text-white/30 uppercase tracking-[0.3em]">Syncing
                                    Slots...</span>
                            </div>

                            <!-- Interactive Selection Logic -->
                            <div x-show="!loading && selectedEmployee && !isSubscriptionExpired" class="animate-reveal">

                                <!-- Token Flow -->
                                <template x-if="isTokenEnabled && !isOffline">
                                    <div class="p-6 space-y-6">
                                        <div
                                            class="bg-white/5 p-8 rounded-[2.5rem] text-center text-white relative overflow-hidden shadow-2xl shadow-black/20 border border-white/10">
                                            <div class="absolute inset-0 theme-gradient-bg opacity-10"></div>
                                            <div class="grid grid-cols-2 gap-4 relative z-10">
                                                <div class="border-r border-white/10 pb-2">
                                                    <p
                                                        class="text-[8px] font-black uppercase tracking-widest text-white/30 mb-2 italic">
                                                        Running Token</p>
                                                    <p class="text-5xl font-black italic tracking-tighter leading-none"
                                                        x-text="'#' + runningToken"></p>
                                                </div>
                                                <div class="pb-2">
                                                    <p
                                                        class="text-[8px] font-black uppercase tracking-widest text-white/30 mb-2 italic">
                                                        Queue Index</p>
                                                    <p class="text-5xl font-black italic tracking-tighter leading-none"
                                                        x-text="'#' + queueIndex"></p>
                                                </div>
                                            </div>
                                            <div class="mt-6 pt-4 border-t border-white/5 relative z-10">
                                                <span
                                                    class="inline-block px-4 py-1.5 bg-white/10 rounded-lg text-[9px] font-black uppercase tracking-widest italic"
                                                    x-text="'Est: ' + (queueIndex > 0 ? (queueIndex - Math.max(1, runningToken) + 1) * 10 : 0) + ' Min Wait'"></span>
                                            </div>
                                        </div>
                                        <button
                                            @click="initiateBooking({start: '{{ now()->format('H:i') }}', end: 'Queue', available: true})"
                                            x-show="canBookToken()"
                                            class="theme-btn w-full h-24 text-xl rounded-3xl font-black italic">
                                            SECURE MY TOKEN
                                        </button>
                                        <div x-show="!canBookToken()" class="py-6 text-center opacity-50 italic">
                                            <p class="text-[10px] font-black uppercase text-white">Wait time exceeds
                                                service hours</p>
                                        </div>
                                    </div>
                                </template>

                                <!-- Slot Flow -->
                                <div x-show="!isTokenEnabled && !isOffline" style="display: none;">
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-2 gap-3">
                                            <template x-for="slot in uniqueSlots" :key="slot.start">
                                                <button @click="initiateBooking(slot)"
                                                    :disabled="!slot.available"
                                                    class="p-4 md:p-6 rounded-2xl border-2 text-center transition-all duration-300 relative overflow-hidden group shadow-sm bg-white/5"
                                                    :class="{
                                                            'opacity-30 cursor-not-allowed grayscale border-transparent pointer-events-none': !slot.available,
                                                            'theme-gradient-bg border-transparent text-white scale-[1.02] theme-glow-sm': selectedSlot && selectedSlot.start === slot.start && slot.available,
                                                            'border-white/10 hover:theme-border hover:scale-[1.03] hover:bg-white/10 hover:theme-glow-sm': (!selectedSlot || selectedSlot.start !== slot.start) && slot.available && !slot.is_premium,
                                                            'theme-border/20 bg-white/5 hover:theme-border': slot.is_premium && (!selectedSlot || selectedSlot.start !== slot.start)
                                                        }">

                                                    <span
                                                        class="text-xl md:text-2xl font-black italic tracking-tighter text-white block transition-colors"
                                                        x-text="slot.start"></span>
                                                    <span
                                                        class="text-[9px] font-black uppercase tracking-widest transition-colors"
                                                        :class="selectedSlot && selectedSlot.start === slot.start ? 'text-white/80' : 'text-white/40'"
                                                        x-text="slot.is_premium ? 'Priority' : (slot.available ? 'Select' : 'Booked')"></span>

                                                    <div x-show="slot.is_premium"
                                                        class="mt-2 text-[8px] theme-gradient-bg text-white rounded-lg font-black py-0.5" x-text="'+₹' + slot.premium_fee_amount">
                                                    </div>
                                                </button>
                                            </template>
                                        </div>
                                        <div x-show="uniqueSlots.length === 0" class="py-20 text-center opacity-10 italic">
                                            <span class="text-4xl font-black uppercase tracking-widest text-white">No
                                                Active Slots</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Offline State -->
                                <template x-if="isOffline">
                                    <div
                                        class="py-20 px-8 text-center bg-white/5 rounded-[2.5rem] border border-white/10 animate-reveal">
                                        <div
                                            class="w-20 h-20 theme-gradient-bg rounded-3xl flex items-center justify-center mx-auto mb-6 border theme-border opacity-50">
                                            <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <h4
                                            class="text-2xl font-black text-white italic tracking-tighter uppercase mb-2">
                                            Outside Booking Hours</h4>
                                        <p class="text-white/40 text-[9px] font-black uppercase tracking-widest mb-6">
                                            Slots become available 2 hours before opening</p>
                                        <div
                                            class="inline-block px-6 py-2 bg-white/10 rounded-xl border border-white/10">
                                            <span
                                                class="theme-gradient-text font-black text-xs uppercase italic tracking-widest">Opens
                                                At: <span x-text="openingTime"></span></span>
                                        </div>
                                    </div>
                                </template>

                                <!-- Paused State -->
                                <template x-if="!isOffline && isPaused">
                                    <div class="py-20 px-8 text-center bg-white/5 rounded-[2.5rem] border border-white/10 animate-reveal">
                                        <div class="w-20 h-20 bg-amber-500/20 rounded-3xl flex items-center justify-center mx-auto mb-6 border border-amber-500/50">
                                            <svg class="w-10 h-10 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <h4 class="text-2xl font-black text-amber-500 italic tracking-tighter uppercase mb-2">Specialist on Break</h4>
                                        <p class="text-white/40 text-[9px] font-black uppercase tracking-widest mb-6">Appointments are temporarily paused. Please check back shortly.</p>
                                    </div>
                                </template>
                            </div>

                            <div x-show="!selectedEmployee && !isSubscriptionExpired" class="py-32 text-center opacity-30 animate-fade-in">
                                <span class="text-6xl block mb-6 grayscale">⏳</span>
                                <p class="text-[9px] font-black uppercase tracking-[0.4em] text-white italic">Initiate
                                    Selection Above</p>
                            </div>

                            <!-- Subscription Expired State -->
                            <div x-show="isSubscriptionExpired" class="py-20 px-8 text-center bg-white/5 rounded-[2.5rem] border border-white/10 animate-reveal">
                                <div class="w-20 h-20 bg-red-500/20 rounded-3xl flex items-center justify-center mx-auto mb-6 border border-red-500/50">
                                    <svg class="w-10 h-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <h4 class="text-2xl font-black text-red-500 italic tracking-tighter uppercase mb-2">Booking Suspended</h4>
                                <p class="text-white/40 text-[9px] font-black uppercase tracking-widest">Online booking is disabled for this business.</p>
                            </div>
                        </div>

                        <div class="bg-black/5 p-6 flex flex-col gap-4 border-t border-black/5">
                            <div class="flex items-center justify-center gap-10 opacity-30">
                                <div class="flex items-center gap-2">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <span
                                        class="text-[8px] font-black uppercase tracking-widest text-slate-100">RSA-256</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    <span
                                        class="text-[8px] font-black uppercase tracking-widest text-slate-100">Low-Latency</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- APPOINTMENT CONFIRMATION -->
        <div x-show="bookingModal" class="fixed inset-0 z-[200] flex items-center justify-center p-6" x-cloak
            x-transition>
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-3xl" @click="bookingModal = false"></div>
            <div
                class="relative bg-[#0a0f2c] text-white rounded-[2rem] sm:rounded-[4rem] p-6 sm:p-12 text-center w-full max-w-xl max-h-[90vh] overflow-y-auto no-scrollbar shadow-[0_100px_200px_-50px_rgba(0,0,0,0.8)] border border-white/10">
                <div class="mb-10">
                    <span
                        class="inline-block px-4 py-1 theme-gradient-bg text-white border theme-border rounded-full text-[9px] font-black uppercase tracking-widest italic mb-6">Security
                        Clearance</span>
                    <h2 class="text-4xl font-black italic tracking-tighter uppercase mb-2">{{ $theme['customer_label']
                        }} Details</h2>
                    <p class="text-white/40 font-medium">Please verify your identification for this {{
    strtolower($theme['booking_label']) }}.</p>
                </div>

                <div class="space-y-5 text-left mb-10">
                    <div class="space-y-2">
                        <label
                            class="text-xs font-black uppercase tracking-[0.2em] text-white/70 ml-2 shrink-0 text-left block">Guest
                            Name</label>
                        <div class="relative group">
                            <span
                                class="absolute left-6 top-1/2 -translate-y-1/2 text-white/20 group-focus-within:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            <input type="text" x-model="guestName" maxlength="50"
                                class="premium-input w-full h-14 pl-12 text-base bg-white/5 border-white/10 text-white placeholder-white/20 theme-focus-border"
                                placeholder="Full {{ $theme['customer_label'] }} Name">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label
                            class="text-xs font-black uppercase tracking-[0.2em] text-white/70 ml-2 shrink-0 text-left block">Phone
                            Number</label>
                        <div class="relative group">
                            <span
                                class="absolute left-6 top-1/2 -translate-y-1/2 text-white/20 group-focus-within:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </span>
                            <input type="tel" x-model="guestPhone" maxlength="10"
                                @input="guestPhone = guestPhone.replace(/[^0-9]/g, '')"
                                class="premium-input w-full h-14 pl-12 text-base bg-white/5 border-white/10 text-white placeholder-white/20 theme-focus-border"
                                placeholder="10 Digit Primary Number">
                        </div>
                    </div>
                </div>

                <div
                    class="bg-black/40 rounded-[2.5rem] p-10 text-white mb-10 shadow-2xl relative overflow-hidden border border-white/5">
                    <div class="absolute inset-0 theme-gradient-bg opacity-10"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="flex justify-between items-center opacity-40">
                            <span class="text-[9px] font-black uppercase tracking-widest italic">Base Professional
                                Rate</span>
                            <span class="font-black" x-text="'₹' + selectedServiceFee"></span>
                        </div>
                        <div x-show="selectedSlot?.is_premium"
                            class="flex justify-between items-center theme-gradient-text">
                            <span class="text-[9px] font-black uppercase tracking-widest italic">Priority Booking Fee</span>
                            <span class="font-black" x-text="'₹' + selectedSlot?.premium_fee_amount"></span>
                        </div>
                        <div class="flex justify-between items-center pt-6 border-t border-white/10">
                            <span class="text-xl font-black italic uppercase tracking-tighter">Due Now</span>
                            <span
                                class="text-4xl font-black theme-gradient-text drop-shadow-[0_0_10px_rgba(255,255,255,0.2)]"
                                x-text="'₹' + totalAmount"></span>
                        </div>
                    </div>
                </div>

                <button @click="confirmBooking()" class="theme-btn w-full h-24 text-xl rounded-3xl group shadow-lg">
                    AUTHENTICATE & BOOK
                    <svg class="w-6 h-6 transform group-hover:translate-x-2 transition-transform" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
                <button @click="bookingModal = false"
                    class="mt-8 text-[9px] font-black uppercase tracking-widest text-white/30 hover:text-white transition-colors">Abort
                    Transaction</button>
            </div>
        </div>

        <!-- TRANSACTION SUCCESS -->
        <div x-show="successModal" class="fixed inset-0 z-[300] flex items-center justify-center p-6" x-cloak
            x-transition>
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-3xl"></div>
            <div
                class="relative bg-slate-900/90 text-white rounded-[5rem] p-16 text-center max-w-lg shadow-[0_100px_200px_-50px_rgba(0,0,0,0.5)] border-8 border-white/5">
                <div
                    class="w-24 h-24 theme-gradient-bg text-white rounded-[2.5rem] flex items-center justify-center mx-auto mb-10 animate-reveal-zoom shadow-2xl theme-glow-sm">
                    <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="text-5xl font-black mb-4 italic tracking-tighter uppercase leading-none theme-gradient-text">Appointment
                    Segmented</h2>
                <p class="text-white/60 font-medium text-lg mb-12" x-text="successMsg"></p>
                <button @click="window.location.href='/'"
                    class="theme-btn w-full h-24 text-xl rounded-3xl opacity-100 italic">GLOBAL
                    REGISTRY</button>
            </div>
        </div>
    </div>

    <!-- LOGICAL ENGINE -->
    @if($vendor->appointment_mode !== 'token')
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    @endif
    <script>
        function bookingSystem() {
            return {
                selectedEmployee: {{ $selectedEmployee ? $selectedEmployee->id : 'null' }},

                selectedServiceFee: {{ $selectedEmployee ? ($selectedEmployee->service_fee_override ?? $vendor->service_fee) : 0 }},
                slots: @js($slots),
                loading: false,
                bookingModal: false,
                isOffline: {{ $isOffline ? 'true' : 'false' }},
                isPaused: {{ (isset($isPaused) && $isPaused) ? 'true' : 'false' }},
                isSubscriptionExpired: {{ $isSubscriptionExpired ? 'true' : 'false' }},

                successModal: false,
                successMsg: '',
                selectedSlot: null,
                guestName: '',
                guestPhone: '',
                isTokenEnabled: {{ $vendor->appointment_mode === 'token' ? 'true' : 'false' }},
                emergencyFee: {{ $vendor->emergency_fee ?: 0 }},
                totalAmount: 0,
                openingTime: '{{ $opensAt }}',
                closingTime: '{{ $vendor->global_closing_time }}',
                allowBookingUntilClosing: {{ $vendor->allow_booking_until_closing ? 'true' : 'false' }},
                avgTimePerToken: {{ $vendor->avg_consultation_time ?: 15 }},
                queueIndex: {{ $queueIndex ?? 0 }},
                runningToken: {{ $runningToken ?? 0 }},

                get uniqueSlots() {
                    const seen = new Set();
                    return this.slots.filter(slot => {
                        if (seen.has(slot.start)) return false;
                        seen.add(slot.start);
                        return true;
                    });
                },

                canBookToken() {
                    if (this.allowBookingUntilClosing) return true;
                    if (this.isOffline) return false;

                    const count = this.queueIndex > 0 ? (this.queueIndex - Math.max(1, this.runningToken) + 1) : 0;
                    const waitMin = count * this.avgTimePerToken;

                    const now = new Date();
                    const closing = new Date();
                    const [h, m, s] = this.closingTime.split(':');
                    closing.setHours(h, m, s || 0);

                    // If cross midnight
                    if (closing < now) closing.setDate(closing.getDate() + 1);

                    const remainingMin = (closing - now) / 60000;
                    return waitMin < remainingMin;
                },
                async fetchSlots(id, fee) {
                    if (this.isSubscriptionExpired) return;
                    this.loading = true;
                    this.slots = []; // Clear immediately to prevent Alpine x-for duplication on re-mount
                    this.selectedEmployee = id;
                    this.selectedServiceFee = fee;
                    try {
                        const res = await fetch(`/api/vendors/{{ $vendor->id }}/employees/${id}/slots`);
                        if (!res.ok) throw new Error('NETWORK REJECTION');
                        const data = await res.json();
                        if (data.offline) {
                            this.isOffline = true;
                            this.isPaused = false;
                            this.openingTime = data.opens_at;
                            this.slots = [];
                            this.queueIndex = 0;
                            this.runningToken = 0;
                        } else if (data.paused) {
                            this.isOffline = false;
                            this.isPaused = true;
                            this.slots = [];
                            this.queueIndex = 0;
                            this.runningToken = 0;
                        } else {
                            this.isOffline = false;
                            this.isPaused = false;
                            this.slots = data.slots;
                            this.queueIndex = data.queue_index;
                            this.runningToken = data.running_token;
                        }
                    } catch (e) {
                        console.error('SYSTEM SYNC ERROR', e);
                        this.slots = [];
                    }
                    this.loading = false;
                },

        initiateBooking(slot) {
            this.selectedSlot = slot;
            const premiumSlotFee = slot.is_premium ? slot.premium_fee_amount : 0;
            this.totalAmount = this.selectedServiceFee + premiumSlotFee;
            this.bookingModal = true;
        },

                async confirmBooking() {
            if (!this.selectedSlot) {
                return;
            }
            if (!this.guestName || this.guestPhone.length < 10) return;
            this.submitBooking('pay_ext_' + Math.random().toString(36).substr(2, 9));
        },

                async submitBooking(paymentId) {
            if (!this.selectedSlot) return;
            this.bookingModal = false;
            this.loading = true;
            try {
                const res = await fetch('/bookings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        vendor_id: {{ $vendor->id }},

                        employee_id: this.selectedEmployee,
                        slot_start: this.selectedSlot.start,
                        slot_end: this.selectedSlot.end,
                        booking_type: this.selectedSlot.is_premium ? 'premium' : 'normal',
                        customer_name: this.guestName,
                        customer_phone: this.guestPhone,
                        payment_id: paymentId
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.successMsg = data.message;
                    this.successModal = true;
                    setTimeout(() => {
                        window.dispatchEvent(new Event('trigger-notification-prompt'));
                    }, 500);
                    // Refresh slots so the just-booked slot reflects as taken
                    await this.fetchSlots(this.selectedEmployee, this.selectedServiceFee);
                } else {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.error || 'ALLOCATION FAILED', type: 'error' } }));
                }
            } catch (e) {
                console.error('ALLOCATION FAILURE', e);
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'SYSTEM ERROR', type: 'error' } }));
            }
            this.loading = false;
        },
                async refreshQueueStatus() {
                    if (this.isSubscriptionExpired) return;
                    try {
                        const res = await fetch(`/vendors/{{ $vendor->slug }}/queue-status?employee_id=${this.selectedEmployee}`);
                        if (res.ok) {
                            const data = await res.json();
                            this.runningToken = data.now_serving;
                            this.queueIndex = data.queue_index;
                            
                            if (!data.is_open || data.bookings_paused) {
                                this.isPaused = data.bookings_paused;
                                this.isOffline = !data.is_open;
                            }
                        }
                    } catch (e) {
                        console.error('Queue refresh failed', e);
                    }
                },

                init() {
                    if (this.isTokenEnabled) {
                        setInterval(() => {
                            if (!this.bookingModal) {
                                this.refreshQueueStatus();
                            }
                        }, 10000); // 10 seconds
                    }
                }
            }
        }

    </script>

    <script>
        function reviewSystem() {
            return {
                showModal: false,
                submitting: false,
                error: '',
                rating: 0,
                hoverRating: 0,
                name: '',
                phone: '',
                comment: '',
                images: [],
                previews: [],
                reviews: @js($reviews),
                averageRating: {{ $averageRating }},
                reviewsCount: {{ $reviewsCount }},

                // Optional Google identity
                googleClientId: @js(config('services.google.client_id')),
                googleUser: null,
                googleCredential: null,
                googleRendered: false,

                // Ratings under 2 stars must include photo proof.
                get requiresImages() {
                    return this.rating > 0 && this.rating < 2;
                },

                openModal() {
                    this.error = '';
                    this.showModal = true;
                    if (this.googleClientId) {
                        this.$nextTick(() => this.initGoogleButton());
                    }
                },

                initGoogleButton() {
                    if (this.googleRendered || !this.googleClientId) return;
                    const render = () => {
                        if (!window.google?.accounts?.id || !this.$refs.googleBtn) return false;
                        window.google.accounts.id.initialize({
                            client_id: this.googleClientId,
                            callback: (resp) => this.handleGoogleCredential(resp),
                        });
                        window.google.accounts.id.renderButton(this.$refs.googleBtn, {
                            theme: 'filled_blue', size: 'large', shape: 'pill', text: 'continue_with', width: 280,
                        });
                        this.googleRendered = true;
                        return true;
                    };
                    if (render()) return;
                    // GIS script may still be loading — retry briefly.
                    let tries = 0;
                    const iv = setInterval(() => {
                        if (render() || ++tries > 40) clearInterval(iv);
                    }, 150);
                },

                handleGoogleCredential(resp) {
                    const payload = this.decodeJwt(resp.credential);
                    if (!payload) {
                        this.error = 'Could not read your Google account. Please try again.';
                        return;
                    }
                    this.googleCredential = resp.credential;
                    this.googleUser = {
                        name: payload.name || payload.email,
                        email: payload.email,
                        picture: payload.picture,
                    };
                    // Auto-fill the (now read-only) name field from the verified account.
                    this.name = this.googleUser.name;
                    this.error = '';
                },

                signOutGoogle() {
                    this.googleUser = null;
                    this.googleCredential = null;
                    this.name = '';
                    if (window.google?.accounts?.id) {
                        window.google.accounts.id.disableAutoSelect();
                    }
                },

                decodeJwt(token) {
                    try {
                        const base64 = token.split('.')[1].replace(/-/g, '+').replace(/_/g, '/');
                        const json = decodeURIComponent(atob(base64).split('').map(
                            c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
                        ).join(''));
                        return JSON.parse(json);
                    } catch (e) {
                        return null;
                    }
                },

                handleFiles(e) {
                    for (const file of Array.from(e.target.files)) {
                        if (this.images.length >= 5) break;
                        if (!file.type.startsWith('image/')) continue;
                        this.images.push(file);
                        this.previews.push(URL.createObjectURL(file));
                    }
                    e.target.value = '';
                },

                removeImage(i) {
                    URL.revokeObjectURL(this.previews[i]);
                    this.images.splice(i, 1);
                    this.previews.splice(i, 1);
                },

                resetForm() {
                    // Note: Google sign-in is intentionally kept so a returning
                    // reviewer doesn't have to re-authenticate.
                    this.rating = 0;
                    this.hoverRating = 0;
                    this.comment = '';
                    if (!this.googleUser) this.name = '';
                    this.phone = '';
                    this.previews.forEach(URL.revokeObjectURL);
                    this.images = [];
                    this.previews = [];
                },

                ratingCount(n) {
                    return this.reviews.filter(r => r.rating === n).length;
                },

                ratingPercent(n) {
                    if (this.reviewsCount === 0) return 0;
                    return Math.round((this.ratingCount(n) / this.reviewsCount) * 100);
                },

                async submit() {
                    this.error = '';
                    if (this.rating < 1) { this.error = 'Please select a star rating'; return; }
                    if (this.requiresImages && this.images.length === 0) {
                        this.error = 'A rating under 2 stars requires at least one photo';
                        return;
                    }

                    const form = new FormData();
                    form.append('rating', this.rating);
                    if (this.name.trim()) form.append('reviewer_name', this.name.trim());
                    if (this.phone) form.append('reviewer_phone', this.phone);
                    if (this.comment) form.append('comment', this.comment);
                    if (this.googleCredential) form.append('google_credential', this.googleCredential);
                    this.images.forEach(file => form.append('images[]', file));

                    this.submitting = true;
                    try {
                        const res = await fetch('{{ route('vendor.reviews.store', $vendor->slug) }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: form
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.reviews.unshift(data.review);
                            this.averageRating = data.average_rating;
                            this.reviewsCount = data.reviews_count;
                            this.showModal = false;
                            this.resetForm();
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message, type: 'success' } }));
                        } else {
                            this.error = (data.errors ? Object.values(data.errors)[0][0] : null) || data.message || 'Could not post review';
                        }
                    } catch (e) {
                        console.error('REVIEW SUBMIT ERROR', e);
                        this.error = 'Something went wrong. Please try again.';
                    }
                    this.submitting = false;
                }
            }
        }
    </script>

    @if(config('services.google.client_id'))
        <script src="https://accounts.google.com/gsi/client" async defer></script>
    @endif
</x-app-layout>