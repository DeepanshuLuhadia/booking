<x-app-layout>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Vendor Sidebar -->
        <div class="lg:col-span-1 space-y-4">
            <div class="glass-card p-6 flex flex-col gap-2 sticky top-24">
                @php
                    $vendor = auth()->user()->vendor;
                @endphp
                
                <a href="{{ route('vendor.dashboard') }}" class="flex items-center gap-4 {{ request()->routeIs('vendor.dashboard') ? 'text-white border-primary-500/30' : 'text-gray-400 hover:bg-white/5' }} p-4 rounded-xl border border-transparent transition-all" style="{{ request()->routeIs('vendor.dashboard') ? 'background: linear-gradient(135deg, #0ea5e9, #2563eb);' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="font-bold">Dashboard</span>
                </a>
                
                <a href="{{ route('vendor.employees.index') }}" class="flex items-center gap-4 {{ request()->routeIs('vendor.employees.*') ? 'text-white border-primary-500/30' : 'text-gray-400 hover:bg-white/5' }} p-4 rounded-xl border border-transparent transition-all" style="{{ request()->routeIs('vendor.employees.*') ? 'background: linear-gradient(135deg, #0ea5e9, #2563eb);' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="font-medium">Employees</span>
                </a>
                
                <a href="{{ route('vendor.bookings.index') }}" class="flex items-center gap-4 {{ request()->routeIs('vendor.bookings.index') ? 'text-white border-primary-500/30' : 'text-gray-400 hover:bg-white/5' }} p-4 rounded-xl border border-transparent transition-all" style="{{ request()->routeIs('vendor.bookings.index') ? 'background: linear-gradient(135deg, #0ea5e9, #2563eb);' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="font-medium">Bookings</span>
                </a>
                
                <a href="{{ route('vendor.profile.edit') }}" class="flex items-center gap-4 {{ request()->routeIs('vendor.profile.*') ? 'text-white border-primary-500/30' : 'text-gray-400 hover:bg-white/5' }} p-4 rounded-xl border border-transparent transition-all" style="{{ request()->routeIs('vendor.profile.*') ? 'background: linear-gradient(135deg, #0ea5e9, #2563eb);' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="font-medium">Settings</span>
                </a>
            </div>

            <!-- Shop Status Toggle -->
            <div class="glass-card p-6">
                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4">Shop Reality</h4>
                <div class="flex items-center justify-between bg-black/20 p-4 rounded-2xl border border-white/5">
                    <span id="shop-status-text" class="text-sm font-black {{ $vendor->is_open ? 'text-green-400' : 'text-red-400' }} italic">
                        {{ $vendor->is_open ? 'LIVE' : 'OFFLINE' }}
                    </span>
                    <form action="{{ route('vendor.status.toggle') }}" method="POST" id="status-toggle-form">
                        @csrf
                        <button type="submit" class="w-12 h-6 {{ $vendor->is_open ? 'bg-green-500 shadow-[0_0_15px_rgba(34,197,94,0.3)]' : 'bg-gray-700' }} rounded-full relative transition-all duration-500">
                            <div class="w-4 h-4 bg-white rounded-full absolute top-1 transition-all duration-500 shadow-lg"
                                 style="{{ $vendor->is_open ? 'left: 1.75rem;' : 'left: 0.25rem;' }}"></div>
                        </button>
                    </form>
                </div>
                <p class="text-[9px] text-gray-500 mt-3 font-bold uppercase tracking-wider">
                    {{ $vendor->is_open ? 'Public marketplace active' : 'Bookings paused' }}
                </p>
            </div>

            <!-- Side QR Code -->
            <div class="glass-card p-6 bg-gradient-to-br from-primary-600/10 to-transparent border-primary-500/10">
                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4">Booking QR</h4>
                <div class="flex flex-col items-center gap-4">
                    <div class="w-full aspect-square bg-white p-3 rounded-2xl shadow-2xl">
                        <img src="{{ asset('storage/' . $vendor->qr_code_path) }}" class="w-full h-full">
                    </div>
                    <a href="{{ asset('storage/' . $vendor->qr_code_path) }}" download class="w-full btn-outline py-2.5 text-[9px] font-black uppercase tracking-widest flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download SVG
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-3">
            @if(session('success'))
                <div class="glass-card bg-green-500/10 border-green-500/20 p-4 mb-6 text-green-400">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="glass-card bg-red-500/10 border-red-500/20 p-4 mb-6 text-red-400">
                    {{ session('error') }}
                </div>
            @endif
            
            {{ $slot }}
        </div>
    </div>
</x-app-layout>
