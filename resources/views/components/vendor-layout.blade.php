<x-app-layout>
    <div class="container mx-auto px-4 md:px-8 py-8 md:py-12 w-full max-w-8xl">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-12 min-h-[calc(100vh-200px)]">
            <!-- Vendor Sidebar -->
            <div class="lg:col-span-1">
                <div class="space-y-6 md:sticky md:top-28 max-h-[calc(100vh-4rem)] overflow-y-auto pb-8 scrollbar-hide">
                    <div
                        class="theme-nav p-2 shadow-2xl shadow-black/5 border border-white/20 backdrop-blur-3xl rounded-[2.5rem] flex flex-col gap-2">
                        @php
                        $vendor = auth()->user()->vendor;
                        @endphp

                        <a href="{{ route('vendor.dashboard') }}"
                            class="flex items-center gap-4 p-4 rounded-2xl transition-all {{ request()->routeIs('vendor.dashboard') ? 'bg-slate-900 text-white shadow-xl' : 'text-slate-500 hover:bg-theme-accent' }}">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span class="font-black italic uppercase tracking-widest text-[11px]">Dashboard</span>
                        </a>

                        <a href="{{ route('vendor.employees.index') }}"
                            class="flex items-center gap-4 p-4 rounded-2xl transition-all {{ request()->routeIs('vendor.employees.*') ? 'bg-slate-900 text-white shadow-xl' : 'text-slate-500 hover:bg-theme-accent' }}">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="font-black italic uppercase tracking-widest text-[11px]">Employees</span>
                        </a>

                        <a href="{{ route('vendor.bookings.index') }}"
                            class="flex items-center gap-4 p-4 rounded-2xl transition-all {{ request()->routeIs('vendor.bookings.index') ? 'bg-slate-900 text-white shadow-xl' : 'text-slate-500 hover:bg-theme-accent' }}">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="font-black italic uppercase tracking-widest text-[11px]">Bookings</span>
                        </a>

                        <a href="{{ route('vendor.profile.edit') }}"
                            class="flex items-center gap-4 p-4 rounded-2xl transition-all {{ request()->routeIs('vendor.profile.*') ? 'bg-slate-900 text-white shadow-xl' : 'text-slate-500 hover:bg-theme-accent' }}">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="font-black italic uppercase tracking-widest text-[11px]">Global Settings</span>
                        </a>
                    </div>

                    <!-- Shop Status Toggle -->
                    <div
                        class="theme-nav p-8 shadow-2xl shadow-black/5 border border-slate-100 rounded-[2.5rem] bg-white">
                        <div class="flex items-center justify-between mb-5">
                            <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] italic">
                                Registry Status</h4>
                            @if(!$vendor->isProfileComplete())
                            <span class="text-[8px] font-black text-rose-500 uppercase italic animate-pulse">Settings Required</span>
                            @endif
                        </div>
                        
                        <div
                            class="flex items-center justify-between {{ $vendor->isEffectivelyOpen() ? 'bg-emerald-50 border-emerald-100' : 'bg-slate-50 border-slate-100' }} p-4 rounded-2xl border transition-colors duration-300 {{ !$vendor->isProfileComplete() ? 'opacity-50 grayscale' : '' }}">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-2.5 h-2.5 rounded-full {{ $vendor->isEffectivelyOpen() ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)] animate-pulse' : 'bg-slate-400' }}">
                                </div>
                                <span id="shop-status-text"
                                    class="text-[10px] font-black {{ $vendor->isEffectivelyOpen() ? 'text-emerald-700' : 'text-slate-500' }} uppercase tracking-widest italic transition-colors delay-100">
                                    @if($vendor->isEffectivelyOpen())
                                        OPERATIONAL
                                    @elseif($vendor->is_open)
                                        OUTSIDE HOURS
                                    @else
                                        DEACTIVATED
                                    @endif
                                </span>
                            </div>
                            <form action="{{ route('vendor.status.toggle') }}" method="POST" id="status-toggle-form"
                                class="m-0 flex">
                                @csrf
                                <button type="submit" {{ !$vendor->isProfileComplete() ? 'disabled' : '' }}
                                    class="w-14 h-8 {{ $vendor->is_open ? 'bg-emerald-500 shadow-md shadow-emerald-500/20' : 'bg-slate-200 border border-slate-300' }} rounded-full relative transition-all duration-300 px-1 flex items-center {{ !$vendor->isProfileComplete() ? 'cursor-not-allowed' : '' }}">
                                    <div
                                        class="w-6 h-6 {{ $vendor->is_open ? 'bg-white translate-x-6' : 'bg-white shadow text-slate-400 translate-x-0' }} rounded-full flex items-center justify-center transition-transform duration-300 ease-out">
                                        @if($vendor->is_open)
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        @else
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        @endif
                                    </div>
                                </button>
                            </form>
                        </div>
                        @if(!$vendor->isProfileComplete())
                        <p class="text-[9px] font-black text-rose-400 uppercase italic mt-4 tracking-tighter leading-tight">Complete Global Settings to activate shop functions.</p>
                        @endif
                    </div>

                    <!-- Side QR Code -->
                    <div
                        class="theme-nav p-8 shadow-2xl shadow-black/5 border border-white/20 backdrop-blur-3xl rounded-[2.5rem]">
                        <h4 class="text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] mb-6 italic">Access
                            Matrix</h4>
                        <div class="flex flex-col items-center gap-6">
                            <div
                                class="w-full aspect-square bg-white/50 p-6 rounded-3xl border border-white/30 shadow-inner">
                                <img src="{{ asset('storage/' . $vendor->qr_code_path) }}"
                                    class="w-full h-full object-contain rounded-xl">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-3">
                @if(session('success'))
                <div
                    class="bg-emerald-600 text-white p-6 rounded-[2rem] text-xs font-black uppercase tracking-widest italic mb-8 shadow-xl shadow-emerald-500/20">
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div
                    class="bg-rose-600 text-white p-6 rounded-[2rem] text-xs font-black uppercase tracking-widest italic mb-8 shadow-xl shadow-rose-500/20">
                    {{ session('error') }}
                </div>
                @endif

                {{ $slot }}
            </div>
        </div>
    </div>
</x-app-layout>