<x-admin-layout>
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('admin.vendors.index') }}" class="text-primary-400 hover:underline text-sm mb-2 inline-block">
                    ← Back to Vendors
                </a>
                <h2 class="text-3xl font-bold">{{ $vendor->business_name }}</h2>
            </div>
            <div class="flex gap-3">
                <form action="{{ route('admin.vendors.update', $vendor) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="{{ $vendor->status == 'active' ? 'inactive' : 'active' }}">
                    <button type="submit" class="btn-outline">
                        {{ $vendor->status == 'active' ? 'Suspend Vendor' : 'Activate Vendor' }}
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Vendor Information -->
            <div class="glass-card p-6">
                <h3 class="text-xl font-bold mb-4 border-b border-white/10 pb-2">Business Information</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Business Name</p>
                        <p class="font-bold">{{ $vendor->business_name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Owner Name</p>
                        <p>{{ $vendor->owner_name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Contact Number</p>
                        <p>{{ $vendor->contact_number }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Email</p>
                        <p>{{ $vendor->user->email }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Status</p>
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $vendor->status == 'active' ? 'bg-green-500/10 text-green-400' : 'bg-yellow-500/10 text-yellow-400' }}">
                            {{ ucfirst($vendor->status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Shop Status</p>
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $vendor->is_open ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' }}">
                            {{ $vendor->is_open ? 'Open' : 'Closed' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Address & Location -->
            <div class="glass-card p-6">
                <h3 class="text-xl font-bold mb-4 border-b border-white/10 pb-2">Location Details</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Address</p>
                        <p>{{ $vendor->address }}</p>
                    </div>
                    @if($vendor->latitude && $vendor->longitude)
                        <div>
                            <p class="text-gray-500 text-xs uppercase">Coordinates</p>
                            <p class="text-sm">{{ $vendor->latitude }}, {{ $vendor->longitude }}</p>
                        </div>
                    @endif
                    @if($vendor->shop_photo)
                        <div>
                            <p class="text-gray-500 text-xs uppercase mb-2">Shop Photo</p>
                            <img src="{{ asset('storage/' . $vendor->shop_photo) }}" class="w-full rounded-lg">
                        </div>
                    @endif
                </div>
            </div>

            <!-- Subscription & Payment -->
            <div class="glass-card p-6">
                <h3 class="text-xl font-bold mb-4 border-b border-white/10 pb-2">Subscription & Payment</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Current Plan</p>
                        <p class="font-bold text-primary-400">{{ $vendor->subscriptionPlan->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Subscription Expires</p>
                        <p>{{ optional($vendor->subscription_expires_at)->format('M d, Y') ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Token Booking</p>
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $vendor->token_booking_enabled ? 'bg-green-500/10 text-green-400' : 'bg-gray-500/10 text-gray-400' }}">
                            {{ $vendor->token_booking_enabled ? 'Enabled - ₹' . $vendor->token_amount : 'Disabled' }}
                        </span>
                    </div>
                    <div class="pt-2 border-t border-white/10">
                        <p class="text-gray-500 text-xs uppercase">UPI ID for Settlements</p>
                        @if($vendor->upi_id)
                            <p class="font-bold">{{ $vendor->upi_id }}</p>
                        @else
                            <p class="text-red-400 text-sm">⚠️ Not set</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Referral Balance</p>
                        <p class="font-bold {{ $vendor->referral_balance > 0 ? 'text-green-400' : '' }}">
                            ₹{{ number_format($vendor->referral_balance) }}
                        </p>
                    </div>
                    @if($vendor->referral_code)
                        <div>
                            <p class="text-gray-500 text-xs uppercase">Referral Code</p>
                            <p class="font-mono text-sm">{{ $vendor->referral_code }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Employees -->
        @if($vendor->employees->count() > 0)
            <div class="glass-card overflow-hidden">
                <div class="p-6 border-b border-white/5">
                    <h3 class="text-xl font-bold">Employees ({{ $vendor->employees->count() }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-white/5 bg-white/5">
                                <th class="p-4">Name</th>
                                <th class="p-4">Expertise</th>
                                <th class="p-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendor->employees as $employee)
                                <tr class="border-b border-white/5 hover:bg-white/5">
                                    <td class="p-4">{{ $employee->name }}</td>
                                    <td class="p-4">{{ $employee->expertise }}</td>
                                    <td class="p-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $employee->is_active ? 'bg-green-500/10 text-green-400' : 'bg-gray-500/10 text-gray-400' }}">
                                            {{ $employee->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Recent Settlements -->
        @if($vendor->settlements->count() > 0)
            <div class="glass-card overflow-hidden">
                <div class="p-6 border-b border-white/5 flex justify-between items-center">
                    <h3 class="text-xl font-bold">Recent Settlements</h3>
                    <a href="{{ route('admin.settlements.index') }}" class="text-primary-400 hover:underline text-sm">View All Settlements →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-white/5 bg-white/5">
                                <th class="p-4">Period</th>
                                <th class="p-4">Bookings</th>
                                <th class="p-4">Referral</th>
                                <th class="p-4">Total</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendor->settlements->take(5) as $settlement)
                                <tr class="border-b border-white/5 hover:bg-white/5">
                                    <td class="p-4">
                                        {{ optional($settlement->period_start)->format('M d') }} - {{ optional($settlement->period_end)->format('M d, Y') }}
                                    </td>
                                    <td class="p-4">{{ $settlement->booking_count }}</td>
                                    <td class="p-4">{{ $settlement->referral_amount > 0 ? '₹' . number_format($settlement->referral_amount) : '-' }}</td>
                                    <td class="p-4 font-bold">₹{{ number_format($settlement->total_amount) }}</td>
                                    <td class="p-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $settlement->status == 'completed' ? 'bg-green-500/10 text-green-400' : 'bg-yellow-500/10 text-yellow-400' }}">
                                            {{ ucfirst($settlement->status) }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <a href="{{ route('admin.settlements.show', $settlement->id) }}" class="text-primary-400 hover:underline text-xs">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-admin-layout>
