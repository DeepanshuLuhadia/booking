<x-app-layout :vendor-theme="$theme" :page-title="$employee->name . ' - Employee Booking'">
    {{-- @booking-closed.window: the realtime listener lives outside Alpine (it has
         to run through whenRealtimeReady), so it hands the outcome over by event. --}}
    <div x-data="employeeBookingSystem()"
        @booking-closed.window="closedBooking = $event.detail"
        class="relative min-h-screen text-white vendor-theme--{{ strtolower(str_replace(' ', '-', $theme['label'] ?? 'default')) }}">

        <!-- EMPLOYEE HEADER SECTION -->
        <section class="relative z-10 pt-28 pb-10 px-5 md:pt-32 md:pb-16 md:px-6">
            <div class="hidden md:flex max-w-7xl mx-auto md:flex-row items-center gap-6 md:gap-12">
                <!-- Left: Employee Photo -->
                <div class="relative group shrink-0">
                    <div class="w-40 h-40 sm:w-56 sm:h-56 md:w-72 md:h-72 rounded-[2rem] md:rounded-[3rem] overflow-hidden theme-glow-border transition-transform duration-1000 group-hover:scale-105 mx-auto bg-white/10 flex items-center justify-center">
                        @if($employee->photo)
                            <img src="{{ asset('storage/' . $employee->photo) }}" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110" alt="{{ $employee->name }}">
                        @else
                            <span class="text-6xl font-black text-white italic opacity-40">{{ substr($employee->name, 0, 1) }}</span>
                        @endif
                    </div>
                </div>

                <!-- Right: Employee Details -->
                <div class="flex-grow w-full min-w-0 text-center md:text-left animate-text-reveal">
                    <div class="flex items-center justify-center md:justify-start gap-3 mb-2">
                        <span class="theme-pill theme-gradient-bg text-white font-black uppercase tracking-widest">{{ $vendor->business_name }}</span>
                        @if($employee->service_fee_override)
                            <span class="px-3 py-1 theme-gradient-bg text-white rounded-full text-[10px] font-black uppercase tracking-widest">Specialist Talent</span>
                        @endif
                    </div>

                    <h1 class="text-4xl sm:text-5xl md:text-6xl font-black text-white mb-4 md:mb-6 tracking-tighter leading-[0.95] italic break-words">
                        {{ $employee->name }}
                    </h1>

                    <div class="flex flex-col md:flex-row md:flex-wrap items-stretch md:items-center justify-center md:justify-start gap-3 md:gap-8 mb-4">
                        <div class="flex items-center gap-3 w-full md:w-auto justify-center md:justify-start px-4 py-3 md:p-0 bg-white/5 md:bg-transparent rounded-2xl md:rounded-none border border-white/10 md:border-0">
                            <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-xl flex items-center justify-center theme-gradient-text border border-white/10 shrink-0">
                                <svg class="w-5 h-5 text-current" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-2xl md:text-3xl text-left md:text-center font-black text-white tracking-tighter italic">₹{{ number_format($servicePrice) }} <span class="tracking-widest text-[10px] font-black uppercase text-white/40 ml-1">Service Fee</span></span>
                        </div>
                    </div>

                    <p class="text-white/60 font-medium text-sm md:text-base italic max-w-xl">
                        Direct QR booking with {{ $employee->name }} at {{ $vendor->business_name }}. Select your preferred slot or token below.
                    </p>
                </div>
            </div>

            <!-- Mobile Hero (<md) -->
            <div class="vd-mobile-hero" style="display:flex; flex-direction:column; align-items:center; text-align:center; max-width:430px; margin:0 auto;">
                <div style="width:160px; height:160px; padding:3px; border-radius:50%; background:linear-gradient(135deg,#fde68a,#f4b740,#b45309); box-shadow:0 20px 45px rgba(0,0,0,0.5); margin-bottom:20px;">
                    <div style="width:100%; height:100%; border-radius:50%; overflow:hidden; background:#0b1020; display:flex; align-items:center; justify-center:center;">
                        @if($employee->photo)
                            <img src="{{ asset('storage/' . $employee->photo) }}" alt="{{ $employee->name }}" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            <span style="font-size:48px; font-weight:900; color:#fff; opacity:0.4;">{{ substr($employee->name, 0, 1) }}</span>
                        @endif
                    </div>
                </div>

                <div style="font-size:12px; font-weight:900; text-transform:uppercase; letter-spacing:0.1em; color:#38bdf8; margin-bottom:6px;">{{ $vendor->business_name }}</div>

                <h1 style="font-size:28px; font-weight:900; color:#fff; letter-spacing:-0.01em; line-height:1.15; margin:0 0 16px;">
                    {{ $employee->name }}
                </h1>

                <div style="width:100%; display:flex; align-items:center; justify-content:center; gap:12px; text-align:center; background:rgba(255,255,255,0.05); padding:12px; border-radius:16px; border:1px solid rgba(255,255,255,0.1);">
                    <div style="font-size:22px; font-weight:900; color:#fff;">₹{{ number_format($servicePrice) }}</div>
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.14em; color:rgba(255,255,255,0.4);">Service Fee</div>
                </div>
            </div>
        </section>

        @if($isSubscriptionExpired)
        <div class="max-w-7xl mx-auto px-6 mb-8 md:mb-12 relative z-10">
            <div class="glass-card p-8 bg-red-500/10 border-red-500/20 backdrop-blur-3xl rounded-[2.5rem] flex flex-col md:flex-row items-center gap-6 shadow-2xl relative overflow-hidden">
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

        <!-- MAIN BOOKING & ACTIVE STATUS SECTION -->
        {{-- id="emp-public-live": everything that moves when the queue does — the
             customer's own token panel, the running token, the slot grid. The
             booking modals sit outside it, so a live update never yanks the form
             out from under someone filling it in. --}}
        <div id="emp-public-live" class="max-w-4xl mx-auto px-5 md:px-6 relative z-10 pb-12 md:pb-32">
            
            <!-- ACTIVE BOOKING DETECTED BANNER / CARD -->
            @if($activeBooking)
            <div class="mb-10 animate-reveal">
                <div class="glass-card shadow-2xl bg-sky-500/10 backdrop-blur-3xl border-sky-400/30 p-8 rounded-[3rem] text-center relative overflow-hidden">
                    <div class="w-16 h-16 theme-gradient-bg text-white rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <span class="inline-block px-4 py-1.5 bg-sky-500/20 text-sky-300 rounded-full text-[10px] font-black uppercase tracking-widest italic mb-3">Your Active Booking</span>

                    {{-- The booking may be with a colleague, not the specialist on
                         this page — one active booking per business is the rule.
                         So the queue figures come from the booking's own
                         specialist ($activeBookingInfo), never this page's
                         $runningToken. --}}
                    @if($vendor->appointment_mode === 'token')
                        <h2 class="text-4xl font-black italic tracking-tighter uppercase text-white mb-2">Token #{{ $activeBooking->token_number }}</h2>
                        <p class="text-white/50 text-xs font-bold uppercase tracking-widest">With {{ $activeBookingInfo['employee_name'] }}</p>
                        <div class="grid grid-cols-2 gap-4 max-w-sm mx-auto mt-6 bg-white/5 p-4 rounded-2xl border border-white/10">
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest text-white/40 italic">{{ $activeBookingInfo['serving_label'] }}</p>
                                <p class="text-3xl font-black text-white italic">{{ $activeBookingInfo['serving_display'] }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest text-white/40 italic">Queue Position</p>
                                <p class="text-3xl font-black text-sky-400 italic">#{{ $activeBookingInfo['people_ahead'] }}</p>
                            </div>
                        </div>
                    @else
                        {{-- appointment_at, not booking_date + slot_start_time:
                             an after-midnight slot belongs to the previous
                             day's sheet but happens the following morning. --}}
                        <h2 class="text-3xl font-black italic tracking-tighter uppercase text-white mb-2">Slot: {{ $activeBooking->appointment_at?->format('h:i A') ?? $activeBooking->slot_start_time }}</h2>
                        <p class="text-white/60 font-medium text-sm">Date: {{ $activeBooking->appointment_date_label }}</p>
                        <span class="inline-block mt-3 px-3 py-1 bg-emerald-500/20 text-emerald-300 text-[10px] font-black uppercase rounded-lg">Status: {{ ucfirst($activeBooking->status) }}</span>
                    @endif
                </div>
            </div>
            @endif

            <!-- BOOKING INTERFACE CARD -->
            <div class="glass-card shadow-2xl shadow-black/20 bg-white/10 backdrop-blur-3xl border-white/10 p-4 md:p-8 rounded-[3rem]">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <span class="theme-gradient-text font-black text-[10px] uppercase tracking-widest italic block mb-1">Direct Booking</span>
                        <h3 class="text-2xl md:text-3xl font-black text-white tracking-tighter italic">
                            {{ $vendor->appointment_mode === 'token' ? 'Secure Token' : 'Select Time Slot' }}
                        </h3>
                    </div>

                    @if(!$isOffline && !$isSubscriptionExpired)
                        <div class="px-4 py-2 bg-emerald-500/10 text-emerald-400 rounded-xl text-[9px] font-black uppercase tracking-widest border border-emerald-500/20 flex items-center gap-2">
                            <span class="open-pulse bg-emerald-500"></span>
                            Available Now
                        </div>
                    @else
                        <div class="px-4 py-2 bg-slate-500/10 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest border border-slate-500/20">
                            Offline / Paused
                        </div>
                    @endif
                </div>

                {{-- Time-slot mode: a live booking at this business closes the slot
                     grid outright. It used to stay clickable, so the customer
                     picked a time and collected a 422 from the duplicate check. --}}
                @if($vendor->appointment_mode !== 'token' && $activeBooking)
                    <div class="py-12 px-6 text-center bg-sky-500/10 rounded-[2.5rem] border border-sky-400/30">
                        <h4 class="text-2xl font-black text-white italic tracking-tighter uppercase mb-2">Already Booked Here</h4>
                        <p class="text-white/50 text-sm font-medium max-w-md mx-auto">
                            Your {{ $activeBookingInfo['slot_time'] ?? 'appointment' }} with {{ $activeBookingInfo['employee_name'] }}
                            on {{ $activeBookingInfo['booking_date'] }} is still open. {{ $vendor->business_name }} becomes
                            bookable for you again once it is complete.
                        </p>
                        <a href="{{ route('bookings.mine') }}" class="inline-block mt-6 px-6 py-3 rounded-xl bg-white/5 border border-white/10 text-[10px] font-black uppercase tracking-widest text-white hover:bg-white/10 transition-all active:scale-95">
                            See All My Bookings
                        </a>
                    </div>
                @elseif($vendor->appointment_mode === 'token' && !$isOffline && !$isPaused && !$isSubscriptionExpired)
                    <div class="space-y-6">
                        <div class="bg-white/5 p-6 md:p-8 rounded-[2.5rem] text-center text-white relative overflow-hidden shadow-2xl border border-white/10">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="border-r border-white/10">
                                    <p class="text-[8px] font-black uppercase tracking-widest text-white/40 mb-2 italic">Running Token</p>
                                    <p class="text-5xl font-black italic tracking-tighter leading-none">#{{ $runningToken }}</p>
                                </div>
                                <div>
                                    <p class="text-[8px] font-black uppercase tracking-widest text-white/40 mb-2 italic">Total Tokens Booked</p>
                                    <p class="text-5xl font-black italic tracking-tighter leading-none">#{{ $queueIndex }}</p>
                                </div>
                            </div>
                        </div>

                        @if($activeBooking)
                            <!-- PERMANENT ACTIVE TOKEN DISPLAY ON SCREEN -->
                            <div class="p-6 md:p-8 bg-sky-500/10 border border-sky-400/30 rounded-[2.5rem] text-center relative overflow-hidden shadow-xl">
                                <span class="inline-block px-4 py-1 bg-sky-500/20 text-sky-300 rounded-full text-[10px] font-black uppercase tracking-widest italic mb-3">Your Booked Token</span>
                                <h2 class="text-4xl sm:text-5xl font-black italic tracking-tighter uppercase text-white mb-4">Token #{{ $activeBooking->token_number }}</h2>

                                <div class="grid grid-cols-2 gap-4 max-w-sm mx-auto bg-white/5 p-4 rounded-2xl border border-white/10">
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-widest text-white/40 italic">{{ $activeBookingInfo['serving_label'] }}</p>
                                        <p class="text-3xl font-black text-white italic">{{ $activeBookingInfo['serving_display'] }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-widest text-white/40 italic">Queue Position</p>
                                        <p class="text-3xl font-black text-sky-400 italic">#{{ $activeBookingInfo['people_ahead'] }}</p>
                                    </div>
                                </div>
                                <p class="text-white/50 text-xs mt-4 italic">
                                    You have an active booking with {{ $activeBookingInfo['employee_name'] }} at {{ $vendor->business_name }}.
                                    Only one active booking per business is allowed each day — book here again once it is complete.
                                </p>
                                <a href="{{ route('bookings.mine') }}" class="inline-block mt-4 px-6 py-3 rounded-xl bg-white/5 border border-white/10 text-[10px] font-black uppercase tracking-widest text-white hover:bg-white/10 transition-all active:scale-95">
                                    See All My Bookings
                                </a>
                            </div>
                        @else
                            <button @click="initiateBooking({start: '{{ now()->format('H:i') }}', end: 'Queue', available: true})"
                                class="theme-btn w-full h-16 md:h-20 text-lg md:text-xl rounded-3xl flex items-center justify-center gap-3 shadow-lg">
                                SECURE TOKEN FOR {{ strtoupper($employee->name) }}
                            </button>
                        @endif
                    </div>
                @elseif($vendor->appointment_mode !== 'token' && !$isOffline && !$isPaused && !$isSubscriptionExpired)
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @forelse($slots as $slot)
                            <button @click="initiateBooking({{ json_encode($slot) }})"
                                :disabled="!{{ $slot['available'] ? 'true' : 'false' }}"
                                class="p-4 rounded-2xl border-2 text-center transition-all duration-300 relative overflow-hidden bg-white/5"
                                :class="{
                                    'opacity-30 cursor-not-allowed grayscale border-transparent': !{{ $slot['available'] ? 'true' : 'false' }},
                                    'border-white/10 hover:theme-border hover:bg-white/10': {{ $slot['available'] ? 'true' : 'false' }}
                                }">
                                <span class="text-xl font-black italic tracking-tighter text-white block">{{ $slot['start'] }}</span>
                                <span class="text-[9px] font-black uppercase tracking-widest text-white/40 block mt-1">
                                    {{ $slot['available'] ? 'Select' : 'Booked' }}
                                </span>
                            </button>
                        @empty
                            <div class="col-span-full py-12 text-center opacity-40 italic">
                                <p class="font-black uppercase tracking-widest text-white">No Slots Available Today</p>
                            </div>
                        @endforelse
                    </div>
                @else
                    <div class="py-12 px-6 text-center bg-white/5 rounded-[2.5rem] border border-white/10">
                        <h4 class="text-2xl font-black text-white italic tracking-tighter uppercase mb-2">Outside Service Hours</h4>
                        <p class="text-white/40 text-xs font-black uppercase tracking-widest">Employee is currently offline or paused. Please check back during opening hours (Opens at {{ $opensAt }}).</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- BOOKING CONFIRMATION MODAL -->
        <div x-show="bookingModal" class="fixed inset-0 z-[200] flex items-center justify-center p-6" x-cloak x-transition>
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-3xl" @click="bookingModal = false"></div>
            <div class="relative bg-[#0a0f2c] text-white rounded-[2rem] sm:rounded-[4rem] p-6 sm:p-12 text-center w-full max-w-xl max-h-[90vh] overflow-y-auto no-scrollbar shadow-[0_100px_200px_-50px_rgba(0,0,0,0.8)] border border-white/10">
                <div class="mb-8">
                    <span class="inline-block px-4 py-1 theme-gradient-bg text-white border theme-border rounded-full text-[9px] font-black uppercase tracking-widest italic mb-4">Direct Employee Booking</span>
                    <h2 class="text-3xl font-black italic tracking-tighter uppercase mb-1">Enter Details</h2>
                    <p class="text-white/40 font-medium text-sm">Booking directly with {{ $employee->name }}</p>
                </div>

                <div class="space-y-4 text-left mb-8">
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-white/70 ml-2 block mb-1">Guest Name</label>
                        <input type="text" x-model="guestName" maxlength="50" class="premium-input w-full h-14 px-5 bg-white/5 border-white/10 text-white placeholder-white/20 theme-focus-border" placeholder="Your Name">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-white/70 ml-2 block mb-1">Phone Number</label>
                        <input type="tel" x-model="guestPhone" maxlength="10" @input="guestPhone = guestPhone.replace(/[^0-9]/g, '')" class="premium-input w-full h-14 px-5 bg-white/5 border-white/10 text-white placeholder-white/20 theme-focus-border" placeholder="10 Digit Phone Number">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-white/70 ml-2 block mb-1">Email <span class="text-white/30">(Optional)</span></label>
                        <input type="email" x-model="guestEmail" maxlength="255" class="premium-input w-full h-14 px-5 bg-white/5 border-white/10 text-white placeholder-white/20 theme-focus-border" placeholder="you@example.com">
                    </div>
                </div>

                <div class="bg-black/40 rounded-[2rem] p-6 text-white mb-8 border border-white/5 flex justify-between items-center">
                    <span class="text-lg font-black italic uppercase">Total Service Fee</span>
                    <span class="text-3xl font-black theme-gradient-text">₹{{ number_format($servicePrice) }}</span>
                </div>

                <button @click="confirmBooking()" class="theme-btn w-full h-16 text-lg rounded-3xl flex items-center justify-center gap-3 shadow-lg">
                    CONFIRM BOOKING
                </button>
                <button @click="bookingModal = false" class="mt-4 text-[9px] font-black uppercase tracking-widest text-white/30 hover:text-white transition-colors">Cancel</button>
            </div>
        </div>
        {{-- BOOKING CLOSED OVERLAY
             The shop completed, cancelled or skipped this customer's booking while
             they were watching their token. It sits OUTSIDE #emp-public-live so the
             live re-render underneath cannot wipe it, and it says which of those
             happened rather than letting the token panel quietly disappear. --}}
        <div x-show="closedBooking" class="fixed inset-0 z-[310] flex items-center justify-center p-6" x-cloak x-transition>
            <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-2xl"></div>

            <div class="relative bg-[#0a0f2c] text-white rounded-[2.5rem] p-8 md:p-12 w-full max-w-lg text-center border border-white/10 shadow-[0_100px_200px_-50px_rgba(0,0,0,0.8)]">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6"
                     :class="closedBooking?.status === 'completed' ? 'bg-emerald-500' : 'bg-amber-500'">
                    <template x-if="closedBooking?.status === 'completed'">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </template>
                    <template x-if="closedBooking?.status !== 'completed'">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                    </template>
                </div>

                <h2 class="text-3xl font-black italic tracking-tighter uppercase mb-3" x-text="closedBookingHeadline()"></h2>

                <p class="text-white/60 text-sm font-medium mb-2">
                    <template x-if="closedBooking?.token_number">
                        <span>Token <span class="font-black text-white" x-text="'#' + closedBooking.token_number"></span> with {{ $employee->name }} at {{ $vendor->business_name }}</span>
                    </template>
                    <template x-if="!closedBooking?.token_number">
                        <span>Your appointment with {{ $employee->name }} at {{ $vendor->business_name }}</span>
                    </template>
                </p>
                <p class="text-white/40 text-xs italic mb-8" x-text="closedBookingNote()"></p>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('bookings.mine') }}"
                       class="flex-1 h-14 rounded-xl theme-gradient-bg text-[10px] font-black uppercase tracking-widest text-white flex items-center justify-center transition-all hover:brightness-110 active:scale-95">
                        View My Bookings
                    </a>
                    <button type="button" @click="closedBooking = null"
                            class="flex-1 h-14 rounded-xl bg-white/5 border border-white/10 text-[10px] font-black uppercase tracking-widest text-white hover:bg-white/10 transition-all active:scale-95">
                        Book Again
                    </button>
                </div>
            </div>
        </div>

        <!-- TRANSACTION SUCCESS MODAL -->
        <div x-show="successModal" class="fixed inset-0 z-[300] flex items-center justify-center p-6" x-cloak x-transition>
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-3xl"></div>
            <div class="relative bg-[#0a0f2c] text-white rounded-[3rem] p-8 sm:p-12 text-center max-w-lg w-full shadow-[0_100px_200px_-50px_rgba(0,0,0,0.8)] border border-white/10">
                <div class="w-20 h-20 theme-gradient-bg text-white rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-2xl">
                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <span class="inline-block px-4 py-1.5 bg-emerald-500/20 text-emerald-300 rounded-full text-[10px] font-black uppercase tracking-widest italic mb-4">Booking Confirmed</span>
                
                <h2 class="text-3xl font-black italic tracking-tighter uppercase mb-4 text-white">Your Confirmation</h2>
                
                <div class="bg-white/5 border border-white/10 rounded-2xl p-6 mb-8 text-center">
                    <template x-if="confirmedToken">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Your Token Number</p>
                            <p class="text-5xl font-black theme-gradient-text italic" x-text="'#' + confirmedToken"></p>
                        </div>
                    </template>
                    <template x-if="!confirmedToken && confirmedTime">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Your Appointment Time</p>
                            <p class="text-3xl font-black theme-gradient-text italic" x-text="confirmedTime"></p>
                        </div>
                    </template>
                    <p class="text-xs text-white/60 font-medium mt-4">Assigned to {{ $employee->name }} at {{ $vendor->business_name }}</p>
                </div>

                <button @click="window.location.reload()" class="theme-btn w-full h-16 text-lg rounded-2xl italic font-black uppercase tracking-widest">
                    GOT IT / DONE
                </button>
            </div>
        </div>
    </div>

    {{--
        Realtime: this specialist's public queue, plus the shop's own state.

        A customer standing in the shop watching this page now sees the running
        token move, their own position close up, and the shop pausing or closing
        — none of which used to reach them without a reload.
    --}}
    <script>
        // Must go through whenRealtimeReady: the Echo bundle is a deferred module
        // and does not exist yet while this script is being parsed.
        window.whenRealtimeReady(function (Echo) {
            const refresh = () => window.Realtime.refresh('#emp-public-live');

            const mine      = {{ $activeBooking->id ?? 'null' }};
            const myToken   = {{ $activeBooking->token_number ?? 'null' }};

            Echo.channel(`queue.{{ $employee->id }}`)
                .listen('.queue.updated', (e) => {
                    const changed = e.changed;

                    // The shop closed out THIS customer's booking. Raise the
                    // outcome overlay rather than letting the token panel quietly
                    // disappear on the re-render below.
                    if (mine && changed && changed.booking_id === mine
                        && changed.status !== 'confirmed' && changed.status !== 'pending') {
                        window.dispatchEvent(new CustomEvent('booking-closed', {
                            detail: { status: changed.status, token_number: myToken }
                        }));
                    }

                    refresh();
                });

            Echo.channel(`shop.{{ $vendor->id }}`)
                .listen('.shop.status', refresh);
        });

        function employeeBookingSystem() {
            return {
                bookingModal: false,
                submitting: false,
                successModal: false,
                confirmedToken: null,
                confirmedTime: null,
                selectedSlot: null,
                guestName: '',
                guestPhone: '{{ session('customer_phone') ?? request()->cookie('customer_phone') }}',
                guestEmail: '',

                /*
                 * Does this business want to know who is booking? The choice is
                 * the shop's, not this specialist's — see
                 * Vendor::$require_customer_details. Off, the details modal is
                 * skipped entirely and tapping a slot books it.
                 */
                requireDetails: {{ $vendor->require_customer_details ? 'true' : 'false' }},

                // Set by the realtime listener when the shop closes out this
                // customer's booking. Drives the outcome overlay above.
                closedBooking: null,

                closedBookingHeadline() {
                    return {
                        completed: 'Appointment Complete',
                        cancelled: 'Booking Cancelled',
                        skipped:   'Appointment Skipped',
                        removed:   'Booking Removed',
                        expired:   'Booking Expired',
                    }[this.closedBooking?.status] ?? 'Booking Closed';
                },

                closedBookingNote() {
                    return {
                        completed: 'Thanks for visiting. You are free to book here again.',
                        cancelled: 'The business cancelled this booking. You are free to book again.',
                        skipped:   'The business could not serve you due to non-availability, so your turn has passed. Please book a new appointment or contact the business to reschedule.',
                        removed:   'The business removed this booking. You are free to book again.',
                        expired:   'The shift closed before your turn came up.',
                    }[this.closedBooking?.status] ?? 'You are free to book again.';
                },

                initiateBooking(slot) {
                    this.selectedSlot = slot;

                    // No details wanted: the tap on the slot IS the booking.
                    if (!this.requireDetails) {
                        this.confirmBooking();
                        return;
                    }

                    this.bookingModal = true;
                },

                async confirmBooking() {
                    if (this.requireDetails
                        && (!this.guestName || !this.guestPhone || this.guestPhone.length < 10)) {
                        alert('Please fill out a valid name and 10-digit phone number.');
                        return;
                    }

                    // Without the details modal in the way, the button books on a
                    // single tap — so a double tap would post twice and meet the
                    // server's "you already have a booking here" refusal.
                    if (this.submitting) return;
                    this.submitting = true;

                    try {
                        const response = await fetch('{{ route('bookings.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                vendor_id: {{ $vendor->id }},
                                employee_id: {{ $employee->id }},
                                slot_start: this.selectedSlot ? this.selectedSlot.start : null,
                                slot_end: this.selectedSlot ? (this.selectedSlot.end || 'Queue') : null,
                                booking_type: (this.selectedSlot && this.selectedSlot.is_premium) ? 'premium' : 'normal',
                                customer_name: this.guestName,
                                customer_phone: this.guestPhone,
                                customer_email: this.guestEmail,
                                fcm_token: window.fcmToken || null
                            })
                        });

                        const result = await response.json();
                        if (response.ok && result.success) {
                            this.bookingModal = false;
                            if (result.booking && result.booking.token_number) {
                                this.confirmedToken = result.booking.token_number;
                            } else if (result.booking && result.booking.slot_start_time) {
                                this.confirmedTime = result.booking.slot_start_time;
                            } else if (this.selectedSlot) {
                                this.confirmedTime = this.selectedSlot.start;
                            }
                            this.successModal = true;

                            // Ask for notification permission now the booking is
                            // real. This page never asked at all, so a customer who
                            // booked here had no way to be told when their turn came.
                            setTimeout(() => {
                                window.dispatchEvent(new Event('trigger-notification-prompt'));
                            }, 500);
                        } else {
                            alert(result.error || 'Failed to complete booking.');

                            /*
                             * The refusal means a booking this device was not
                             * showing — the phone just entered is not the one it
                             * was remembered by. Reload identified by that number
                             * so the booking behind the refusal becomes visible.
                             *
                             * With no phone to reload by, the plain reload does
                             * the same job: the guest key in the cookie is what
                             * the server matched on, so the booking is already
                             * ours to see.
                             */
                            if (result.bookings_url) {
                                if (this.guestPhone) {
                                    window.location.search = '?phone=' + encodeURIComponent(this.guestPhone);
                                } else {
                                    window.location.reload();
                                }
                            }
                        }
                    } catch (e) {
                        alert('Error submitting booking.');
                    } finally {
                        this.submitting = false;
                    }
                }
            };
        }
    </script>
</x-app-layout>
