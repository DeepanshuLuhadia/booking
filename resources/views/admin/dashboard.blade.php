<x-admin-layout>
    <div class="space-y-10">
        <div class="flex flex-col gap-2">
            <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white">Platform Overview</h2>
            <p class="text-xs md:text-sm font-medium text-slate-400 uppercase tracking-widest">Real-time business insights and health indicators</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="glass-card p-6 hover:scale-[1.02] hover:shadow-xl transition-all duration-300">
                <p class="text-slate-400 text-[10px] mb-2 uppercase font-black tracking-widest">Total Revenue</p>
                <h3 class="text-3xl font-black text-slate-950">₹{{ number_format($stats['total_revenue'] / 1000, 1) }}k+</h3>
            </div>
            <div class="glass-card p-6 hover:scale-[1.02] hover:shadow-xl transition-all duration-300">
                <p class="text-slate-400 text-[10px] mb-2 uppercase font-black tracking-widest">Active Vendors</p>
                <h3 class="text-3xl font-black text-slate-950">{{ number_format($stats['active_vendors'] / 1000, 1) }}k+</h3>
            </div>
            <div class="glass-card p-6 hover:scale-[1.02] hover:shadow-xl transition-all duration-300">
                <p class="text-slate-400 text-[10px] mb-2 uppercase font-black tracking-widest">Total Bookings</p>
                <h3 class="text-3xl font-black text-slate-950">{{ number_format($stats['total_bookings'] / 1000, 1) }}k+</h3>
            </div>
            <div class="glass-card p-6 hover:scale-[1.02] hover:shadow-xl transition-all duration-300">
                <p class="text-slate-400 text-[10px] mb-2 uppercase font-black tracking-widest">Active Users</p>
                <h3 class="text-3xl font-black text-slate-950">{{ number_format($stats['active_users'] / 1000, 1) }}k+</h3>
            </div>
        </div>

        <!-- Subscription Plans Management -->
        <div class="glass-card p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <div>
                    <h3 class="text-2xl font-black text-white">Subscription Plans</h3>
                    <p class="text-xs text-slate-400 uppercase font-black tracking-widest mt-1">Configure active pricing packages</p>
                </div>
                <a href="{{ route('admin.plans.create') }}" class="btn-primary inline-flex justify-center items-center px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-widest">Add New Plan</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach(\App\Models\SubscriptionPlan::all() as $plan)
                    <div class="glass-card p-6 border-white/10/50 bg-white/5/5 relative group hover:shadow-2xl transition-all duration-300">
                        <h4 class="text-xl font-black mb-2 text-white">{{ $plan->name }}</h4>
                        <p class="text-4xl font-black mb-4 text-white">₹{{ number_format($plan->price) }}</p>
                        <ul class="text-xs font-semibold text-slate-400 space-y-2.5 mb-6">
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                Up to {{ $plan->max_employees }} employees
                            </li>
                            @foreach($plan->features as $feature)
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.plans.edit', $plan) }}" class="btn-outline flex-grow py-3 text-center text-xs font-black uppercase tracking-widest rounded-xl">Edit Package</a>
                            <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-3 border border-red-500/20 text-red-500 hover:text-white rounded-xl hover:bg-red-500 transition-colors duration-200 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-admin-layout>
