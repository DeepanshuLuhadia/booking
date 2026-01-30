<x-admin-layout>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('admin.settlements.index') }}" class="text-primary-400 hover:underline text-sm mb-2 inline-block">
                    ← Back to Settlements
                </a>
                <h2 class="text-3xl font-bold">Settlement Details</h2>
            </div>
            @if($settlement->status == 'pending')
                <button onclick="openMarkPaidModal({{ $settlement->id }})" class="btn-primary">
                    Mark as Paid
                </button>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Vendor Information -->
            <div class="glass-card p-6">
                <h3 class="text-xl font-bold mb-4 border-b border-white/10 pb-2">Vendor Information</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Business Name</p>
                        <p class="font-bold">{{ $settlement->vendor->business_name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Owner Name</p>
                        <p>{{ $settlement->vendor->owner_name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Contact Number</p>
                        <p>{{ $settlement->vendor->contact_number }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Email</p>
                        <p>{{ $settlement->vendor->user->email }}</p>
                    </div>
                    <div class="pt-2 border-t border-white/10">
                        <p class="text-gray-500 text-xs uppercase">UPI ID</p>
                        @if($settlement->vendor->upi_id)
                            <p class="font-bold text-primary-400 text-lg">{{ $settlement->vendor->upi_id }}</p>
                            <button onclick="copyToClipboard('{{ $settlement->vendor->upi_id }}')" 
                                    class="text-xs text-gray-400 hover:text-white mt-1">
                                📋 Copy UPI ID
                            </button>
                        @else
                            <p class="text-red-400 text-sm">⚠️ Not set</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Settlement Summary -->
            <div class="lg:col-span-2 glass-card p-6">
                <h3 class="text-xl font-bold mb-4 border-b border-white/10 pb-2">Settlement Summary</h3>
                
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <p class="text-gray-500 text-xs uppercase mb-1">Settlement Period</p>
                        <p class="font-bold">
                            {{ optional($settlement->period_start)->format('M d, Y') }} - 
                            {{ optional($settlement->period_end)->format('M d, Y') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase mb-1">Status</p>
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $settlement->status == 'completed' ? 'bg-green-500/10 text-green-400' : 'bg-yellow-500/10 text-yellow-400' }}">
                            {{ ucfirst($settlement->status) }}
                        </span>
                    </div>
                </div>

                <div class="space-y-4 bg-black/20 p-4 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Total Bookings</span>
                        <span class="font-bold">{{ $settlement->booking_count }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Booking Amount</span>
                        <span class="font-bold">₹{{ number_format($settlement->booking_amount) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Emergency Booking Fees</span>
                        <span class="font-bold">₹{{ number_format($settlement->emergency_booking_amount) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Referral Bonus</span>
                        <span class="font-bold {{ $settlement->referral_amount > 0 ? 'text-green-400' : '' }}">
                            ₹{{ number_format($settlement->referral_amount) }}
                        </span>
                    </div>
                    <div class="border-t border-white/10 pt-3 flex justify-between items-center">
                        <span class="text-lg font-bold">Total Payout</span>
                        <span class="text-2xl font-black text-primary-400">₹{{ number_format($settlement->total_amount) }}</span>
                    </div>
                </div>

                @if($settlement->status == 'completed')
                    <div class="mt-6 p-4 bg-green-500/10 border border-green-500/20 rounded-lg">
                        <p class="text-green-400 font-bold mb-2">✓ Payment Completed</p>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-400">Payout Date</p>
                                <p class="text-white">{{ optional($settlement->payout_date)->format('M d, Y h:i A') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-400">UPI Transaction ID</p>
                                <p class="text-white font-mono">{{ $settlement->upi_transaction_id }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Booking Details -->
        @if($settlement->vendor->bookings->count() > 0)
            <div class="glass-card overflow-hidden">
                <div class="p-6 border-b border-white/5">
                    <h3 class="text-xl font-bold">Included Bookings ({{ $settlement->vendor->bookings->count() }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-white/5 bg-white/5">
                                <th class="p-4">Date</th>
                                <th class="p-4">Customer</th>
                                <th class="p-4">Booking Amount</th>
                                <th class="p-4">Emergency Fee</th>
                                <th class="p-4">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($settlement->vendor->bookings as $booking)
                                <tr class="border-b border-white/5 hover:bg-white/5">
                                    <td class="p-4">{{ optional($booking->booking_date)->format('M d, Y') }}</td>
                                    <td class="p-4">
                                        <div>{{ $booking->customer_name }}</div>
                                        <div class="text-gray-500 text-xs">{{ $booking->customer_phone }}</div>
                                    </td>
                                    <td class="p-4">₹{{ number_format($booking->online_paid_amount) }}</td>
                                    <td class="p-4">
                                        @if($booking->emergency_fee > 0)
                                            <span class="text-yellow-400">₹{{ number_format($booking->emergency_fee) }}</span>
                                        @else
                                            <span class="text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="p-4 font-bold">₹{{ number_format($booking->online_paid_amount + $booking->emergency_fee) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Mark as Paid Modal -->
    <div id="markPaidModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
        <div class="glass-card p-8 max-w-md w-full mx-4">
            <h3 class="text-2xl font-bold mb-4">Mark Settlement as Paid</h3>
            <p class="text-gray-400 mb-6">Enter the UPI transaction ID to confirm the payout of ₹{{ number_format($settlement->total_amount) }}.</p>
            
            <form id="markPaidForm" method="POST" action="{{ route('admin.settlements.markAsPaid', $settlement->id) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-bold mb-2">UPI Transaction ID</label>
                    <input type="text" name="upi_transaction_id" required 
                           class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2 focus:outline-none focus:border-primary-400"
                           placeholder="e.g., 123456789012">
                </div>
                
                <div class="flex gap-4">
                    <button type="submit" class="btn-primary flex-1">
                        Confirm Payment
                    </button>
                    <button type="button" onclick="closeMarkPaidModal()" class="bg-white/5 hover:bg-white/10 px-6 py-2 rounded-lg font-bold transition-all">
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
