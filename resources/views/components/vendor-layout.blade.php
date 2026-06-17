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
            <a href="{{ route('vendor.profile.edit') }}"
                class="flex items-center gap-4 px-6 py-4 rounded-2xl transition-all {{ request()->routeIs('vendor.profile.*') ? 'bg-white/5 text-white shadow-sm' : 'text-slate-300 hover:bg-white/5/50' }}">
                <svg class="h-5 w-5 {{ request()->routeIs('vendor.profile.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="font-black italic uppercase tracking-widest text-[11px] whitespace-nowrap">Settings</span>
            </a>
            <div class="h-px bg-white/10 mx-6 my-2"></div>
            @php $vendor = auth()->user()->vendor; @endphp
            @if($vendor)
            <div class="px-6 py-4 flex flex-col gap-4">
                <!-- Shop Status Toggle -->
                <div class="p-4 rounded-2xl bg-white/5 border border-white/10 italic">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[9px] font-black text-slate-400 uppercase">Registry Status</span>
                    </div>
                    <div class="flex items-center justify-between bg-white/5 p-3 rounded-xl border border-white/10">
                            <div class="w-2.5 h-2.5 rounded-full {{ $vendor->isEffectivelyOpen() ? 'bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)]' : 'bg-slate-500' }}"></div>
                            <span class="text-[9px] font-black {{ $vendor->isEffectivelyOpen() ? 'text-emerald-400' : 'text-slate-400' }} uppercase">{{ $vendor->isEffectivelyOpen() ? 'Live' : 'Off' }}</span>
                            <form action="{{ route('vendor.status.toggle') }}" method="POST" class="m-0 flex">
                            @csrf
                            <button type="submit" class="w-12 h-6 {{$vendor->isEffectivelyOpen()  ? 'bg-emerald-500' : 'bg-slate-600' }} rounded-full relative transition-all duration-300 border border-white/20">
                                <div class="w-4 h-4 bg-white rounded-full absolute top-1 shadow-md transition-all duration-300 {{ $vendor->isEffectivelyOpen() ? 'left-7' : 'left-1' }}"></div>
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
                    BOOK<span class="text-blue-600">APPOINTMENT</span>
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

                    <a href="{{ route('vendor.profile.edit') }}"
                        class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('vendor.profile.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/30' : 'text-slate-400 hover:bg-white/5 hover:translate-x-1' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span class="font-black italic uppercase tracking-widest text-[10px] whitespace-nowrap">Settings</span>
                    </a>
                </div>

                <div class="flex flex-col gap-6 mt-6">
                    <!-- Shop Status Toggle -->
                    <div class="p-5 rounded-3xl bg-white/5 border border-white/10 italic">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[9px] font-black text-slate-400 uppercase">Registry Status</span>
                        </div>
                        <div class="flex items-center justify-between bg-white/5 p-3 rounded-xl border border-white/10">
                             <div class="w-2.5 h-2.5 rounded-full {{ $vendor->isEffectivelyOpen() ? 'bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)]' : 'bg-slate-500' }}"></div>
                             <span class="text-[9px] font-black {{ $vendor->isEffectivelyOpen() ? 'text-emerald-400' : 'text-slate-400' }} uppercase">{{ $vendor->isEffectivelyOpen() ? 'Live' : 'Off' }}</span>
                             <form action="{{ route('vendor.status.toggle') }}" method="POST" class="m-0 flex">
                                @csrf
                                <button type="submit" class="w-12 h-6 {{ $vendor->isEffectivelyOpen() ? 'bg-emerald-500' : 'bg-slate-600' }} rounded-full relative transition-all duration-300 border border-white/20">
                                    <div class="w-4 h-4 bg-white rounded-full absolute top-1 shadow-md transition-all duration-300 {{ $vendor->isEffectivelyOpen() ? 'left-7' : 'left-1' }}"></div>
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