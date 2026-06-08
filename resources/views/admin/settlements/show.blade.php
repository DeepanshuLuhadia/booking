<x-admin-layout>
    <div class="space-y-10">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="space-y-1">
                <a href="{{ route('admin.settlements.index') }}" class="text-xs font-black uppercase tracking-widest text-slate-400 hover:text-white transition-colors inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    Back to Settlements
                </a>
                <h2 class="text-3xl font-extrabold tracking-tight text-white">Settlement Details</h2>
            </div>
            @if($settlement->status == 'pending')
                <button onclick="openMarkPaidModal()" class="btn-primary px-6 py-3 text-xs font-black uppercase tracking-widest rounded-xl">
                    Mark as Paid
                </button>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Vendor Information -->
            <div class="glass-card p-6 space-y-6">
                <h3 class="text-lg font-black text-white border-b border-white/10 pb-3">Vendor Information</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Business Name</p>
                        <p class="font-extrabold text-white mt-1">{{ $settlement->vendor->business_name }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Owner Name</p>
                        <p class="font-semibold text-slate-100 mt-1">{{ $settlement->vendor->owner_name }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Contact Number</p>
                        <p class="font-semibold text-slate-100 mt-1">{{ $settlement->vendor->contact_number }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Email</p>
                        <p class="font-semibold text-slate-100 mt-1">{{ $settlement->vendor->user->email }}</p>
                    </div>
                    <div class="pt-4 border-t border-white/10 space-y-2">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">UPI ID</p>
                        @if($settlement->vendor->upi_id)
                            <p class="font-black text-white text-lg tracking-tight">{{ $settlement->vendor->upi_id }}</p>
                            <button onclick="copyToClipboard('{{ $settlement->vendor->upi_id }}')" 
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-white transition-colors inline-flex items-center gap-1.5 mt-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m-6 8l1 1 3-3"/></svg>
                                Copy UPI ID
                            </button>
                        @else
                            <p class="text-rose-500 font-bold text-xs">⚠️ Not configured</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Settlement Summary -->
            <div class="lg:col-span-2 glass-card p-6 space-y-6">
                <h3 class="text-lg font-black text-white border-b border-white/10 pb-3">Settlement Summary</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Settlement Period</p>
                        <p class="font-extrabold text-white mt-1">
                            {{ optional($settlement->period_start)->format('M d, Y') }} - 
                            {{ optional($settlement->period_end)->format('M d, Y') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Status</p>
                        <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $settlement->status == 'completed' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                            {{ $settlement->status }}
                        </span>
                    </div>
                </div>

                <div class="space-y-4 bg-white/5 p-6 rounded-2xl border border-white/10">
                    <div class="flex justify-between items-center text-xs font-semibold">
                        <span class="text-slate-400 uppercase tracking-wider">Total Bookings</span>
                        <span class="font-black text-slate-950">{{ $settlement->booking_count }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-semibold">
                        <span class="text-slate-400 uppercase tracking-wider">Booking Amount</span>
                        <span class="font-black text-slate-950">₹{{ number_format($settlement->booking_amount) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-semibold">
                        <span class="text-slate-400 uppercase tracking-wider">Emergency Booking Fees</span>
                        <span class="font-black text-slate-950">₹{{ number_format($settlement->emergency_booking_amount) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-semibold">
                        <span class="text-slate-400 uppercase tracking-wider">Referral Bonus</span>
                        <span class="font-black text-slate-950 {{ $settlement->referral_amount > 0 ? 'text-emerald-600' : '' }}">
                            ₹{{ number_format($settlement->referral_amount) }}
                        </span>
                    </div>
                    <div class="border-t border-white/10 pt-4 flex justify-between items-center">
                        <span class="text-sm font-black uppercase tracking-wider text-white">Total Payout</span>
                        <span class="text-3xl font-black text-white">₹{{ number_format($settlement->total_amount) }}</span>
                    </div>
                </div>

                @if($settlement->status == 'completed')
                    <div class="p-6 bg-emerald-50/50 border border-emerald-100 rounded-2xl space-y-4">
                        <p class="text-emerald-700 text-sm font-black uppercase tracking-widest flex items-center gap-1.5">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Payment Completed
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-semibold">
                            <div>
                                <p class="text-slate-400 uppercase tracking-wider mb-1">Payout Date</p>
                                <p class="text-slate-100 font-extrabold">{{ optional($settlement->payout_date)->format('M d, Y h:i A') }}</p>
                            </div>
                            <div>
                                <p class="text-slate-400 uppercase tracking-wider mb-1">UPI Transaction ID</p>
                                <p class="text-slate-100 font-mono font-extrabold">{{ $settlement->upi_transaction_id }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Booking Details -->
        @if($settlement->vendor->bookings->count() > 0)
            <div class="glass-card overflow-hidden">
                <div class="p-6 border-b border-white/10">
                    <h3 class="text-lg font-black text-slate-950">Included Bookings ({{ $settlement->vendor->bookings->count() }})</h3>
                </div>
                <div class="table-responsive-wrapper">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-white/10 bg-white/5/50">
                                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Date</th>
                                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Customer</th>
                                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Booking Amount</th>
                                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 hidden sm:table-cell">Emergency Fee</th>
                                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($settlement->vendor->bookings as $booking)
                                <tr class="border-b border-white/10 hover:bg-white/5/30 transition-all">
                                    <td class="p-4 text-xs font-semibold text-slate-200">{{ optional($booking->booking_date)->format('M d, Y') }}</td>
                                    <td class="p-4 text-xs font-semibold text-slate-200">
                                        <div class="font-black text-white">{{ $booking->customer_name }}</div>
                                        <div class="text-[10px] font-bold text-slate-400 mt-0.5">{{ $booking->customer_phone }}</div>
                                    </td>
                                    <td class="p-4 text-xs font-semibold text-slate-100">₹{{ number_format($booking->online_paid_amount) }}</td>
                                    <td class="p-4 text-xs font-semibold text-slate-300 hidden sm:table-cell">
                                        @if($booking->emergency_fee > 0)
                                            <span class="text-amber-600 font-bold">₹{{ number_format($booking->emergency_fee) }}</span>
                                        @else
                                            <span class="text-slate-400 font-bold">-</span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-sm font-black text-white">₹{{ number_format($booking->online_paid_amount + $booking->emergency_fee) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Mark as Paid Modal -->
    <div id="markPaidModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-md flex items-center justify-center z-[9999] transition-all duration-300">
        <div class="glass-card p-8 max-w-md w-full mx-4 shadow-2xl relative">
            <h3 class="text-2xl font-black text-white mb-2">Mark as Paid</h3>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-6">Confirm and enter UPI transaction ID for ₹{{ number_format($settlement->total_amount) }}</p>
            
            <form id="markPaidForm" method="POST" action="{{ route('admin.settlements.markAsPaid', $settlement->id) }}" class="space-y-6 m-0">
                @csrf
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">UPI Transaction ID</label>
                    <input type="text" name="upi_transaction_id" required placeholder="e.g., 123456789012"
                           class="w-full glass-input min-h-[2.75rem] rounded-xl px-4 py-2.5 text-sm font-semibold focus:outline-none">
                </div>
                
                <div class="flex gap-4">
                    <button type="submit" class="btn-primary flex-grow py-3 text-xs font-black uppercase tracking-widest rounded-xl">
                        Confirm Payout
                    </button>
                    <button type="button" onclick="closeMarkPaidModal()" class="btn-outline px-6 py-3 text-xs font-black uppercase tracking-widest rounded-xl">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openMarkPaidModal() {
            document.getElementById('markPaidModal').classList.remove('hidden');
        }

        // Keep close modal function
        function closeMarkPaidModal() {
            document.getElementById('markPaidModal').classList.add('hidden');
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('UPI ID copied to clipboard!');
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMarkPaidModal();
            }
        });
    </script>
</x-admin-layout>
