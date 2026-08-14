<x-employee-layout>
    @php
        // Badge palette per booking type, reused for the current card and the queue slides.
        $typeStyles = [
            'premium' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
            'vendor'  => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
            'normal'  => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
        ];
    @endphp

    {{-- id="emp-live": the region the realtime listener re-renders in place when a
         booking arrives or the queue moves. Everything that can change while the
         specialist is standing at the screen has to live inside it. --}}
    <div id="emp-live" class="space-y-8 md:space-y-10">
        {{-- Greeting + live status -------------------------------------------------- --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div class="space-y-2">
                <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white">Hello, {{ $employee->name }}</h2>
                <p class="text-xs md:text-sm font-medium text-slate-400 uppercase tracking-widest">Manage your daily appointments</p>
            </div>
            <span class="inline-flex items-center gap-2 self-start px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest {{ $employee->is_paused ? 'bg-amber-500/10 text-amber-400' : 'bg-emerald-500/10 text-emerald-400' }}">
                <span class="w-2 h-2 rounded-full {{ $employee->is_paused ? 'bg-amber-400' : 'bg-emerald-400 animate-pulse' }}"></span>
                Status: {{ $employee->is_paused ? 'PAUSED' : 'ACTIVE' }}
            </span>
        </div>

        {{-- Stat cards -------------------------------------------------------------- --}}
        <div class="grid grid-cols-2 gap-4 md:gap-6">
            <div class="glass-card p-5 md:p-8">
                <p class="text-slate-400 text-[10px] md:text-xs uppercase font-black tracking-widest mb-4">Completed Today</p>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-emerald-500/15 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 md:w-7 md:h-7 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-4xl md:text-5xl font-black text-emerald-400 leading-none">{{ $stats['completed'] }}</h3>
                </div>
            </div>
            <div class="glass-card p-5 md:p-8">
                <p class="text-slate-400 text-[10px] md:text-xs uppercase font-black tracking-widest mb-4">Remaining Today</p>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-amber-500/15 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 md:w-7 md:h-7 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-4xl md:text-5xl font-black text-amber-400 leading-none">{{ $stats['remaining'] }}</h3>
                </div>
            </div>
        </div>

        {{-- Employee QR Code Section ------------------------------------------------ --}}
        <div class="glass-card p-5 sm:p-8 bg-white/5 border border-white/10 rounded-3xl" x-data="{ copied: false }">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <!-- Left: QR Code + Text details -->
                <div class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left w-full">
                    {{--<div class="w-28 h-28 sm:w-24 sm:h-24 rounded-2xl bg-white p-2.5 shrink-0 border border-white/20 shadow-2xl flex items-center justify-center">
                        <img src="{{ asset('storage/' . $employee->qr_code_path) }}" alt="QR Code" class="w-full h-full object-contain">
                    </div>
                     <div class="flex-1 min-w-0 space-y-2 w-full">
                        <div class="flex items-center justify-center sm:justify-start gap-2">
                            <span class="w-2 h-2 rounded-full bg-sky-400 animate-ping"></span>
                            <h3 class="text-lg sm:text-xl font-black text-white italic tracking-tight">Your Personal QR Code</h3>
                        </div>
                        <p class="text-xs text-slate-400 font-medium leading-relaxed max-w-xl">
                            Customers scan this QR code or use your personal link to access your direct booking page.
                        </p>
                        <div class="pt-1 flex items-center justify-center sm:justify-start">
                            <div class="max-w-full inline-flex items-center gap-2 px-3 py-1.5 bg-white/10 rounded-xl border border-white/10 text-[11px] font-mono text-sky-300 overflow-hidden">
                                <svg class="w-3.5 h-3.5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                <span class="truncate select-all">{{ $employee->public_url }}</span>
                            </div>
                        </div>
                    </div> --}}
                </div>

                <!-- Right: Action Buttons -->
                <div class="grid grid-cols-2 sm:flex sm:flex-col gap-3 w-full md:w-auto shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-white/10">
                    {{-- <button type="button" @click="navigator.clipboard.writeText('{{ $employee->public_url }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="px-4 py-3 rounded-xl bg-sky-500/20 text-sky-300 hover:bg-sky-500/30 border border-sky-500/30 text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        <span x-text="copied ? 'COPIED!' : 'COPY LINK'"></span>
                    </button> --}}
                    <div class="w-28 h-28 sm:w-24 sm:h-24 rounded-2xl bg-white p-2.5 shrink-0 border border-white/20 shadow-2xl flex items-center justify-center">
                        <img src="{{ asset('storage/' . $employee->qr_code_path) }}" alt="QR Code" class="w-full h-full object-contain">
                    </div>
                    <a href="{{ asset('storage/' . $employee->qr_code_path) }}" download="qr-{{ $employee->slug }}.svg"
                        class="px-4 py-3 rounded-xl bg-white/10 text-white hover:bg-white/20 border border-white/10 text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>DOWNLOAD QR</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Main workspace: current appointment + upcoming queue.
             Desktop → two columns; mobile → stacked with a swipeable queue. --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8 items-start">

            {{-- Current appointment ------------------------------------------------- --}}
            <div class="lg:col-span-2 glass-card p-6 sm:p-8 lg:p-10">
                <div class="mb-8 border-b border-white/10 pb-6">
                    <h3 class="text-2xl font-black text-white">Current Appointment</h3>
                    <p class="text-xs text-slate-400 uppercase font-black tracking-widest mt-1">Next customer in queue</p>
                </div>

                @if($currentBooking)
                    <div class="bg-white/5 rounded-3xl p-6 sm:p-8 border border-white/10 mb-8">
                        <div class="flex flex-col sm:flex-row sm:items-start gap-6">
                            <div class="w-16 h-16 rounded-full bg-blue-500/15 flex items-center justify-center shrink-0">
                                <svg class="w-8 h-8 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div class="flex-1 space-y-5">
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Customer Name</p>
                                    <p class="text-2xl font-black text-white">{{ $currentBooking->customer_name ?? 'Walk-in' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Time Slot</p>
                                    <p class="text-lg font-bold text-white">{{ \Carbon\Carbon::parse($currentBooking->slot_start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($currentBooking->slot_end_time)->format('h:i A') }}</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-block px-4 py-1.5 rounded-lg text-[11px] font-black uppercase tracking-widest border {{ $typeStyles[$currentBooking->booking_type] ?? $typeStyles['normal'] }}">
                                        {{ ucfirst($currentBooking->booking_type) }}
                                    </span>
                                    @if($currentBooking->token_number)
                                        <span class="inline-block px-4 py-1.5 rounded-lg text-[11px] font-black uppercase tracking-widest bg-white/5 text-slate-300 border border-white/10">
                                            Token #{{ $currentBooking->token_number }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Complete / Skip / Cancel — stacked on mobile, side-by-side on larger screens.
                         Skip is the middle ground between the two: the customer could not be
                         served, the queue moves on, and they are told to rebook or call the shop. --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                        <form action="{{ route('employee.mark-done') }}" method="POST">
                            @csrf
                            <input type="hidden" name="booking_id" value="{{ $currentBooking->id }}">
                            <button type="submit" class="emp-btn-complete group w-full py-4 sm:py-5 px-4 rounded-2xl transition-all active:scale-[0.98] flex items-center justify-center gap-2.5">
                                <span class="emp-btn-ico w-9 h-9 rounded-full flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span class="font-black text-[13px] sm:text-sm uppercase tracking-wide whitespace-nowrap">Complete</span>
                            </button>
                        </form>
                        <form action="{{ route('employee.skip') }}" method="POST"
                              onsubmit="return confirm('Skip this appointment? The customer will be told you are unavailable and asked to rebook.');">
                            @csrf
                            <input type="hidden" name="booking_id" value="{{ $currentBooking->id }}">
                            <button type="submit" class="emp-btn-skip group w-full py-4 sm:py-5 px-4 rounded-2xl transition-all active:scale-[0.98] flex items-center justify-center gap-2.5">
                                <span class="emp-btn-ico w-9 h-9 rounded-full flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                                </span>
                                <span class="font-black text-[13px] sm:text-sm uppercase tracking-wide whitespace-nowrap">Skip</span>
                            </button>
                        </form>
                        <form action="{{ route('employee.cancel') }}" method="POST"
                              onsubmit="return confirm('Cancel this appointment? This cannot be undone.');">
                            @csrf
                            <input type="hidden" name="booking_id" value="{{ $currentBooking->id }}">
                            <button type="submit" class="emp-btn-cancel group w-full py-4 sm:py-5 px-4 rounded-2xl transition-all active:scale-[0.98] flex items-center justify-center gap-2.5">
                                <span class="emp-btn-ico w-9 h-9 rounded-full flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                </span>
                                <span class="font-black text-[13px] sm:text-sm uppercase tracking-wide whitespace-nowrap">Cancel</span>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="text-center py-14 bg-white/5 rounded-3xl border border-white/10">
                        <svg class="w-16 h-16 text-slate-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-lg font-bold text-white">No pending appointments.</p>
                        <p class="text-sm text-slate-400 mt-2">You have completed all active appointments for now.</p>
                    </div>
                @endif

                {{-- Pause / resume --}}
                <div class="border-t border-white/10 pt-6 mt-8">
                    <form action="{{ route('employee.toggle-pause') }}" method="POST">
                        @csrf
                        <button type="submit" class="{{ $employee->is_paused ? 'emp-btn-resume' : 'emp-btn-pause' }} group w-full py-4 sm:py-5 rounded-2xl flex items-center justify-center gap-3 font-black text-sm sm:text-base uppercase tracking-widest transition-all active:scale-[0.98]">
                            <span class="emp-btn-ico w-9 h-9 rounded-full flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                @if($employee->is_paused)
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                @else
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 5h4v14H6zM14 5h4v14h-4z"/></svg>
                                @endif
                            </span>
                            {{ $employee->is_paused ? 'Resume Appointments' : 'Pause Appointments (Take a Break)' }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Upcoming queue: next 5 ---------------------------------------------- --}}
            <div class="glass-card p-6 sm:p-8" x-data="queueSlider()">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-xl font-black text-white">Up Next</h3>
                        <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest mt-1">Next {{ $upcomingBookings->count() }} in queue</p>
                    </div>
                    {{-- Arrow controls (useful on desktop; swipe works on mobile) --}}
                    @if($upcomingBookings->count() > 1)
                        <div class="hidden sm:flex items-center gap-2">
                            <button type="button" @click="scrollByCard(-1)" class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 text-slate-300 hover:bg-white/10 hover:text-white flex items-center justify-center transition-all active:scale-95" aria-label="Previous">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button type="button" @click="scrollByCard(1)" class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 text-slate-300 hover:bg-white/10 hover:text-white flex items-center justify-center transition-all active:scale-95" aria-label="Next">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    @endif
                </div>

                @if($upcomingBookings->count())
                    <div x-ref="track" class="emp-queue-slider">
                        @foreach($upcomingBookings as $index => $booking)
                            <div class="emp-queue-card bg-white/5 rounded-2xl p-5 border border-white/10">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-500/15 text-blue-300 text-xs font-black">#{{ $index + 2 }}</span>
                                    <span class="inline-block px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border {{ $typeStyles[$booking->booking_type] ?? $typeStyles['normal'] }}">
                                        {{ ucfirst($booking->booking_type) }}
                                    </span>
                                </div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Customer</p>
                                <p class="text-lg font-black text-white truncate mb-4">{{ $booking->customer_name ?? 'Walk-in' }}</p>
                                <div class="flex items-center gap-2 text-slate-300">
                                    <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="text-sm font-bold">{{ \Carbon\Carbon::parse($booking->slot_start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($booking->slot_end_time)->format('h:i A') }}</span>
                                </div>
                                @if($booking->token_number)
                                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mt-3">Token #{{ $booking->token_number }}</p>
                                @endif

                                {{-- Skip / cancel this upcoming booking. Neither touches the
                                     token queue — only the head of the queue advances it. --}}
                                <div class="mt-4 pt-4 border-t border-white/10 grid grid-cols-2 gap-2">
                                    <form action="{{ route('employee.skip') }}" method="POST"
                                          onsubmit="return confirm('Skip this booking? The customer will be told you are unavailable and asked to rebook.');">
                                        @csrf
                                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                        <button type="submit" class="emp-btn-skip-soft w-full py-2.5 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                                            Skip
                                        </button>
                                    </form>
                                    <form action="{{ route('employee.cancel') }}" method="POST"
                                          onsubmit="return confirm('Cancel this booking? This cannot be undone.');">
                                        @csrf
                                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                        <button type="submit" class="emp-btn-cancel-soft w-full py-2.5 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Cancel
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="sm:hidden text-center text-[10px] text-slate-500 uppercase font-black tracking-widest mt-4">← Swipe to review →</p>
                @else
                    <div class="text-center py-12 bg-white/5 rounded-2xl border border-white/10">
                        <svg class="w-12 h-12 text-slate-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <p class="text-sm font-bold text-white">No one else waiting.</p>
                        <p class="text-xs text-slate-400 mt-1">Upcoming appointments will appear here.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Swipeable queue: horizontal snap-scroll on mobile, vertical stack on desktop. --}}
    <style>
        /* Action buttons — defined here (not via Tailwind color utilities) because the
           prebuilt CSS bundle ships only a limited palette; this guarantees the green
           and red always render regardless of the build. */
        .emp-btn-complete { background: linear-gradient(135deg, #34d399 0%, #059669 55%, #047857 100%) !important; color: #fff !important; box-shadow: 0 14px 30px -14px rgba(16,185,129,0.65); }
        .emp-btn-complete:hover { background: linear-gradient(135deg, #6ee7b7 0%, #10b981 55%, #059669 100%) !important; }
        .emp-btn-cancel { background: linear-gradient(135deg, #fb7185 0%, #ef4444 50%, #be123c 100%) !important; color: #fff !important; box-shadow: 0 14px 30px -14px rgba(244,63,94,0.65); }
        .emp-btn-cancel:hover { background: linear-gradient(135deg, #fda4af 0%, #f87171 50%, #e11d48 100%) !important; }
        .emp-btn-cancel-soft { background: linear-gradient(135deg, rgba(251,113,133,0.20), rgba(190,18,60,0.12)) !important; border: 1px solid rgba(251,113,133,0.40); color: #fecaca !important; }
        .emp-btn-cancel-soft:hover { background: linear-gradient(135deg, rgba(251,113,133,0.32), rgba(190,18,60,0.22)) !important; color: #fff !important; }
        .emp-btn-skip { background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 55%, #d97706 100%) !important; color: #fff !important; box-shadow: 0 14px 30px -14px rgba(245,158,11,0.65); }
        .emp-btn-skip:hover { background: linear-gradient(135deg, #fcd34d 0%, #fbbf24 55%, #f59e0b 100%) !important; }
        .emp-btn-skip-soft { background: linear-gradient(135deg, rgba(251,191,36,0.20), rgba(217,119,6,0.12)) !important; border: 1px solid rgba(251,191,36,0.40); color: #fde68a !important; }
        .emp-btn-skip-soft:hover { background: linear-gradient(135deg, rgba(251,191,36,0.32), rgba(217,119,6,0.22)) !important; color: #fff !important; }
        .emp-btn-pause { background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 55%, #ea580c 100%) !important; color: #fff !important; box-shadow: 0 14px 30px -14px rgba(245,158,11,0.6); }
        .emp-btn-pause:hover { background: linear-gradient(135deg, #fcd34d 0%, #fbbf24 55%, #f97316 100%) !important; }
        .emp-btn-resume { background: linear-gradient(135deg, #34d399 0%, #059669 55%, #047857 100%) !important; color: #fff !important; box-shadow: 0 14px 30px -14px rgba(16,185,129,0.65); }
        .emp-btn-resume:hover { background: linear-gradient(135deg, #6ee7b7 0%, #10b981 55%, #059669 100%) !important; }
        .emp-btn-ico { background: rgba(255,255,255,0.25); }

        .emp-queue-slider {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        @media (max-width: 1023px) {
            .emp-queue-slider {
                flex-direction: row;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
                gap: 12px;
                padding-bottom: 4px;
            }
            .emp-queue-slider::-webkit-scrollbar { display: none; }
            .emp-queue-card {
                scroll-snap-align: start;
                flex: 0 0 82%;
                min-width: 0;
            }
        }
        @media (min-width: 640px) and (max-width: 1023px) {
            .emp-queue-card { flex-basis: 45%; }
        }
    </style>

    {{--
        Realtime: the specialist's own feed.

        This screen is what somebody works from all day, and it used to only
        change when they reloaded it — a booking made thirty seconds ago was
        simply not there. It now follows two channels:

          private-employee.{id}  their bookings arriving and changing
          queue.{id}             their own queue counters moving

        Both just re-render #emp-live from the server (see Realtime.refresh), so
        there is one definition of how a queue looks and it stays in Blade.
    --}}
    <script>
        // Must go through whenRealtimeReady: the Echo bundle is a deferred module
        // and does not exist yet while this script is being parsed.
        window.whenRealtimeReady(function (Echo) {
            const employeeId = {{ $employee->id }};

            Echo.private(`employee.${employeeId}`)
                .listen('.booking.changed', (e) => {
                    window.Realtime.refresh('#emp-live');

                    const who = e.booking?.customer_name ?? 'A customer';
                    const messages = {
                        created:   `New booking — ${who}${e.booking?.token_number ? ' (token #' + e.booking.token_number + ')' : ''}`,
                        cancelled: e.actor === 'customer' ? `${who} cancelled their booking` : null,
                        // A skip performed at the counter, not by this specialist —
                        // their own skips already show a flash message.
                        skipped:   e.actor === 'employee' ? null : `${who} was skipped by the shop`,
                        expired:   null,
                    };

                    // Only announce what the specialist did NOT just do themselves;
                    // their own actions already show a confirmation.
                    if (messages[e.action]) {
                        window.Realtime.toast(messages[e.action], e.action === 'created' ? 'success' : 'info');
                    }
                });

            Echo.channel(`queue.${employeeId}`)
                .listen('.queue.updated', () => window.Realtime.refresh('#emp-live'));
        });

        function queueSlider() {
            return {
                scrollByCard(dir) {
                    const track = this.$refs.track;
                    if (!track) return;
                    const card = track.querySelector('.emp-queue-card');
                    // On desktop the list is vertical; on mobile it's horizontal.
                    const horizontal = window.matchMedia('(max-width: 1023px)').matches;
                    const step = card ? (horizontal ? card.offsetWidth + 12 : card.offsetHeight + 12) : 200;
                    track.scrollBy(horizontal ? { left: dir * step, behavior: 'smooth' } : { top: dir * step, behavior: 'smooth' });
                }
            };
        }
    </script>
</x-employee-layout>
