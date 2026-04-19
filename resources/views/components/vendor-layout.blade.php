<x-app-layout>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-12 min-h-[calc(100vh-200px)]">
        <!-- Vendor Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <div class="theme-nav p-2 shadow-2xl shadow-black/5 border border-white/20 backdrop-blur-3xl rounded-[2.5rem] sticky top-24 flex flex-col gap-2">
                @php
                    $vendor = auth()->user()->vendor;
                @endphp
                
                <a href="{{ route('vendor.dashboard') }}" 
                   class="flex items-center gap-4 p-4 rounded-2xl transition-all {{ request()->routeIs('vendor.dashboard') ? 'bg-theme-primary text-white shadow-xl' : 'text-slate-500 hover:bg-theme-accent' }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span class="font-black italic uppercase tracking-widest text-[11px]">Control Hub</span>
                </a>
                
                <a href="{{ route('vendor.employees.index') }}" 
                   class="flex items-center gap-4 p-4 rounded-2xl transition-all {{ request()->routeIs('vendor.employees.*') ? 'bg-theme-primary text-white shadow-xl' : 'text-slate-500 hover:bg-theme-accent' }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span class="font-black italic uppercase tracking-widest text-[11px]">Specialists</span>
                </a>
                
                <a href="{{ route('vendor.bookings.index') }}" 
                   class="flex items-center gap-4 p-4 rounded-2xl transition-all {{ request()->routeIs('vendor.bookings.index') ? 'bg-theme-primary text-white shadow-xl' : 'text-slate-500 hover:bg-theme-accent' }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="font-black italic uppercase tracking-widest text-[11px]">Registry</span>
                </a>
                
                <a href="{{ route('vendor.profile.edit') }}" 
                   class="flex items-center gap-4 p-4 rounded-2xl transition-all {{ request()->routeIs('vendor.profile.*') ? 'bg-theme-primary text-white shadow-xl' : 'text-slate-500 hover:bg-theme-accent' }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="font-black italic uppercase tracking-widest text-[11px]">Protocols</span>
                </a>
            </div>

            <!-- Shop Status Toggle -->
            <div class="theme-nav p-8 shadow-2xl shadow-black/5 border border-white/20 backdrop-blur-3xl rounded-[2.5rem]">
                <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 italic">Registry Status</h4>
                <div class="flex items-center justify-between bg-white/50 p-4 rounded-2xl border border-white/30">
                    <span id="shop-status-text" class="text-xs font-black {{ $vendor->is_open ? 'text-theme-primary' : 'text-slate-400' }} italic">
                        {{ $vendor->is_open ? 'OPERATIONAL' : 'DEACTIVATED' }}
                    </span>
                    <form action="{{ route('vendor.status.toggle') }}" method="POST" id="status-toggle-form">
                        @csrf
                        <button type="submit" class="w-12 h-6 {{ $vendor->is_open ? 'bg-theme-primary shadow-lg' : 'bg-slate-200' }} rounded-full relative transition-all duration-500">
                            <div class="w-4 h-4 bg-white rounded-full absolute top-1 transition-all duration-500 shadow-sm"
                                 style="{{ $vendor->is_open ? 'left: 1.75rem;' : 'left: 0.25rem;' }}"></div>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Side QR Code -->
            <div class="theme-nav p-8 shadow-2xl shadow-black/5 border border-white/20 backdrop-blur-3xl rounded-[2.5rem]">
                <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6 italic">Access Matrix</h4>
                <div class="flex flex-col items-center gap-6">
                    <div class="w-full aspect-square bg-white/50 p-6 rounded-3xl border border-white/30 shadow-inner">
                        <img src="{{ asset('storage/' . $vendor->qr_code_path) }}" class="w-full h-full opacity-60 mix-blend-multiply grayscale hover:grayscale-0 transition-all duration-700">
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-3">
            @if(session('success'))
                <div class="bg-emerald-600 text-white p-6 rounded-[2rem] text-xs font-black uppercase tracking-widest italic mb-8 shadow-xl shadow-emerald-500/20">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-rose-600 text-white p-6 rounded-[2rem] text-xs font-black uppercase tracking-widest italic mb-8 shadow-xl shadow-rose-500/20">
                    {{ session('error') }}
                </div>
            @endif
            
            {{ $slot }}
        </div>
    </div>
</x-app-layout>
