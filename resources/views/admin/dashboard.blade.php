<x-admin-layout>
    <div class="space-y-8">
        <h2 class="text-3xl font-bold">Platform Overview</h2>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="glass-card p-6">
                <p class="text-gray-500 text-xs mb-1 uppercase font-bold tracking-wider">Total Revenue</p>
                <h3 class="text-2xl font-black">₹{{ number_format($stats['total_revenue'] / 1000, 1) }}k+</h3>
            </div>
            <div class="glass-card p-6">
                <p class="text-gray-500 text-xs mb-1 uppercase font-bold tracking-wider">Active Vendors</p>
                <h3 class="text-2xl font-black">{{ number_format($stats['active_vendors'] / 1000, 1) }}k+</h3>
            </div>
            <div class="glass-card p-6">
                <p class="text-gray-500 text-xs mb-1 uppercase font-bold tracking-wider">Total Bookings</p>
                <h3 class="text-2xl font-black">{{ number_format($stats['total_bookings'] / 1000, 1) }}k+</h3>
            </div>
            <div class="glass-card p-6">
                <p class="text-gray-500 text-xs mb-1 uppercase font-bold tracking-wider">Active Users</p>
                <h3 class="text-2xl font-black">{{ number_format($stats['active_users'] / 1000, 1) }}k+</h3>
            </div>
        </div>

        <!-- Subscription Plans Management -->
        <div class="glass-card p-8">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-2xl font-bold">Subscription Plans</h3>
                <a href="{{ route('admin.plans.create') }}" class="btn-primary">Add New Plan</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach(\App\Models\SubscriptionPlan::all() as $plan)
                    <div class="glass-card p-6 border-white/5 bg-white/5 relative group">
                        <h4 class="text-xl font-bold mb-2">{{ $plan->name }}</h4>
                        <p class="text-4xl font-black mb-4">₹{{ number_format($plan->price) }}</p>
                        <ul class="text-sm text-gray-400 space-y-2 mb-6">
                            <li>Up to {{ $plan->max_employees }} employees</li>
                            @foreach($plan->features as $feature)
                                <li>• {{ $feature }}</li>
                            @endforeach
                        </ul>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.plans.edit', $plan) }}" class="btn-outline flex-grow py-2 text-center">Edit</a>
                            <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 border border-red-500/20 text-red-400 rounded-xl hover:bg-red-500/10">
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
