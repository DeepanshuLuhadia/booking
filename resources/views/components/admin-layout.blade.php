<x-app-layout panelType="admin">
    <x-slot name="mobileMenu">
        <div class="flex flex-col gap-2 mb-6">
            <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] px-6 mb-3">Admin Menu</h4>
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-4 px-6 py-4 rounded-2xl transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-white/5 text-white shadow-sm' : 'text-slate-300 hover:bg-white/5/50' }}">
                <svg class="h-5 w-5 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="font-black italic uppercase tracking-widest text-[11px] whitespace-nowrap">Overview</span>
            </a>
            <a href="{{ route('admin.vendors.index') }}"
                class="flex items-center gap-4 px-6 py-4 rounded-2xl transition-all {{ request()->routeIs('admin.vendors.*') ? 'bg-white/5 text-white shadow-sm' : 'text-slate-300 hover:bg-white/5/50' }}">
                <svg class="h-5 w-5 {{ request()->routeIs('admin.vendors.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="font-black italic uppercase tracking-widest text-[11px] whitespace-nowrap">Vendors</span>
            </a>
            <a href="{{ route('admin.plans.index') }}"
                class="flex items-center gap-4 px-6 py-4 rounded-2xl transition-all {{ request()->routeIs('admin.plans.*') ? 'bg-white/5 text-white shadow-sm' : 'text-slate-300 hover:bg-white/5/50' }}">
                <svg class="h-5 w-5 {{ request()->routeIs('admin.plans.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
                <span class="font-black italic uppercase tracking-widest text-[11px] whitespace-nowrap">Plans</span>
            </a>
            <a href="{{ route('admin.settlements.index') }}"
                class="flex items-center gap-4 px-6 py-4 rounded-2xl transition-all {{ request()->routeIs('admin.settlements.*') ? 'bg-white/5 text-white shadow-sm' : 'text-slate-300 hover:bg-white/5/50' }}">
                <svg class="h-5 w-5 {{ request()->routeIs('admin.settlements.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-black italic uppercase tracking-widest text-[11px] whitespace-nowrap">Settlements</span>
            </a>
            <a href="{{ route('admin.reviews.index') }}"
                class="flex items-center gap-4 px-6 py-4 rounded-2xl transition-all {{ request()->routeIs('admin.reviews.*') ? 'bg-white/5 text-white shadow-sm' : 'text-slate-300 hover:bg-white/5/50' }}">
                <svg class="h-5 w-5 {{ request()->routeIs('admin.reviews.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                <span class="font-black italic uppercase tracking-widest text-[11px] whitespace-nowrap">Reviews</span>
            </a>
            <div class="h-px bg-white/10 mx-6 my-2"></div>
        </div>
    </x-slot>

    <div class="flex min-h-screen overflow-x-hidden">
        <!-- Dashboard Sidebar (Fixed Desktop Panel) -->
        <aside class="hidden lg:flex flex-col w-72 fixed left-0 top-0 bottom-0 bg-white/5 border-r border-white/10 z-[150] overflow-y-auto no-scrollbar pt-8 px-6 pb-6">
            <a href="/" class="flex items-center gap-3 mb-10 pl-2 group">
                <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white text-xl font-black transition-transform group-hover:rotate-12 group-hover:scale-110">B</div>
                <span class="text-xl font-black tracking-tighter text-white whitespace-nowrap">
                    BOOK<span class="text-blue-600">APPOINTMENT</span>
                </span>
            </a>
            <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] px-4 mb-5 italic">Admin Authority</h4>
            
            <div class="flex flex-col gap-2">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('admin.dashboard') ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/30' : 'text-slate-400 hover:bg-white/5 hover:translate-x-1' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    <span class="font-black italic uppercase tracking-widest text-[10px] whitespace-nowrap">Overview</span>
                </a>

                <a href="{{ route('admin.vendors.index') }}"
                    class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('admin.vendors.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/30' : 'text-slate-400 hover:bg-white/5 hover:translate-x-1' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    <span class="font-black italic uppercase tracking-widest text-[10px] whitespace-nowrap">Vendors</span>
                </a>

                <a href="{{ route('admin.plans.index') }}"
                    class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('admin.plans.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/30' : 'text-slate-400 hover:bg-white/5 hover:translate-x-1' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                    <span class="font-black italic uppercase tracking-widest text-[10px] whitespace-nowrap">Plans</span>
                </a>

                <a href="{{ route('admin.settlements.index') }}"
                    class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('admin.settlements.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/30' : 'text-slate-400 hover:bg-white/5 hover:translate-x-1' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="font-black italic uppercase tracking-widest text-[10px] whitespace-nowrap">Settlements</span>
                </a>

                <a href="{{ route('admin.reviews.index') }}"
                    class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('admin.reviews.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/30' : 'text-slate-400 hover:bg-white/5 hover:translate-x-1' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                    <span class="font-black italic uppercase tracking-widest text-[10px] whitespace-nowrap">Reviews</span>
                </a>
            </div>
            
            <div class="mt-auto p-4 flex flex-col items-center">
                 <div class="w-16 h-1 rounded-full bg-white/10 mb-4"></div>
                 <p class="text-[8px] font-black text-slate-300 uppercase tracking-[0.3em]">ADMIN CORE</p>
            </div>
        </aside>

        <!-- Main Workspace -->
        <main class="flex-1 lg:pl-72 flex flex-col w-full min-w-0">
            <div class="w-full h-full pt-32 pb-20 px-4 sm:px-6 md:px-10 lg:px-16">
                @if(session('success'))
                    <div class="bg-emerald-500 text-white p-6 rounded-[2rem] text-xs font-black uppercase tracking-widest italic mb-10 shadow-xl shadow-emerald-500/10">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-rose-500 text-white p-6 rounded-[2rem] text-xs font-black uppercase tracking-widest italic mb-10 shadow-xl shadow-rose-500/10">
                        {{ session('error') }}
                    </div>
                @endif
                {{ $slot }}
            </div>
        </main>
    </div>
</x-app-layout>
