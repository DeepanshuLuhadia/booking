<x-app-layout panelType="vendor">
    <x-slot name="mobileMenu">
        <div class="flex flex-col gap-2 mb-6">
            <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] px-6 mb-3">Vendor Menu</h4>
            <a href="{{ route('vendor.dashboard') }}"
                class="flex items-center gap-4 px-6 py-4 rounded-2xl transition-all {{ request()->routeIs('vendor.dashboard') ? 'bg-white/5 text-white shadow-sm' : 'text-slate-300 hover:bg-white/5/50' }}">
                <svg class="h-5 w-5 {{ request()->routeIs('vendor.dashboard') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="font-black italic uppercase tracking-widest text-[11px] whitespace-nowrap">Dashboard</span>
            </a>
            <a href="{{ route('vendor.employees.index') }}"
                class="flex items-center gap-4 px-6 py-4 rounded-2xl transition-all {{ request()->routeIs('vendor.employees.*') ? 'bg-white/5 text-white shadow-sm' : 'text-slate-300 hover:bg-white/5/50' }}">
                <svg class="h-5 w-5 {{ request()->routeIs('vendor.employees.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="font-black italic uppercase tracking-widest text-[11px] whitespace-nowrap">Employees</span>
            </a>
            <a href="{{ route('vendor.bookings.index') }}"
                class="flex items-center gap-4 px-6 py-4 rounded-2xl transition-all {{ request()->routeIs('vendor.bookings.index') ? 'bg-white/5 text-white shadow-sm' : 'text-slate-300 hover:bg-white/5/50' }}">
                <svg class="h-5 w-5 {{ request()->routeIs('vendor.bookings.index') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="font-black italic uppercase tracking-widest text-[11px] whitespace-nowrap">Bookings</span>
            </a>
            @php
                $menuUnreadNotifications = auth()->user()->unreadNotifications()->count();
            @endphp
            <a href="{{ route('vendor.notifications.index') }}"
                class="flex items-center gap-4 px-6 py-4 rounded-2xl transition-all {{ request()->routeIs('vendor.notifications.*') ? 'bg-white/5 text-white shadow-sm' : 'text-slate-300 hover:bg-white/5/50' }}">
                <svg class="h-5 w-5 {{ request()->routeIs('vendor.notifications.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span class="font-black italic uppercase tracking-widest text-[11px] whitespace-nowrap">Notifications</span>
                @if($menuUnreadNotifications > 0)
                    <span class="ml-auto px-2 py-0.5 rounded-md bg-blue-500 text-white text-[9px] font-black tabular-nums">{{ $menuUnreadNotifications }}</span>
                @endif
            </a>
            {{-- The online-payments ledger. Only shown to shops that take direct
                 UPI payments — it has no meaning for the rest.

                 The badge counts credits the shop has not ticked off yet. No
                 booking waits on them, but each one is money that has left a
                 customer's account, so it is worth a nudge. --}}
            @if(auth()->user()?->vendor?->acceptsDirectAdvance())
            @php
                $awaitingPayments = \App\Models\Booking::where('vendor_id', auth()->user()->vendor->id)
                    ->awaitingPaymentVerification()
                    ->count();
            @endphp
            <a href="{{ route('vendor.payments.index') }}"
                class="flex items-center gap-4 px-6 py-4 rounded-2xl transition-all {{ request()->routeIs('vendor.payments.*') ? 'bg-white/5 text-white shadow-sm' : 'text-slate-300 hover:bg-white/5/50' }}">
                <svg class="h-5 w-5 {{ request()->routeIs('vendor.payments.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span class="font-black italic uppercase tracking-widest text-[11px] whitespace-nowrap">Payments</span>
                @if($awaitingPayments > 0)
                    <span class="ml-auto px-2 py-0.5 rounded-md bg-amber-500 text-slate-900 text-[9px] font-black tabular-nums">{{ $awaitingPayments }}</span>
                @endif
            </a>
            @endif
            {{-- Reports are a free-trial and Premium feature; the route enforces
                 that, this only keeps a dead link out of the menu. --}}
            @if(auth()->user()?->vendor?->hasReportAccess())
            <a href="{{ route('vendor.reports.index') }}"
                class="flex items-center gap-4 px-6 py-4 rounded-2xl transition-all {{ request()->routeIs('vendor.reports.*') ? 'bg-white/5 text-white shadow-sm' : 'text-slate-300 hover:bg-white/5/50' }}">
                <svg class="h-5 w-5 {{ request()->routeIs('vendor.reports.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="font-black italic uppercase tracking-widest text-[11px] whitespace-nowrap">Reports</span>
            </a>
            @endif
            <a href="{{ route('vendor.reviews.index') }}"
                class="flex items-center gap-4 px-6 py-4 rounded-2xl transition-all {{ request()->routeIs('vendor.reviews.*') ? 'bg-white/5 text-white shadow-sm' : 'text-slate-300 hover:bg-white/5/50' }}">
                <svg class="h-5 w-5 {{ request()->routeIs('vendor.reviews.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                <span class="font-black italic uppercase tracking-widest text-[11px] whitespace-nowrap">Reviews</span>
            </a>
            <a href="{{ route('vendor.profile.edit') }}"
                class="flex items-center gap-4 px-6 py-4 rounded-2xl transition-all {{ request()->routeIs('vendor.profile.*') ? 'bg-white/5 text-white shadow-sm' : 'text-slate-300 hover:bg-white/5/50' }}">
                <svg class="h-5 w-5 {{ request()->routeIs('vendor.profile.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="font-black italic uppercase tracking-widest text-[11px] whitespace-nowrap">Settings</span>
            </a>
            <a href="/" target="_blank"
                class="flex items-center gap-4 px-6 py-4 rounded-2xl transition-all text-slate-300 hover:bg-white/5/50">
                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span class="font-black italic uppercase tracking-widest text-[11px] whitespace-nowrap">Website</span>
            </a>
            <div class="h-px bg-white/10 mx-6 my-2"></div>
            @php $vendor = auth()->user()->vendor; @endphp
            @if($vendor)
            <div class="px-6 py-4 flex flex-col gap-4">
                <!-- Shop Status Toggle -->
                <div class="p-4 rounded-2xl bg-white/5 border border-white/10 italic space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[9px] font-black text-slate-400 uppercase">Registry Status</span>
                    </div>
                    <div class="flex flex-col gap-2">
                        <form method="POST" action="{{ route('vendor.status.toggle') }}">
                            @csrf
                            <input type="hidden" name="type" value="open">
                            <button type="submit" class="w-full px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest {{ $vendor->is_open && !$vendor->bookings_paused ? 'bg-emerald-500 text-white shadow-[0_0_8px_rgba(52,211,153,0.8)]' : 'bg-white/10 text-white/50 hover:bg-white/20' }}">
                                ✅ Open
                            </button>
                        </form>
                        <form method="POST" action="{{ route('vendor.status.toggle') }}">
                            @csrf
                            <input type="hidden" name="type" value="pause">
                            <button type="submit" class="w-full px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest {{ $vendor->bookings_paused ? 'bg-amber-500 text-white shadow-[0_0_8px_rgba(245,158,11,0.8)]' : 'bg-white/10 text-white/50 hover:bg-white/20' }}">
                                ⏸ Pause Bookings
                            </button>
                        </form>
                        <form method="POST" action="{{ route('vendor.status.toggle') }}">
                            @csrf
                            <input type="hidden" name="type" value="close">
                            <button type="submit" class="w-full px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest {{ !$vendor->is_open ? 'bg-rose-500 text-white shadow-[0_0_8px_rgba(244,63,94,0.8)]' : 'bg-white/10 text-white/50 hover:bg-white/20' }}">
                                ❌ Close Today
                            </button>
                        </form>
                    </div>
                </div>

                <div class="flex items-center gap-4 p-2">
                        <img src="{{ asset('storage/' . $vendor->qr_code_path) }}" class="w-12 h-12 rounded-xl border border-white/10 shadow-sm">
                        <div class="flex flex-col gap-1">
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest italic">MATRIX ID</span>
                            <span class="text-[10px] font-black text-white mono">#{{ $vendor->id }}</span>
                            <a href="{{ asset('storage/' . $vendor->qr_code_path) }}" download="QR_{{ $vendor->business_name }}.png" class="text-[9px] font-black text-blue-600 hover:text-blue-800 uppercase tracking-widest italic flex items-center gap-1 mt-1 transition-colors">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download
                            </a>
                        </div>
                </div>
            </div>
            @endif
        </div>
    </x-slot>

    <div class="flex min-h-screen">
        <!-- Sidebar Navigation (Hidden on Mobile, Fixed on Desktop) -->
        <aside class="hidden lg:flex flex-col w-72 fixed left-0 top-0 bottom-0 bg-white/5 border-r border-white/10 z-[150] overflow-y-auto no-scrollbar pt-8 px-6 pb-6">
            <a href="/" class="flex items-center gap-3 mb-10 pl-2 group">
                <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white text-xl font-black transition-transform group-hover:rotate-12 group-hover:scale-110">B</div>
                <span class="text-xl font-black tracking-tighter text-white whitespace-nowrap">
                    {{ config('brand.logo_prefix') }}<span class="text-blue-600">{{ config('brand.logo_suffix') }}</span>
                </span>
            </a>
            <div class="flex flex-col h-full justify-between">
                <div class="flex flex-col gap-2">
                    @php $vendor = auth()->user()->vendor; @endphp
                    
                    <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] px-4 mb-5 italic">Registry Panel</h4>
                    
                    <a href="{{ route('vendor.dashboard') }}"
                        class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('vendor.dashboard') ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/30' : 'text-slate-400 hover:bg-white/5 hover:translate-x-1' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        <span class="font-black italic uppercase tracking-widest text-[10px] whitespace-nowrap">Dashboard</span>
                    </a>

                    <a href="{{ route('vendor.employees.index') }}"
                        class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('vendor.employees.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/30' : 'text-slate-400 hover:bg-white/5 hover:translate-x-1' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        <span class="font-black italic uppercase tracking-widest text-[10px] whitespace-nowrap">Employees</span>
                    </a>

                    <a href="{{ route('vendor.bookings.index') }}"
                        class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('vendor.bookings.index') ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/30' : 'text-slate-400 hover:bg-white/5 hover:translate-x-1' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span class="font-black italic uppercase tracking-widest text-[10px] whitespace-nowrap">Bookings</span>
                    </a>

                    {{-- Re-queried rather than shared with the mobile menu —
                         same reasoning as the payments badge below. --}}
                    @php
                        $sidebarUnreadNotifications = auth()->user()->unreadNotifications()->count();
                    @endphp
                    <a href="{{ route('vendor.notifications.index') }}"
                        class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('vendor.notifications.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/30' : 'text-slate-400 hover:bg-white/5 hover:translate-x-1' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        <span class="font-black italic uppercase tracking-widest text-[10px] whitespace-nowrap">Notifications</span>
                        @if($sidebarUnreadNotifications > 0)
                            <span class="ml-auto px-2 py-0.5 rounded-md bg-blue-500 text-white text-[9px] font-black tabular-nums">{{ $sidebarUnreadNotifications }}</span>
                        @endif
                    </a>

                    {{-- Online payments — mirrors the desktop sidebar item.
                         The count is re-queried rather than shared with it: the
                         two menus render in separate scopes and a variable leaked
                         between them would go stale on the pages that only draw
                         one of them. --}}
                    @if($vendor?->acceptsDirectAdvance())
                    <a href="{{ route('vendor.payments.index') }}"
                        class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('vendor.payments.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/30' : 'text-slate-400 hover:bg-white/5 hover:translate-x-1' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                        <span class="font-black italic uppercase tracking-widest text-[10px] whitespace-nowrap">Payments</span>
                        @php
                            $mobileAwaitingPayments = \App\Models\Booking::where('vendor_id', $vendor->id)
                                ->awaitingPaymentVerification()
                                ->count();
                        @endphp
                        @if($mobileAwaitingPayments > 0)
                            <span class="ml-auto px-2 py-0.5 rounded-md bg-amber-500 text-slate-900 text-[9px] font-black tabular-nums">{{ $mobileAwaitingPayments }}</span>
                        @endif
                    </a>
                    @endif

                    @if($vendor?->hasReportAccess())
                    <a href="{{ route('vendor.reports.index') }}"
                        class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('vendor.reports.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/30' : 'text-slate-400 hover:bg-white/5 hover:translate-x-1' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <span class="font-black italic uppercase tracking-widest text-[10px] whitespace-nowrap">Reports</span>
                    </a>
                    @endif

                    <a href="{{ route('vendor.reviews.index') }}"
                        class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('vendor.reviews.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/30' : 'text-slate-400 hover:bg-white/5 hover:translate-x-1' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                        <span class="font-black italic uppercase tracking-widest text-[10px] whitespace-nowrap">Reviews</span>
                    </a>

                    <a href="{{ route('vendor.profile.edit') }}"
                        class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('vendor.profile.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/30' : 'text-slate-400 hover:bg-white/5 hover:translate-x-1' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span class="font-black italic uppercase tracking-widest text-[10px] whitespace-nowrap">Settings</span>
                    </a>

                    <a href="/" target="_blank"
                        class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 text-slate-400 hover:bg-white/5 hover:translate-x-1">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        <span class="font-black italic uppercase tracking-widest text-[10px] whitespace-nowrap">Website</span>
                    </a>
                </div>

                <div class="flex flex-col gap-6 mt-6">
                    <!-- Shop Status Toggle -->
                    <div class="p-5 rounded-3xl bg-white/5 border border-white/10 italic space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-black text-slate-400 uppercase">Registry Status</span>
                        </div>
                        <div class="flex flex-col gap-2">
                            <form method="POST" action="{{ route('vendor.status.toggle') }}">
                                @csrf
                                <input type="hidden" name="type" value="open">
                                <button type="submit" class="w-full px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest {{ $vendor->is_open && !$vendor->bookings_paused ? 'bg-emerald-500 text-white shadow-[0_0_8px_rgba(52,211,153,0.8)]' : 'bg-white/10 text-white/50 hover:bg-white/20' }}">
                                    ✅ Open
                                </button>
                            </form>
                            <form method="POST" action="{{ route('vendor.status.toggle') }}">
                                @csrf
                                <input type="hidden" name="type" value="pause">
                                <button type="submit" class="w-full px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest {{ $vendor->bookings_paused ? 'bg-amber-500 text-white shadow-[0_0_8px_rgba(245,158,11,0.8)]' : 'bg-white/10 text-white/50 hover:bg-white/20' }}">
                                    ⏸ Pause Bookings
                                </button>
                            </form>
                            <form method="POST" action="{{ route('vendor.status.toggle') }}">
                                @csrf
                                <input type="hidden" name="type" value="close">
                                <button type="submit" class="w-full px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest {{ !$vendor->is_open ? 'bg-rose-500 text-white shadow-[0_0_8px_rgba(244,63,94,0.8)]' : 'bg-white/10 text-white/50 hover:bg-white/20' }}">
                                    ❌ Close Today
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 px-4 pb-2">
                         <img src="{{ asset('storage/' . $vendor->qr_code_path) }}" class="w-16 h-16 rounded-xl border border-white/10 shadow-sm">
                         <div class="flex flex-col gap-1">
                             <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest italic">MATRIX ID</span>
                             <span class="text-[10px] font-black text-white mono">#{{ $vendor->id }}</span>
                             <a href="{{ asset('storage/' . $vendor->qr_code_path) }}" download="QR_{{ $vendor->business_name }}.png" class="text-[9px] font-black text-blue-600 hover:text-blue-800 uppercase tracking-widest italic flex items-center gap-1 mt-1 transition-colors">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Download
                             </a>
                         </div>
                     </div>
                </div>
            </div>
        </aside>

        <!-- Main Workspace (Center Content Always Full Width) -->
        <main class="flex-1 lg:pl-72 flex flex-col w-full min-w-0">
            {{-- `panel-main` carries the top-bar clearance (see the inline
                 stylesheet in app-layout). It is a plain class rather than
                 Tailwind utilities because the bar's height changes at two
                 breakpoints and an unbuilt utility here means the page heading
                 lands underneath the header. --}}
            <div class="panel-main w-full h-full pb-20 px-4 sm:px-6 md:px-10 lg:px-16">
                {{-- @if(session('success'))
                    <div class="bg-emerald-500 text-white p-6 rounded-[2rem] text-xs font-black uppercase tracking-widest italic mb-10 shadow-xl shadow-emerald-500/10">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-rose-500 text-white p-6 rounded-[2rem] text-xs font-black uppercase tracking-widest italic mb-10 shadow-xl shadow-rose-500/10">
                        {{ session('error') }}
                    </div>
                @endif
                --}}
                
                {{ $slot }}
            </div>
        </main>
    </div>

    @if(session('business_live'))
    @php $liveVendor = auth()->user()->vendor; @endphp
    {{-- The moment of going live — shown exactly once, ever.

         Fired by whichever save cleared the last listing blocker (see
         EmployeeController::store / ProfileController::update); the once is
         `vendors.live_celebrated_at`, stamped before this flash was set, so a
         refresh, another device or a later edit can never replay it. In the
         layout rather than a page because the two completing steps land on
         different pages. --}}
    <div x-data="{ showLive: true }"
         x-show="showLive"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="app-modal">

        <div @click="showLive = false" class="app-modal__backdrop bg-slate-900/70 backdrop-blur-xl"></div>

        <div class="app-modal__panel max-w-lg custom-scrollbar border border-emerald-500/30 rounded-[2.5rem] p-6 sm:p-8 shadow-2xl text-white text-center" style="background-color:#0a0f2c;">
            <div class="text-5xl mb-4">🎉</div>

            <div class="flex items-center justify-center gap-3 mb-2">
                <span class="w-8 h-1 bg-emerald-500 rounded-full"></span>
                <span class="text-emerald-400 font-black text-[9px] uppercase tracking-widest italic">Setup Complete</span>
                <span class="w-8 h-1 bg-emerald-500 rounded-full"></span>
            </div>

            <h3 class="text-2xl sm:text-3xl font-black italic tracking-tighter uppercase mb-3">
                Your Business Is <span class="text-emerald-400">Live!</span>
            </h3>
            <p class="text-xs sm:text-sm font-medium text-white/70 leading-relaxed mb-7">
                Congratulations{{ $liveVendor?->business_name ? ', ' . $liveVendor->business_name : '' }} — everything
                customers need is in place. Your business now appears on the listing page, and people can book
                appointments with you right away.
            </p>

            <div class="space-y-3">
                @if($liveVendor)
                <a href="{{ route('vendor.show', $liveVendor->slug) }}" target="_blank" rel="noopener"
                   class="w-full h-14 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-900 font-black uppercase tracking-widest text-xs flex items-center justify-center gap-2 transition-all hover:opacity-90">
                    See Your Public Page
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
                @endif
                <button @click="showLive = false"
                        class="w-full h-12 rounded-xl bg-white/5 border border-white/10 text-white/40 hover:text-white/70 font-black uppercase tracking-widest text-[10px] flex items-center justify-center transition-all">
                    Continue To Dashboard
                </button>
            </div>
        </div>
    </div>
    @endif
</x-app-layout>