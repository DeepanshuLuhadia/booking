<x-app-layout :vendor-theme="$theme" :page-title="$vendor->business_name">
    <div x-data="bookingSystem()" class="relative min-h-screen text-white">
        
        <!-- PROFILE HERO -->
        <section class="relative z-10 pt-32 pb-16 px-6">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center gap-12">
                <!-- Profile Identity Card -->
                <div class="relative group">
                    <div class="w-64 h-64 md:w-80 md:h-80 rounded-[3rem] overflow-hidden border-8 border-white/10 shadow-2xl shadow-black/20 animate-reveal-zoom">
                        @if($vendor->shop_photo)
                            <img src="{{ asset('storage/' . $vendor->shop_photo) }}" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110" alt="{{ $vendor->business_name }}">
                        @else
                            <div class="w-full h-full bg-white/10 backdrop-blur-3xl flex items-center justify-center text-8xl text-orange-500 font-black italic">
                                {{ substr($vendor->business_name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <!-- Role Badge Overlay -->
                    <div class="absolute -bottom-4 -right-4">
                        <span class="px-6 py-3 {{ $theme['badge_classes'] }} rounded-2xl text-[10px] font-black uppercase tracking-widest border border-white/20 shadow-xl">
                            {{ $theme['label'] }} Expert
                        </span>
                    </div>
                </div>

                <!-- Business Credentials -->
                <div class="flex-grow text-center md:text-left animate-text-reveal">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 border border-white/20 backdrop-blur-xl rounded-full text-white/50 text-[9px] font-black uppercase tracking-widest mb-6">
                        <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span> Verified Appointment Registry
                    </div>
                    
                    <h1 class="text-6xl md:text-[5.5rem] font-black text-white mb-6 tracking-tighter leading-[0.9] italic">
                        {{ $vendor->business_name }}
                    </h1>

                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-8 mb-10">
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($vendor->address ?? 'Professional District') }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="flex items-center gap-3 group/address transition-all hover:scale-[1.02]">
                            <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-xl flex items-center justify-center text-orange-500 border border-white/10 group-hover/address:border-orange-500 group-hover/address:bg-orange-500/20 transition-all">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            </div>
                            <span class="text-base font-bold text-white/60 italic group-hover/address:text-white transition-colors decoration-orange-500/30 group-hover/address:decoration-orange-500 underline underline-offset-4">{{ $vendor->address ?? 'Professional District' }}</span>
                        </a>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-xl flex items-center justify-center text-orange-500 border border-white/10">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-3xl font-black text-white tracking-tighter italic">₹{{ number_format($vendor->service_fee) }} <span class="text-[9px] font-black uppercase text-white/30 ml-1">Standard Rate</span></span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 justify-center md:justify-start">
                        @foreach($theme['services'] as $service)
                            <span class="px-4 py-2 bg-white/5 border border-white/10 text-white/40 text-[9px] font-black uppercase tracking-widest rounded-xl shadow-sm">
                                {{ $service }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- APPOINTMENT SELECTION MATRIX -->
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-12 gap-16 relative z-10 pb-40">
            
            <!-- LEFT: Service Selection -->
            <div class="lg:col-span-12 xl:col-span-7">
                <div class="mb-12">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-10 h-1 bg-orange-500 rounded-full"></span>
                        <span class="text-orange-500 font-black text-[10px] uppercase tracking-widest italic">Booking Step 01</span>
                    </div>
                    <h2 class="text-4xl font-black text-white tracking-tighter uppercase italic">Select {{ $theme['employee_label'] }}</h2>
                </div>

                <div class="space-y-4">
                    @forelse($vendor->employees as $employee)
                        <button @click="fetchSlots({{ $employee->id }}, {{ $employee->service_fee_override ?? $vendor->service_fee }})"
                                class="w-full p-6 flex items-center gap-6 text-left transition-all duration-500 rounded-[2.5rem] border-2 group relative overflow-hidden glass-card shadow-xl shadow-black/20 backdrop-blur-3xl"
                                :class="selectedEmployee === {{ $employee->id }} ? 'bg-orange-500/20 ring-4 ring-orange-500/10 border-orange-500 hover:translate-x-2' : 'bg-white/5 border-white/10 hover:border-orange-500 hover:bg-white/10 hover:translate-x-2'">
                            
                            <div class="w-20 h-20 rounded-[1.5rem] bg-white/10 flex items-center justify-center overflow-hidden border border-white/10 group-hover:scale-105 transition-transform shadow-inner">
                                @if($employee->photo)
                                    <img src="{{ asset('storage/' . $employee->photo) }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-3xl font-black text-white italic opacity-30">{{ substr($employee->name, 0, 1) }}</span>
                                @endif
                            </div>

                            <div class="flex-grow">
                                <h4 class="text-2xl font-black italic transition-colors"
                                    :class="selectedEmployee === {{ $employee->id }} ? 'text-orange-500' : 'text-white group-hover:text-orange-500'">{{ $employee->name }}</h4>
                                <div class="flex items-center gap-4 mt-2">
                                    <span class="text-[9px] font-black text-white/30 uppercase tracking-widest italic leading-none">Operational Today</span>
                                    @if($employee->service_fee_override)
                                        <span class="px-3 py-1 bg-orange-500 text-white rounded-lg text-[8px] font-black uppercase tracking-widest">Premium Talent</span>
                                    @endif
                                </div>
                            </div>

                            <div class="w-12 h-12 rounded-xl border flex items-center justify-center transition-all transform shadow-sm group-hover:rotate-12"
                                 :class="selectedEmployee === {{ $employee->id }} ? 'bg-orange-500 text-white border-orange-500 rotate-12 scale-110 shadow-lg shadow-orange-500/40' : 'bg-white/10 border-white/10 text-white group-hover:bg-orange-500 group-hover:text-white group-hover:border-orange-500'">
                                <svg class="w-5 h-5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </div>
                        </button>
                    @empty
                        <div class="py-20 text-center border-4 border-dashed border-white/5 rounded-[3rem] opacity-20 italic">
                            <span class="text-6xl block mb-6 grayscale text-white">Offline</span>
                            <p class="font-black uppercase tracking-widest text-white">No Specialists Available</p>
                        </div>
                    @endforelse
                </div>

                <!-- Professional Overview -->
                <div class="mt-24 pt-24 border-t border-white/5">
                    <h3 class="text-3xl font-black text-white tracking-tighter uppercase italic mb-8">Establishment Overview</h3>
                    <div class="glass-card shadow-xl shadow-black/20 bg-white/5 backdrop-blur-3xl border-white/10 p-10 text-lg font-medium text-white/60 leading-relaxed italic">
                        @if($vendor->description)
                            {!! nl2br(e($vendor->description)) !!}
                        @else
                            The premier {{ strtolower($theme['label']) }} destination at <strong class="text-white">{{ $vendor->business_name }}</strong>. Experience unrivaled professional standards in a world-class environment.
                        @endif
                    </div>
                </div>
            </div>

            <!-- RIGHT: Time Allocation Section -->
            <div class="lg:col-span-12 xl:col-span-5 relative">
                <div class="sticky top-32">
                    <div class="glass-card shadow-2xl shadow-black/20 bg-white/10 backdrop-blur-3xl border-white/10 p-2 overflow-hidden rounded-[3rem]">
                        <div class="p-8 pb-4 flex items-center justify-between">
                            <div>
                                <span class="text-orange-500 font-black text-[10px] uppercase tracking-widest italic block mb-2">Step 02</span>
                                <h3 class="text-3xl font-black text-white tracking-tighter italic">Choose Time</h3>
                            </div>
                            <div class="px-4 py-2 bg-emerald-500/10 text-emerald-400 rounded-xl text-[9px] font-black uppercase tracking-widest border border-emerald-500/20 flex items-center gap-2">
                                <span class="open-pulse bg-emerald-500"></span>
                                Online Now
                            </div>
                        </div>

                        <div class="p-4 pt-0">
                            <!-- Loading Interface -->
                            <div x-show="loading" class="py-32 flex flex-col items-center justify-center gap-6">
                                <div class="w-10 h-10 border-4 border-white/10 border-t-orange-500 rounded-full animate-spin"></div>
                                <span class="text-[9px] font-black text-white/30 uppercase tracking-[0.3em]">Syncing Slots...</span>
                            </div>

                            <!-- Interactive Selection Logic -->
                            <div x-show="!loading && selectedEmployee" class="animate-reveal">
                                
                                <!-- Token Flow -->
                                <template x-if="isTokenEnabled">
                                    <div class="p-6 space-y-6">
                                        <div class="bg-white/5 p-8 rounded-[2.5rem] text-center text-white relative overflow-hidden shadow-2xl shadow-black/20 border border-white/10">
                                            <div class="absolute inset-0 bg-orange-500 opacity-5"></div>
                                            <div class="grid grid-cols-2 gap-4 relative z-10">
                                                <div class="border-r border-white/10 pb-2">
                                                    <p class="text-[8px] font-black uppercase tracking-widest text-white/30 mb-2 italic">Running Token</p>
                                                    <p class="text-5xl font-black italic tracking-tighter leading-none" x-text="'#' + runningToken"></p>
                                                </div>
                                                <div class="pb-2">
                                                    <p class="text-[8px] font-black uppercase tracking-widest text-white/30 mb-2 italic">Queue Index</p>
                                                    <p class="text-5xl font-black italic tracking-tighter leading-none" x-text="'#' + queueIndex"></p>
                                                </div>
                                            </div>
                                            <div class="mt-6 pt-4 border-t border-white/5">
                                                <span class="inline-block px-4 py-1.5 bg-white/10 rounded-lg text-[9px] font-black uppercase tracking-widest italic" x-text="'Est: ' + (queueIndex > 0 ? (queueIndex - Math.max(1, runningToken) + 1) * 10 : 0) + ' Min Wait'"></span>
                                            </div>
                                        </div>
                                        <button @click="initiateBooking({start: '{{ now()->format('H:i') }}', end: 'Queue', available: true})"
                                                class="btn-premium w-full h-24 text-xl rounded-3xl">
                                            ISSUE APPOINTMENT TOKEN
                                        </button>
                                    </div>
                                </template>

                                <!-- Slot Flow -->
                                <template x-if="!isTokenEnabled && !isOffline">
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-2 gap-3">
                                            <template x-for="slot in slots" :key="slot.start">
                                                <button @click="initiateBooking(slot)"
                                                        :disabled="!slot.available && !slot.requires_emergency"
                                                        class="p-6 rounded-2xl border-2 text-center transition-all duration-500 relative overflow-hidden group bg-white/5 shadow-sm border-white/10"
                                                        :class="{
                                                            'opacity-30 cursor-not-allowed grayscale bg-white/5 border-transparent pointer-events-none': !slot.available && !slot.requires_emergency,
                                                            'border-white/10 hover:border-orange-500 hover:scale-[1.03] hover:bg-white/10 hover:shadow-xl': slot.available,
                                                            'border-amber-500/30 bg-amber-500/5 hover:border-amber-500': slot.requires_emergency && !slot.available
                                                        }">
                                                    
                                                    <span class="text-2xl font-black italic tracking-tighter text-white block" x-text="slot.start"></span>
                                                    <span class="text-[9px] font-black uppercase tracking-widest text-white/30" x-text="slot.requires_emergency && !slot.available ? 'Priority' : (slot.available ? 'Select' : 'Booked')"></span>
                                                    
                                                    <div x-show="slot.requires_emergency && !slot.available" class="mt-2 text-[8px] bg-amber-500 text-white rounded-lg font-black py-0.5">+₹{{ number_format($vendor->emergency_fee) }}</div>
                                                </button>
                                            </template>
                                        </div>
                                        <div x-show="slots.length === 0" class="py-20 text-center opacity-10 italic">
                                            <span class="text-4xl font-black uppercase tracking-widest text-white">No Active Slots</span>
                                        </div>
                                    </div>
                                </template>

                                <!-- Offline State -->
                                <template x-if="isOffline">
                                    <div class="py-20 px-8 text-center bg-white/5 rounded-[2.5rem] border border-white/10 animate-reveal">
                                        <div class="w-20 h-20 bg-orange-500/10 rounded-3xl flex items-center justify-center mx-auto mb-6 border border-orange-500/20">
                                            <svg class="w-10 h-10 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <h4 class="text-2xl font-black text-white italic tracking-tighter uppercase mb-2">Outside Booking Hours</h4>
                                        <p class="text-white/40 text-[9px] font-black uppercase tracking-widest mb-6">Slots become available 2 hours before opening</p>
                                        <div class="inline-block px-6 py-2 bg-white/10 rounded-xl border border-white/10">
                                            <span class="text-orange-500 font-black text-xs uppercase italic tracking-widest">Opens At: <span x-text="openingTime"></span></span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div x-show="!selectedEmployee" class="py-32 text-center opacity-30 animate-fade-in">
                                <span class="text-6xl block mb-6 grayscale">⏳</span>
                                <p class="text-[9px] font-black uppercase tracking-[0.4em] text-white italic">Initiate Selection Above</p>
                            </div>
                        </div>

                        <div class="bg-black/5 p-6 flex flex-col gap-4 border-t border-black/5">
                            <div class="flex items-center justify-center gap-10 opacity-30">
                                <div class="flex items-center gap-2">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <span class="text-[8px] font-black uppercase tracking-widest text-slate-900">RSA-256</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    <span class="text-[8px] font-black uppercase tracking-widest text-slate-900">Low-Latency</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- APPOINTMENT CONFIRMATION -->
        <div x-show="bookingModal" class="fixed inset-0 z-[200] flex items-center justify-center p-6" x-cloak x-transition>
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-3xl" @click="bookingModal = false"></div>
            <div class="relative bg-slate-900/90 text-white rounded-[4rem] p-12 text-center max-w-xl shadow-[0_100px_200px_-50px_rgba(0,0,0,0.8)] border border-white/10">
                <div class="mb-10">
                    <span class="inline-block px-4 py-1 bg-orange-500/10 text-orange-500 border border-orange-500/20 rounded-full text-[9px] font-black uppercase tracking-widest italic mb-6">Security Clearance</span>
                    <h2 class="text-4xl font-black italic tracking-tighter uppercase mb-2">{{ $theme['customer_label'] }} Details</h2>
                    <p class="text-white/40 font-medium">Please verify your identification for this {{ strtolower($theme['booking_label']) }}.</p>
                </div>

                <div class="space-y-5 text-left mb-10">
                    <div class="relative group">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 text-white/20 group-focus-within:text-orange-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                        <input type="text" x-model="guestName" class="premium-input w-full h-16 pl-14 bg-white/5 border-white/10 text-white placeholder-white/20" placeholder="Full {{ $theme['customer_label'] }} Name">
                    </div>
                    <div class="relative group">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 text-white/20 group-focus-within:text-orange-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </span>
                        <input type="tel" x-model="guestPhone" maxlength="10" class="premium-input w-full h-16 pl-14 bg-white/5 border-white/10 text-white placeholder-white/20" placeholder="10 Digit Primary Number">
                    </div>
                </div>

                <div class="bg-black/40 rounded-[2.5rem] p-10 text-white mb-10 shadow-2xl relative overflow-hidden border border-white/5">
                    <div class="absolute inset-0 bg-orange-500 opacity-5"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="flex justify-between items-center opacity-40">
                            <span class="text-[9px] font-black uppercase tracking-widest italic">Base Professional Rate</span>
                            <span class="font-black" x-text="'₹' + selectedServiceFee"></span>
                        </div>
                        <div x-show="selectedSlot?.requires_emergency" class="flex justify-between items-center text-orange-400">
                            <span class="text-[9px] font-black uppercase tracking-widest italic">Express Priority Access</span>
                            <span class="font-black">₹{{ number_format($vendor->emergency_fee) }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-6 border-t border-white/10">
                            <span class="text-xl font-black italic uppercase tracking-tighter">Due Now</span>
                            <span class="text-4xl font-black text-orange-500" x-text="'₹' + totalAmount"></span>
                        </div>
                    </div>
                </div>

                <button @click="confirmBooking()" class="btn-premium w-full h-24 text-xl rounded-3xl group shadow-lg shadow-orange-500/20">
                    AUTHENTICATE & BOOK
                    <svg class="w-6 h-6 transform group-hover:translate-x-2 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
                <button @click="bookingModal = false" class="mt-8 text-[9px] font-black uppercase tracking-widest text-white/30 hover:text-white transition-colors">Abort Transaction</button>
            </div>
        </div>

        <!-- TRANSACTION SUCCESS -->
        <div x-show="successModal" class="fixed inset-0 z-[300] flex items-center justify-center p-6" x-cloak x-transition>
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-3xl"></div>
            <div class="relative bg-slate-900/90 text-white rounded-[5rem] p-16 text-center max-w-lg shadow-[0_100px_200px_-50px_rgba(0,0,0,0.5)] border-8 border-emerald-500/10">
                <div class="w-24 h-24 bg-emerald-500 text-white rounded-[2.5rem] flex items-center justify-center mx-auto mb-10 animate-reveal-zoom shadow-2xl shadow-emerald-500/30">
                    <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h2 class="text-5xl font-black mb-4 italic tracking-tighter uppercase leading-none">Appointment Segmented</h2>
                <p class="text-white/60 font-medium text-lg mb-12" x-text="successMsg"></p>
                <button @click="window.location.href='/'" class="btn-premium w-full h-24 text-xl rounded-3xl opacity-100 italic shadow-lg shadow-orange-500/20">GLOBAL REGISTRY</button>
            </div>
        </div>
    </div>

    <!-- LOGICAL ENGINE -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        function bookingSystem() {
            return {
                selectedEmployee: {{ $selectedEmployee ? $selectedEmployee->id : 'null' }},
                selectedServiceFee: {{ $selectedEmployee ? ($selectedEmployee->service_fee_override ?? $vendor->service_fee) : 0 }},
                slots: @js($slots),
                loading: false,
                bookingModal: false,
                successModal: false,
                successMsg: '',
                selectedSlot: null,
                guestName: '',
                guestPhone: '',
                isTokenEnabled: {{ $vendor->appointment_mode === 'token' ? 'true' : 'false' }},
                emergencyFee: {{ $vendor->emergency_fee ?: 0 }},
                totalAmount: 0,
                isOffline: {{ $isOffline ? 'true' : 'false' }},
                openingTime: '{{ $opensAt }}',
                queueIndex: {{ $selectedEmployee ? $vendor->bookings()->where('employee_id', $selectedEmployee->id)->where('booking_date', date('Y-m-d'))->max('token_number') ?: 0 : 0 }},
                runningToken: {{ $selectedEmployee ? $vendor->bookings()->where('employee_id', $selectedEmployee->id)->where('booking_date', date('Y-m-d'))->where('status', 'confirmed')->min('token_number') ?: 0 : 0 }},
                
                async fetchSlots(id, fee = 0) {
                    this.loading = true;
                    this.selectedEmployee = id;
                    this.selectedServiceFee = fee;
                    try {
                        const res = await fetch(`/api/vendors/{{ $vendor->id }}/employees/${id}/slots`);
                        if (!res.ok) throw new Error('NETWORK REJECTION');
                        const data = await res.json();
                        if (data.offline) {
                            this.isOffline = true;
                            this.openingTime = data.opens_at;
                            this.slots = [];
                            this.queueIndex = 0;
                            this.runningToken = 0;
                        } else {
                            this.isOffline = false;
                            this.slots = data.slots;
                            this.queueIndex = data.queue_index;
                            this.runningToken = data.running_token;
                        }
                    } catch (e) { console.error('SYSTEM SYNC ERROR', e); }
                    this.loading = false;
                },

                initiateBooking(slot) {
                    this.selectedSlot = slot;
                    const expressFee = (slot.requires_emergency && !slot.available) ? this.emergencyFee : 0;
                    this.totalAmount = this.selectedServiceFee + expressFee;
                    this.bookingModal = true;
                },

                async confirmBooking() {
                    if (!this.guestName || this.guestPhone.length < 10) return;
                    this.submitBooking('pay_ext_' + Math.random().toString(36).substr(2, 9));
                },

                async submitBooking(paymentId) {
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
                                booking_type: this.selectedSlot.requires_emergency && !this.selectedSlot.available ? 'emergency' : 'normal',
                                customer_name: this.guestName,
                                customer_phone: this.guestPhone,
                                payment_id: paymentId
                            })
                        });
                        const data = await res.json();
                        if (data.success) { 
                            this.successMsg = data.message; 
                            this.successModal = true; 
                            if (this.isTokenEnabled) {
                                await this.fetchSlots(this.selectedEmployee, this.selectedServiceFee);
                            }
                        } else {
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.error || 'ALLOCATION FAILED', type: 'error' } }));
                        }
                    } catch (e) { 
                        console.error('ALLOCATION FAILURE', e); 
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'SYSTEM ERROR', type: 'error' } }));
                    }
                    this.loading = false;
                }
            }
        }
    </script>
</x-app-layout>
