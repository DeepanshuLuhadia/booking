<x-admin-layout>
    <div class="space-y-10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="space-y-1">
                <a href="{{ route('admin.vendors.index') }}" class="text-xs font-black uppercase tracking-widest text-slate-400 hover:text-white transition-colors inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    Back to Vendors
                </a>
                <h2 class="text-3xl font-extrabold tracking-tight text-white">{{ $vendor->business_name }}</h2>
            </div>
            <div class="flex items-center gap-3">
                <form action="{{ route('admin.vendors.update', $vendor) }}" method="POST" class="m-0">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="{{ $vendor->status == 'active' ? 'inactive' : 'active' }}">
                    <button type="submit" class="btn-outline px-6 py-3 text-xs font-black uppercase tracking-widest rounded-xl">
                        {{ $vendor->status == 'active' ? 'Suspend Account' : 'Activate Account' }}
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Vendor Information -->
            <div class="glass-card p-6 space-y-6">
                <h3 class="text-lg font-black text-white border-b border-white/10 pb-3">Business Information</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Business Name</p>
                        <p class="font-extrabold text-white mt-1">{{ $vendor->business_name }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Owner Name</p>
                        <p class="font-semibold text-slate-100 mt-1">{{ $vendor->owner_name }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Contact Number</p>
                        <p class="font-semibold text-slate-100 mt-1">{{ $vendor->contact_number }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Email</p>
                        <p class="font-semibold text-slate-100 mt-1">{{ $vendor->user->email }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Status</p>
                        <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $vendor->status == 'active' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                            {{ $vendor->status }}
                        </span>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Shop Status</p>
                        <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $vendor->is_open ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                            {{ $vendor->is_open ? 'Open' : 'Closed' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Address & Location -->
            <div class="glass-card p-6 space-y-6">
                <h3 class="text-lg font-black text-white border-b border-white/10 pb-3">Location Details</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Address</p>
                        <p class="font-semibold text-slate-100 mt-1 leading-relaxed">{{ $vendor->address }}</p>
                    </div>
                    @if($vendor->latitude && $vendor->longitude)
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Coordinates</p>
                            <p class="text-xs font-mono text-slate-200 mt-1">{{ $vendor->latitude }}, {{ $vendor->longitude }}</p>
                        </div>
                    @endif
                    @if($vendor->shop_photo)
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Shop Photo</p>
                            <img src="{{ asset('storage/' . $vendor->shop_photo) }}" class="w-full rounded-xl border border-white/10">
                        </div>
                    @endif
                </div>
            </div>

            <!-- Subscription & Payment -->
            <div class="glass-card p-6 space-y-6">
                <h3 class="text-lg font-black text-white border-b border-white/10 pb-3">Subscription & Payment</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Current Plan</p>
                        <p class="font-extrabold text-white mt-1">{{ $vendor->subscriptionPlan->name }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Subscription Expires</p>
                        <p class="font-semibold text-slate-100 mt-1">{{ optional($vendor->subscription_expires_at)->format('M d, Y') ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Token Booking</p>
                        <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $vendor->token_booking_enabled ? 'bg-emerald-50 text-emerald-600' : 'bg-white/10 text-slate-400' }}">
                            {{ $vendor->token_booking_enabled ? 'Enabled - ₹' . $vendor->token_amount : 'Disabled' }}
                        </span>
                    </div>
                    <div class="pt-2 border-t border-white/10">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">UPI ID for Settlements</p>
                        @if($vendor->upi_id)
                            <p class="font-extrabold text-white mt-1">{{ $vendor->upi_id }}</p>
                        @else
                            <p class="text-rose-500 font-bold text-xs mt-1">⚠️ Not configured</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Referral Balance</p>
                        <p class="font-black text-white mt-1 {{ $vendor->referral_balance > 0 ? 'text-emerald-600' : '' }}">
                            ₹{{ number_format($vendor->referral_balance) }}
                        </p>
                    </div>
                    @if($vendor->referral_code)
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Referral Code</p>
                            <p class="font-mono text-xs text-slate-300 mt-1">{{ $vendor->referral_code }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Employees -->
        @if($vendor->employees->count() > 0)
            <div class="glass-card overflow-hidden">
                <div class="p-6 border-b border-white/10">
                    <h3 class="text-lg font-black text-slate-950">Employees ({{ $vendor->employees->count() }})</h3>
                </div>
                <div class="table-responsive-wrapper">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-white/10 bg-white/5/50">
                                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Name</th>
                                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Expertise</th>
                                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendor->employees as $employee)
                                <tr class="border-b border-white/10 hover:bg-white/5/30 transition-all">
                                    <td class="p-4 font-black text-white text-sm">{{ $employee->name }}</td>
                                    <td class="p-4 text-xs font-semibold text-slate-300">{{ $employee->expertise }}</td>
                                    <td class="p-4">
                                        <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $employee->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-white/10 text-slate-400' }}">
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
                <div class="p-6 border-b border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <h3 class="text-lg font-black text-slate-950">Recent Settlements</h3>
                    <a href="{{ route('admin.settlements.index') }}" class="text-xs font-black uppercase tracking-widest text-slate-400 hover:text-white transition-colors">View All Settlements →</a>
                </div>
                <div class="table-responsive-wrapper">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-white/10 bg-white/5/50">
                                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Period</th>
                                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Bookings</th>
                                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 hidden sm:table-cell">Referral</th>
                                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Total</th>
                                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendor->settlements->take(5) as $settlement)
                                <tr class="border-b border-white/10 hover:bg-white/5/30 transition-all">
                                    <td class="p-4 text-xs font-semibold text-slate-200">
                                        {{ optional($settlement->period_start)->format('M d') }} - {{ optional($settlement->period_end)->format('M d, Y') }}
                                    </td>
                                    <td class="p-4 text-xs font-semibold text-slate-300">{{ $settlement->booking_count }}</td>
                                    <td class="p-4 text-xs font-semibold text-slate-300 hidden sm:table-cell">{{ $settlement->referral_amount > 0 ? '₹' . number_format($settlement->referral_amount) : '-' }}</td>
                                    <td class="p-4 text-xs font-black text-white">₹{{ number_format($settlement->total_amount) }}</td>
                                    <td class="p-4">
                                        <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $settlement->status == 'completed' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                                            {{ $settlement->status }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <a href="{{ route('admin.settlements.show', $settlement->id) }}" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-white transition-colors">
                                            Details
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
