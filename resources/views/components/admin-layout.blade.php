<x-app-layout>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Admin Sidebar -->
        <div class="lg:col-span-1 space-y-4">
            <div class="glass-card p-6 flex flex-col gap-2 sticky top-24">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-4 {{ request()->routeIs('admin.dashboard') ? 'text-white border-primary-500/30' : 'text-gray-400 hover:bg-white/5' }} p-4 rounded-xl border border-transparent transition-all" style="{{ request()->routeIs('admin.dashboard') ? 'background: linear-gradient(135deg, #0ea5e9, #2563eb);' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span class="font-bold">Overview</span>
                </a>
                <a href="{{ route('admin.vendors.index') }}" class="flex items-center gap-4 {{ request()->routeIs('admin.vendors.*') ? 'text-white border-primary-500/30' : 'text-gray-400 hover:bg-white/5' }} p-4 rounded-xl border border-transparent transition-all" style="{{ request()->routeIs('admin.vendors.*') ? 'background: linear-gradient(135deg, #0ea5e9, #2563eb);' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="font-medium">Vendors</span>
                </a>
                <a href="{{ route('admin.plans.index') }}" class="flex items-center gap-4 {{ request()->routeIs('admin.plans.*') ? 'text-white border-primary-500/30' : 'text-gray-400 hover:bg-white/5' }} p-4 rounded-xl border border-transparent transition-all" style="{{ request()->routeIs('admin.plans.*') ? 'background: linear-gradient(135deg, #0ea5e9, #2563eb);' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                    <span class="font-medium">Plans</span>
                </a>
                <a href="{{ route('admin.settlements.index') }}" class="flex items-center gap-4 {{ request()->routeIs('admin.settlements.*') ? 'text-white border-primary-500/30' : 'text-gray-400 hover:bg-white/5' }} p-4 rounded-xl border border-transparent transition-all" style="{{ request()->routeIs('admin.settlements.*') ? 'background: linear-gradient(135deg, #0ea5e9, #2563eb);' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium">Settlements</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-3">
            {{ $slot }}
        </div>
    </div>
</x-app-layout>
