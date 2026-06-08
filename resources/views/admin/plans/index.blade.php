<x-admin-layout>
    <div class="space-y-10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-col gap-2">
                <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white dark:text-white">Subscription Plans</h2>
                <p class="text-xs md:text-sm font-medium text-slate-400 uppercase tracking-widest">Configure subscription plan pricing and package features</p>
            </div>
            <a href="{{ route('admin.plans.create') }}" class="btn-primary inline-flex justify-center items-center px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-widest">Add New Plan</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($plans as $plan)
                <div class="glass-card p-6 sm:p-8 relative group overflow-hidden hover:scale-[1.02] hover:shadow-2xl transition-all duration-300">
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-primary-500/10 blur-3xl rounded-full"></div>
                    
                    <h3 class="text-2xl font-black mb-2 text-white">{{ $plan->name }}</h3>
                    <p class="text-5xl font-black mb-6 text-white">₹{{ number_format($plan->price) }}</p>
                    
                    <ul class="space-y-3.5 mb-8">
                        <li class="flex items-center gap-2.5 text-xs font-semibold text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                            Up to {{ $plan->max_employees }} employees
                        </li>
                        @foreach($plan->features as $feature)
                            <li class="flex items-center gap-2.5 text-xs font-semibold text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>

                    <div class="flex gap-2 relative z-10">
                        <a href="{{ route('admin.plans.edit', $plan) }}" class="btn-outline flex-grow py-3 text-center text-xs font-black uppercase tracking-widest rounded-xl">Edit Package</a>
                        <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-3 border border-red-500/20 text-red-500 hover:text-white rounded-xl hover:bg-red-500 transition-colors duration-200 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-admin-layout>
