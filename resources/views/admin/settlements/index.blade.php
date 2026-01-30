<x-admin-layout>
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <h2 class="text-3xl font-bold">Settlements</h2>
            <p class="text-gray-400">Manage vendor payouts and financial records</p>
        </div>

        @if(session('success'))
            <div class="glass-card p-4 bg-green-500/10 border border-green-500/20 text-green-400">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="glass-card p-6">
                <p class="text-gray-500 text-xs mb-1 uppercase font-bold tracking-wider">Pending Settlements</p>
                <h3 class="text-2xl font-black">₹{{ number_format($settlements->where('status', 'pending')->sum('total_amount')) }}</h3>
            </div>
            <div class="glass-card p-6">
                <p class="text-gray-500 text-xs mb-1 uppercase font-bold tracking-wider">Completed Settlements</p>
                <h3 class="text-2xl font-black">₹{{ number_format($settlements->where('status', 'completed')->sum('total_amount')) }}</h3>
            </div>
            <div class="glass-card p-6">
                <p class="text-gray-500 text-xs mb-1 uppercase font-bold tracking-wider">Total Referral Payouts</p>
                <h3 class="text-2xl font-black text-primary-400">₹{{ number_format($settlements->sum('referral_amount')) }}</h3>
            </div>
        </div>

        <div class="glass-card overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-white/5 bg-white/5">
                        <th class="p-4 font-bold">Vendor</th>
                        <th class="p-4 font-bold">Period</th>
                        <th class="p-4 font-bold">Bookings</th>
                        <th class="p-4 font-bold">Referral</th>
                        <th class="p-4 font-bold">Total Amount</th>
                        <th class="p-4 font-bold">Status</th>
                        <th class="p-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($settlements as $settlement)
                        <tr class="border-b border-white/5 hover:bg-white/5 transition-all">
                            <td class="p-4">
                                <a href="{{ route('admin.settlements.show', $settlement->id) }}" class="font-bold text-primary-400 hover:underline">
                                    {{ $settlement->vendor->business_name }}
                                </a>
                            </td>
                            <td class="p-4 text-gray-400 text-sm">
                                {{ optional($settlement->period_start)->format('M d') }} - {{ optional($settlement->period_end)->format('M d, Y') }}
                            </td>
                            <td class="p-4 text-sm">
                                <div>{{ $settlement->booking_count }} bookings</div>
                                <div class="text-gray-500">₹{{ number_format($settlement->booking_amount + $settlement->emergency_booking_amount) }}</div>
                            </td>
                            <td class="p-4 font-semibold">
                                @if($settlement->referral_amount > 0)
                                    <span class="text-green-400">₹{{ number_format($settlement->referral_amount) }}</span>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="p-4 font-black">₹{{ number_format($settlement->total_amount) }}</td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $settlement->status == 'completed' ? 'bg-green-500/10 text-green-400' : 'bg-yellow-500/10 text-yellow-400' }}">
                                    {{ ucfirst($settlement->status) }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                @if($settlement->status == 'pending')
                                    <button onclick="openMarkPaidModal({{ $settlement->id }})" class="btn-primary py-1 text-xs">
                                        Mark Paid
                                    </button>
                                @else
                                    <span class="text-gray-500 text-xs italic">Paid</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-20 text-center text-gray-500">No settlement records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mark as Paid Modal -->
    <div id="markPaidModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
        <div class="glass-card p-8 max-w-md w-full mx-4">
            <h3 class="text-2xl font-bold mb-4">Mark Settlement as Paid</h3>
            <p class="text-gray-400 mb-6">Enter the UPI transaction ID to confirm the payout.</p>
            
            <form id="markPaidForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-bold mb-2">UPI Transaction ID</label>
                    <input type="text" name="upi_transaction_id" required 
                           class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2 focus:outline-none focus:border-primary-400">
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
        function openMarkPaidModal(settlementId) {
            const modal = document.getElementById('markPaidModal');
            const form = document.getElementById('markPaidForm');
            form.action = `/admin/settlements/${settlementId}/mark-paid`;
            modal.classList.remove('hidden');
        }

        function closeMarkPaidModal() {
            document.getElementById('markPaidModal').classList.add('hidden');
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMarkPaidModal();
            }
        });
    </script>
</x-admin-layout>
