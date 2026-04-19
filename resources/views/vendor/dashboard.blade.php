<x-vendor-layout>
    <div x-data="{ 
        showBookingModal: false,
        selectedEmployee: null,
        slots: [],
        loadingSlots: false,
        async loadSlots(id) {
            this.loadingSlots = true;
            this.selectedEmployee = id;
            try {
                const res = await fetch(`/api/vendors/{{ $vendor->id }}/employees/${id}/slots`);
                if (!res.ok) throw new Error('API Error');
                this.slots = await res.json();
            } catch (e) {
                console.error('Failed to load slots', e);
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'FAILED TO LOAD SLOTS', type: 'error' } }));
            }
            this.loadingSlots = false;
        }
    }">

        <!-- Header Operational Intel -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <div class="bg-white p-8 shadow-2xl shadow-slate-200/50 border border-slate-100 rounded-[2.5rem]">
                <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] mb-4 italic">Total Payload Today</p>
                <h2 class="text-5xl font-black text-slate-900 italic tracking-tighter">{{ $stats['today_bookings'] }}</h2>
            </div>
            <div class="bg-white p-8 shadow-2xl shadow-slate-200/50 border border-slate-100 rounded-[2.5rem]">
                <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] mb-4 italic">Active Matrix</p>
                <div class="flex items-end gap-3">
                    <h2 class="text-5xl font-black text-slate-900 italic tracking-tighter">{{ $stats['active_employees'] }}</h2>
                    <span class="text-xs font-black text-slate-300 mb-2 italic">/ {{ $stats['plan_limit'] }} ACTIVE SLOTS</span>
                </div>
            </div>
            <div class="bg-white p-8 shadow-2xl shadow-slate-200/50 border border-slate-100 rounded-[2.5rem]">
                <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] mb-4 italic">Revenue Sync</p>
                <h2 class="text-5xl font-black text-blue-600 italic tracking-tighter">₹{{ number_format($stats['today_revenue']) }}</h2>
            </div>
        </div>

        <div class="space-y-12">
            <!-- Full Width Recent Bookings -->
            <div class="bg-white shadow-2xl shadow-slate-200/50 border border-slate-100 rounded-[3rem] overflow-hidden">
                <div class="p-10 border-b border-slate-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-3xl font-black italic tracking-tight uppercase">Registry <span class="text-blue-600">Feed.</span></h3>
                        <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] mt-2 italic">REAL-TIME TRANSACTION STREAM</p>
                    </div>
                    <a href="{{ route('vendor.bookings.index') }}" class="px-8 py-3 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all italic">Full Logs</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="text-left bg-slate-50">
                            <tr>
                                <th class="px-10 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic">Principal</th>
                                <th class="px-10 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic">Specialist</th>
                                <th class="px-10 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic">Time Matrix</th>
                                <th class="px-10 py-6 text-[9px) font-black text-slate-400 uppercase tracking-[0.2em] italic">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($recentBookings as $booking)
                                <tr class="hover:bg-slate-50/50 transition-all">
                                    <td class="px-10 py-8">
                                        <div class="font-black text-slate-900 text-lg tracking-tight uppercase italic">{{ $booking->customer_name }}</div>
                                        <div class="text-[9px] text-slate-300 font-black mt-1 uppercase tracking-widest italic">{{ $booking->customer_phone }}</div>
                                    </td>
                                    <td class="px-10 py-8">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center text-xs font-black italic">
                                                {{ substr($booking->employee->name, 0, 1) }}
                                            </div>
                                            <span class="font-black text-slate-700 text-sm uppercase italic">{{ $booking->employee->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-10 py-8">
                                        <div class="text-sm font-black text-slate-900 italic tracking-tight">{{ $booking->booking_date->format('l, M d') }}</div>
                                        <div class="text-[9px] text-blue-600 font-black mt-1 uppercase tracking-widest italic">{{ $booking->slot_start_time }} - {{ $booking->slot_end_time }}</div>
                                    </td>
                                    <td class="px-10 py-8">
                                        @php
                                            $color = match($booking->status) {
                                                'confirmed' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                                'cancelled' => 'bg-rose-50 text-rose-600 border-rose-100',
                                                'completed' => 'bg-blue-50 text-blue-600 border-blue-100',
                                                default => 'bg-slate-50 text-slate-600 border-slate-100'
                                            };
                                        @endphp
                                        <span class="px-4 py-1.5 {{ $color }} rounded-lg text-[9px] font-black uppercase border tracking-widest italic">{{ $booking->status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-10 py-32 text-center">
                                        <div class="flex flex-col items-center opacity-10">
                                            <svg class="h-20 w-20 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <p class="text-2xl font-black uppercase tracking-[0.3em] italic">Registry Null</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bottom Section: Quick Action & Referral -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <div class="lg:col-span-2">
                    <!-- Walk-in Command -->
                    <div class="bg-white p-12 shadow-2xl shadow-slate-200/50 border border-slate-100 rounded-[3rem] flex flex-col md:flex-row items-center justify-between gap-12 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-64 h-64 bg-slate-50 rounded-full blur-3xl -mr-32 -mt-32"></div>
                        <div class="relative z-10">
                            <h3 class="text-4xl font-black italic mb-3 uppercase tracking-tight">Walk-in <span class="text-blue-600">Manual.</span></h3>
                            <p class="text-slate-400 text-lg italic leading-relaxed">Direct synchronization of unplanned arrivals.</p>
                        </div>
                        <button @click="showBookingModal = true" class="relative z-10 w-full md:w-auto px-12 py-6 bg-slate-900 text-white rounded-[2rem] text-xl font-black italic uppercase tracking-widest hover:bg-black transition-all flex items-center justify-center gap-6 group">
                            SECURE SLOT
                            <svg class="w-8 h-8 transition-transform group-hover:translate-x-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Referral Intelligence -->
                        <div class="bg-white p-10 shadow-2xl shadow-slate-200/50 border border-slate-100 rounded-[3rem]">
                            <div class="flex items-center gap-4 mb-8">
                                <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <h4 class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] italic">Growth Incentive</h4>
                            </div>
                            <p class="text-sm text-slate-500 mb-8 font-medium italic">Bounty: <span class="text-slate-900 font-black italic">₹150</span> per verified referral.</p>
                            
                            <div class="flex flex-col gap-4">
                                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between group">
                                    <span class="text-base font-black text-slate-900 tracking-widest">{{ $vendor->referral_code }}</span>
                                    <button @click="navigator.clipboard.writeText('{{ $vendor->referral_code }}'); window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'REFERRAL CODE COPIED', type: 'success' } }))" class="text-slate-300 hover:text-blue-600 transition-colors">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </button>
                                </div>
                                
                                <div class="px-8 py-4 bg-blue-600 rounded-2xl text-white shadow-xl shadow-blue-500/20">
                                    <p class="text-[8px] uppercase font-black opacity-60 mb-1 italic">Settlement Balance</p>
                                    <p class="text-2xl font-black italic tracking-tighter">₹{{ number_format($vendor->referral_balance) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manual Booking Modal -->
        <div x-show="showBookingModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6" x-cloak>
            <div @click="showBookingModal = false" class="absolute inset-0 bg-white/80 backdrop-blur-xl"></div>
            
            <div class="relative bg-white w-full max-w-5xl max-h-[90vh] overflow-hidden flex flex-col border border-slate-100 shadow-[0_100px_200px_-50px_rgba(0,0,0,0.15)] rounded-[4rem]">
                <div class="p-10 border-b border-slate-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-3xl font-black text-slate-900 italic tracking-tight uppercase">Registry <span class="text-blue-600">Insertion.</span></h3>
                        <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] mt-2 italic">MANUAL ALLOCATION PROTOCOL</p>
                    </div>
                    <button @click="showBookingModal = false" class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 hover:text-rose-500 hover:bg-rose-50 transition-all">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <form action="{{ route('vendor.bookings.store') }}" method="POST" class="flex-grow overflow-y-auto p-12">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
                        <!-- Customer & Specialist Selection -->
                        <div class="space-y-12">
                            <section>
                                <h4 class="text-[9px] font-black text-blue-600 uppercase tracking-[0.3em] mb-8 italic">01. Principal Intelligence</h4>
                                <div class="space-y-6">
                                    <div class="group">
                                        <label class="block text-[8px] font-black text-slate-300 uppercase mb-2 ml-4 italic tracking-widest">Full Name</label>
                                        <input type="text" name="customer_name" required class="w-full h-16 bg-slate-50 border-none rounded-2xl px-6 font-black italic text-slate-900 placeholder:text-slate-100 focus:ring-4 focus:ring-blue-50 transition-all">
                                    </div>
                                    <div class="group">
                                        <label class="block text-[8px] font-black text-slate-300 uppercase mb-2 ml-4 italic tracking-widest">Communication Uplink</label>
                                        <input type="text" name="customer_phone" class="w-full h-16 bg-slate-50 border-none rounded-2xl px-6 font-black italic text-slate-900 placeholder:text-slate-100 focus:ring-4 focus:ring-blue-50 transition-all" placeholder="+91">
                                    </div>
                                </div>
                            </section>

                            <section>
                                <h4 class="text-[9px] font-black text-blue-600 uppercase tracking-[0.3em] mb-8 italic">02. Matrix Specialist</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    @foreach($vendor->employees as $employee)
                                        <label class="cursor-pointer group">
                                            <input type="radio" name="employee_id" value="{{ $employee->id }}" @click="loadSlots({{ $employee->id }})" class="sr-only peer" required>
                                            <div class="p-6 bg-white border-2 border-slate-100 rounded-[2.5rem] peer-checked:border-blue-600 peer-checked:bg-blue-50/50 transition-all flex items-center gap-4">
                                                <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-black italic text-sm">
                                                    {{ substr($employee->name, 0, 1) }}
                                                </div>
                                                <div class="text-[11px] font-black text-slate-900 uppercase italic tracking-tight truncate">{{ $employee->name }}</div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </section>
                        </div>

                        <!-- Time Matrix -->
                        <div class="bg-slate-50 rounded-[3.5rem] p-10 border border-slate-100">
                            <h4 class="text-[9px] font-black text-blue-600 uppercase tracking-[0.3em] mb-8 italic">03. Temporal Selection</h4>
                            
                            <div x-show="!selectedEmployee" class="h-80 flex flex-col items-center justify-center text-center opacity-20">
                                <svg class="h-16 w-16 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-sm font-black uppercase tracking-widest italic">Awaiting Specialist Focus</p>
                            </div>

                            <div x-show="loadingSlots" class="h-80 flex items-center justify-center text-blue-600">
                                <svg class="animate-spin h-10 w-10" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>

                            <div x-show="selectedEmployee && !loadingSlots">
                                <div class="grid grid-cols-3 gap-4">
                                    <template x-for="slot in slots" :key="slot.start">
                                        <label class="cursor-pointer">
                                            <input type="radio" name="slot_info" :value="slot.start + '|' + slot.end" 
                                                   @change="$refs.slot_start.value = slot.start; $refs.slot_end.value = slot.end"
                                                   class="sr-only peer" :disabled="slot.is_booked">
                                            <div class="p-4 bg-white border-2 border-white rounded-2xl peer-checked:border-blue-600 peer-checked:bg-blue-600 peer-checked:text-white text-center transition-all shadow-sm"
                                                :class="slot.is_booked ? 'opacity-10 cursor-not-allowed' : 'hover:border-slate-200'">
                                                <span class="text-xs font-black italic uppercase" x-text="slot.start"></span>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                                
                                <input type="hidden" name="slot_start" x-ref="slot_start">
                                <input type="hidden" name="slot_end" x-ref="slot_end">

                                <template x-if="slots.length === 0">
                                    <p class="text-center text-rose-500 text-[10px] font-black uppercase italic mt-12 tracking-widest leading-loose">Matrix Depleted: No availability detected for current cycle.</p>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="mt-20 flex justify-end gap-8">
                        <button type="button" @click="showBookingModal = false" class="text-[10px] font-black text-slate-300 uppercase tracking-widest hover:text-rose-500 transition-colors italic">Abort Sequence</button>
                        <button type="submit" class="px-16 h-20 bg-slate-900 text-white rounded-3xl text-lg font-black italic uppercase tracking-widest hover:bg-black transition-all shadow-2xl shadow-slate-900/20" :disabled="!selectedEmployee">Commit Allocation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-vendor-layout>

<style>
.loader {
    width: 48px;
    height: 48px;
    border: 3px solid #FFF;
    border-bottom-color: #3b82f6;
    border-radius: 50%;
    display: inline-block;
    box-sizing: border-box;
    animation: rotation 1s linear infinite;
}

@keyframes rotation {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
