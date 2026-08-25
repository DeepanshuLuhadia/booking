<x-app-layout panelType="employee">
    <x-slot name="mobileMenu">
        <div class="flex flex-col gap-2 mb-6">
            <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] px-6 mb-3">Employee Menu</h4>
            <a href="{{ route('employee.dashboard') }}"
                class="flex items-center gap-4 px-6 py-4 rounded-2xl transition-all {{ request()->routeIs('employee.dashboard') ? 'bg-white/5 text-white shadow-sm' : 'text-slate-300 hover:bg-white/5/50' }}">
                <svg class="h-5 w-5 {{ request()->routeIs('employee.dashboard') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="font-black italic uppercase tracking-widest text-[11px] whitespace-nowrap">Dashboard</span>
            </a>
            @php
                $menuUnreadNotifications = auth()->user()->unreadNotifications()->count();
            @endphp
            <a href="{{ route('employee.notifications.index') }}"
                class="flex items-center gap-4 px-6 py-4 rounded-2xl transition-all {{ request()->routeIs('employee.notifications.*') ? 'bg-white/5 text-white shadow-sm' : 'text-slate-300 hover:bg-white/5/50' }}">
                <svg class="h-5 w-5 {{ request()->routeIs('employee.notifications.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span class="font-black italic uppercase tracking-widest text-[11px] whitespace-nowrap">Notifications</span>
                @if($menuUnreadNotifications > 0)
                    <span class="ml-auto px-2 py-0.5 rounded-md bg-blue-500 text-white text-[9px] font-black tabular-nums">{{ $menuUnreadNotifications }}</span>
                @endif
            </a>
        </div>
    </x-slot>

    <div class="flex min-h-screen">
        <!-- Sidebar Navigation (Hidden on Mobile, Fixed on Desktop) -->
        <aside class="hidden lg:flex flex-col w-72 fixed left-0 top-0 bottom-0 bg-white/5 border-r border-white/10 z-[150] overflow-y-auto no-scrollbar pt-8 px-6 pb-6">
            <a href="{{ route('employee.dashboard') }}" class="flex items-center gap-3 mb-10 pl-2 group">
                <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white text-xl font-black transition-transform group-hover:rotate-12 group-hover:scale-110">B</div>
                <span class="text-xl font-black tracking-tighter text-white whitespace-nowrap">
                    {{ config('brand.logo_prefix') }}<span class="text-blue-600">{{ config('brand.logo_suffix') }}</span>
                </span>
            </a>
            <div class="flex flex-col h-full justify-between">
                <div class="flex flex-col gap-2">
                    <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] px-4 mb-5 italic">Employee Panel</h4>
                    
                    <a href="{{ route('employee.dashboard') }}"
                        class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('employee.dashboard') ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/30' : 'text-slate-400 hover:bg-white/5 hover:translate-x-1' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        <span class="font-black italic uppercase tracking-widest text-[10px] whitespace-nowrap">Dashboard</span>
                    </a>

                    @php
                        $sidebarUnreadNotifications = auth()->user()->unreadNotifications()->count();
                    @endphp
                    <a href="{{ route('employee.notifications.index') }}"
                        class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('employee.notifications.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/30' : 'text-slate-400 hover:bg-white/5 hover:translate-x-1' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        <span class="font-black italic uppercase tracking-widest text-[10px] whitespace-nowrap">Notifications</span>
                        @if($sidebarUnreadNotifications > 0)
                            <span class="ml-auto px-2 py-0.5 rounded-md bg-blue-500 text-white text-[9px] font-black tabular-nums">{{ $sidebarUnreadNotifications }}</span>
                        @endif
                    </a>
                </div>
                
                <div class="mt-auto p-4 flex flex-col items-center">
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="w-full py-3 rounded-xl bg-white/5 text-slate-300 hover:bg-rose-500/10 hover:text-rose-500 text-[10px] font-black uppercase tracking-[0.2em] italic flex items-center justify-center gap-3 transition-colors">
                            Logout
                        </button>
                    </form>
                </div>
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
