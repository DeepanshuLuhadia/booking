<x-admin-layout>
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <h2 class="text-3xl font-bold">Subscription Plans</h2>
            <a href="{{ route('admin.plans.create') }}" class="btn-primary">Add New Plan</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($plans as $plan)
                <div class="glass-card p-8 relative group overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-primary-500/10 blur-3xl rounded-full"></div>
                    
                    <h3 class="text-2xl font-bold mb-2">{{ $plan->name }}</h3>
                    <p class="text-5xl font-black mb-6">₹{{ number_format($plan->price) }}</p>
                    
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center gap-3 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Up to {{ $plan->max_employees }} employees
                        </li>
                        @foreach($plan->features as $feature)
                            <li class="flex items-center gap-3 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>

                    <div class="flex gap-2 relative z-10">
                        <a href="{{ route('admin.plans.edit', $plan) }}" class="btn-outline flex-grow py-3 text-center font-bold">Edit Plan</a>
                        <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-3 border border-red-500/20 text-red-400 rounded-xl hover:bg-red-500/10 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-admin-layout>
