<x-admin-layout>
    <div class="space-y-10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-col gap-2">
                <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white dark:text-white">Settlements</h2>
                <p class="text-xs md:text-sm font-medium text-slate-400 uppercase tracking-widest">Manage vendor payouts and financial records</p>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-500 text-white p-6 rounded-[2rem] text-xs font-black uppercase tracking-widest italic shadow-xl shadow-emerald-500/10">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="glass-card p-6 hover:scale-[1.02] hover:shadow-xl transition-all duration-300">
                <p class="text-slate-400 text-[10px] mb-2 uppercase font-black tracking-widest">Pending Settlements</p>
                <h3 class="text-3xl font-black text-white">₹{{ number_format($settlements->where('status', 'pending')->sum('total_amount')) }}</h3>
            </div>
            <div class="glass-card p-6 hover:scale-[1.02] hover:shadow-xl transition-all duration-300">
                <p class="text-slate-400 text-[10px] mb-2 uppercase font-black tracking-widest">Completed Settlements</p>
                <h3 class="text-3xl font-black text-white">₹{{ number_format($settlements->where('status', 'completed')->sum('total_amount')) }}</h3>
            </div>
            <div class="glass-card p-6 hover:scale-[1.02] hover:shadow-xl transition-all duration-300">
                <p class="text-slate-400 text-[10px] mb-2 uppercase font-black tracking-widest">Total Referral Payouts</p>
                <h3 class="text-3xl font-black text-white">₹{{ number_format($settlements->sum('referral_amount')) }}</h3>
            </div>
        </div>

        <div class="glass-card overflow-hidden">
            <div class="table-responsive-wrapper">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/10 bg-white/5/50">
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Vendor</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 hidden md:table-cell">Period</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 hidden sm:table-cell">Bookings</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 hidden lg:table-cell">Referral</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Total Amount</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($settlements as $settlement)
                            <tr class="border-b border-white/10 hover:bg-white/5/30 transition-all">
                                <td class="p-4">
                                    <a href="{{ route('admin.settlements.show', $settlement->id) }}" class="font-black text-white text-sm hover:underline">
                                        {{ $settlement->vendor->business_name }}
                                    </a>
                                </td>
                                <td class="p-4 text-xs font-semibold text-slate-400 hidden md:table-cell">
                                    {{ optional($settlement->period_start)->format('M d') }} - {{ optional($settlement->period_end)->format('M d, Y') }}
                                </td>
                                <td class="p-4 text-xs font-semibold text-slate-300 hidden sm:table-cell">
                                    <div>{{ $settlement->booking_count }} bookings</div>
                                    <div class="text-slate-400 font-bold text-[10px] tracking-wider mt-0.5">₹{{ number_format($settlement->booking_amount + $settlement->emergency_booking_amount) }}</div>
                                </td>
                                <td class="p-4 font-semibold text-xs hidden lg:table-cell">
                                    @if($settlement->referral_amount > 0)
                                        <span class="text-emerald-600 font-black">₹{{ number_format($settlement->referral_amount) }}</span>
                                    @else
                                        <span class="text-slate-400 font-bold">-</span>
                                    @endif
                                </td>
                                <td class="p-4 text-sm font-black text-white">₹{{ number_format($settlement->total_amount) }}</td>
                                <td class="p-4">
                                    <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $settlement->status == 'completed' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                                        {{ $settlement->status }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    @if($settlement->status == 'pending')
                                        <button onclick="openMarkPaidModal({{ $settlement->id }})" class="btn-primary py-2 px-3 text-[10px] font-black uppercase tracking-widest rounded-lg">
                                            Mark Paid
                                        </button>
                                    @else
                                        <span class="text-slate-400 text-xs font-black uppercase tracking-widest italic">Paid</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-20 text-center text-slate-400 font-bold text-sm">No settlement records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Mark as Paid Modal -->
    <div id="markPaidModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-md flex items-center justify-center z-[9999] transition-all duration-300">
        <div class="glass-card p-8 max-w-md w-full mx-4 shadow-2xl relative">
            <h3 class="text-2xl font-black text-white mb-2">Mark as Paid</h3>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-6">Confirm and enter UPI transaction ID</p>
            
            <form id="markPaidForm" method="POST" class="space-y-6 m-0">
                @csrf
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">UPI Transaction ID</label>
                    <input type="text" name="upi_transaction_id" required placeholder="Enter transaction reference"
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
