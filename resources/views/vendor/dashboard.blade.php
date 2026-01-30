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

        <!-- Header Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="glass-card p-6 bg-gradient-to-br from-primary-500/10 to-transparent">
                <p class="text-gray-400 text-sm mb-1">Total Bookings Today</p>
                <h2 class="text-4xl font-black">{{ $stats['today_bookings'] }}</h2>
            </div>
            <div class="glass-card p-6 bg-gradient-to-br from-purple-500/10 to-transparent">
                <p class="text-gray-400 text-sm mb-1">Active Employees</p>
                <h2 class="text-4xl font-black">{{ $stats['active_employees'] }} / {{ $stats['plan_limit'] }}</h2>
                <p class="text-gray-500 text-xs mt-2">Maximum for your plan</p>
            </div>
            <div class="glass-card p-6 bg-gradient-to-br from-yellow-500/10 to-transparent">
                <p class="text-gray-400 text-sm mb-1">Today's Revenue</p>
                <h2 class="text-4xl font-black">₹{{ number_format($stats['today_revenue']) }}</h2>
            </div>
        </div>

        <div class="space-y-10">
            <!-- Full Width Recent Bookings (MOVED TO TOP) -->
            <div class="glass-card overflow-hidden">
                <div class="p-8 border-b border-white/5 flex items-center justify-between bg-white/[0.02]">
                    <div>
                        <h3 class="text-3xl font-black italic">Recent Bookings</h3>
                        <p class="text-sm text-gray-500 mt-1 uppercase tracking-widest font-bold">Real-time Appointment Stream</p>
                    </div>
                    <a href="{{ route('vendor.bookings.index') }}" class="btn-outline py-3 px-8 text-sm hover:bg-white/5 shadow-xl">View Full Transaction History</a>
                </div>
                <div class="overflow-x-auto p-2">
                    <table class="w-full">
                        <thead class="text-left text-xs uppercase text-gray-500 tracking-[0.2em] font-black border-b border-white/5">
                            <tr>
                                <th class="px-8 py-6">Customer</th>
                                <th class="px-8 py-6">Professional</th>
                                <th class="px-8 py-6">Time Window</th>
                                <th class="px-8 py-6">Status</th>
                                <th class="px-8 py-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($recentBookings as $booking)
                                <tr class="hover:bg-white/[0.03] transition-all group">
                                    <td class="px-8 py-6">
                                        <div class="font-black text-white text-lg tracking-tight">{{ $booking->customer_name }}</div>
                                        <div class="text-xs text-gray-500 font-bold mt-1">{{ $booking->customer_phone }}</div>
                                    </td>
                                    <td class="px-8 py-6 text-gray-300">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-primary-600/20 flex items-center justify-center text-xs font-black text-primary-400 border border-primary-500/20">
                                                {{ substr($booking->employee->name, 0, 1) }}
                                            </div>
                                            <span class="font-bold text-base">{{ $booking->employee->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="text-base font-black text-white italic">{{ $booking->booking_date->format('l, M d') }}</div>
                                        <div class="text-xs text-primary-400 font-bold mt-1 uppercase tracking-widest">{{ $booking->slot_start_time }} - {{ $booking->slot_end_time }}</div>
                                    </td>
                                    <td class="px-8 py-6">
                                        @php
                                            $color = match($booking->status) {
                                                'confirmed' => 'bg-green-500/10 text-green-400 border-green-500/20',
                                                'cancelled' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                                'completed' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                                default => 'bg-gray-500/10 text-gray-400 border-gray-500/20'
                                            };
                                        @endphp
                                        <span class="px-5 py-2 {{ $color }} rounded-2xl text-[10px] font-black uppercase border tracking-widest">{{ $booking->status }}</span>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <button class="p-3 hover:bg-white/10 rounded-2xl text-gray-500 hover:text-white transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-24 text-center">
                                        <div class="flex flex-col items-center opacity-20">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <p class="text-2xl font-black uppercase tracking-[0.3em] italic">No Transactions Logged</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bottom Section: Quick Action & Referral -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                <div class="lg:col-span-2 space-y-8">
                    <!-- Quick Action -->
                    <div class="glass-card p-10 border-primary-500/20 bg-gradient-to-br from-primary-600/10 to-transparent flex flex-col md:flex-row items-center justify-between gap-8">
                        <div>
                            <h3 class="text-4xl font-black italic mb-3">Walk-in Center</h3>
                            <p class="text-gray-400 text-lg">Instant allocation for current customers.</p>
                        </div>
                        <button @click="showBookingModal = true" class="btn-primary flex items-center gap-4 px-10 py-5 text-xl shadow-2xl hover:scale-105 active:scale-95 transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Secure Slot
                        </button>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 gap-6">
                        <!-- Referral Section -->
                        <div class="glass-card p-8 border-amber-500/20 bg-amber-500/5">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-12 h-12 rounded-2xl bg-amber-500/20 flex items-center justify-center text-amber-500 border border-amber-500/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                                    </svg>
                                </div>
                                <h4 class="text-xs font-black text-gray-200 uppercase tracking-[0.2em] leading-tight">Expansion Reward</h4>
                            </div>
                            <p class="text-sm text-gray-400 mb-6 font-medium">Bounty: ₹150 per active referral.</p>
                            
                            <div class="flex gap-4">
                                <div class="flex-grow p-4 bg-black/40 rounded-2xl border border-white/5 flex items-center justify-between group">
                                    <span class="text-base font-black text-amber-500 tracking-widest">{{ $vendor->referral_code }}</span>
                                    <button @click="navigator.clipboard.writeText('{{ $vendor->referral_code }}'); window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'REFERRAL CODE COPIED', type: 'success' } }))" class="text-gray-500 hover:text-white transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </div>
                                
                                <div class="px-6 bg-amber-500 rounded-2xl flex flex-col justify-center text-white min-w-[120px] shadow-lg shadow-amber-500/20">
                                    <p class="text-[9px] uppercase font-black opacity-80 mb-1">Balance</p>
                                    <p class="text-xl font-black leading-none italic">₹{{ number_format($vendor->referral_balance) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manual Booking Modal (IMPROVED UI) -->
        <div x-show="showBookingModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
            <div @click="showBookingModal = false" class="absolute inset-0 bg-black/90 backdrop-blur-md"></div>
            
            <div class="relative glass-card w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col border-white/20 shadow-2xl">
                <div class="p-6 border-b border-white/10 flex items-center justify-between bg-white/5">
                    <div>
                        <h3 class="text-2xl font-black text-white">New Appointment</h3>
                        <p class="text-sm text-gray-500">Manual slot reservation for walk-in customers.</p>
                    </div>
                    <button @click="showBookingModal = false" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:text-white hover:bg-white/10 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form action="{{ route('vendor.bookings.store') }}" method="POST" class="flex-grow overflow-y-auto p-8">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                        <!-- Left: Customer & Staff -->
                        <div class="space-y-8">
                            <section>
                                <h4 class="text-xs font-black text-primary-400 uppercase tracking-[0.2em] mb-4">1. Customer Information</h4>
                                <div class="space-y-4">
                                    <div class="group">
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1 ml-1">Customer Name</label>
                                        <input type="text" name="customer_name" required class="w-full glass-input py-3" placeholder="Full Name">
                                    </div>
                                    <div class="group">
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1 ml-1">Phone Number</label>
                                        <input type="text" name="customer_phone" class="w-full glass-input py-3" placeholder="+91 XXX XXX XXXX">
                                    </div>
                                </div>
                            </section>

                            <section>
                                <h4 class="text-xs font-black text-purple-400 uppercase tracking-[0.2em] mb-4">2. Assign Professional</h4>
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach($vendor->employees as $employee)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="employee_id" value="{{ $employee->id }}" @click="loadSlots({{ $employee->id }})" class="sr-only peer" required>
                                            <div class="p-4 glass-card border-white/5 peer-checked:border-primary-500 peer-checked:bg-primary-500/10 hover:bg-white/5 transition-all flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl bg-primary-500/20 flex items-center justify-center font-bold text-primary-400 text-sm">
                                                    {{ substr($employee->name, 0, 1) }}
                                                </div>
                                                <div class="text-xs font-bold text-gray-300 truncate">{{ $employee->name }}</div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </section>
                        </div>

                        <!-- Right: Time Slots -->
                        <div class="bg-white/5 rounded-3xl p-6 border border-white/10">
                            <h4 class="text-xs font-black text-green-400 uppercase tracking-[0.2em] mb-6">3. Select Available Time</h4>
                            
                            <div x-show="!selectedEmployee" class="h-64 flex flex-col items-center justify-center text-center opacity-40">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm">Please select a professional first to view their availability</p>
                            </div>

                            <div x-show="loadingSlots" class="h-64 flex items-center justify-center">
                                <span class="loader"></span>
                            </div>

                            <div x-show="selectedEmployee && !loadingSlots">
                                <div class="grid grid-cols-3 gap-3">
                                    <template x-for="slot in slots" :key="slot.start">
                                        <label class="cursor-pointer">
                                            <input type="radio" name="slot_info" :value="slot.start + '|' + slot.end" 
                                                   @change="$refs.slot_start.value = slot.start; $refs.slot_end.value = slot.end"
                                                   class="sr-only peer" :disabled="slot.is_booked">
                                            <div class="p-2.5 glass-card border-white/5 peer-checked:border-primary-500 peer-checked:bg-primary-500 text-center transition-all"
                                                :class="slot.is_booked ? 'opacity-10 cursor-not-allowed' : 'hover:bg-white/10'">
                                                <span class="text-[11px] font-black" x-text="slot.start"></span>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                                
                                <input type="hidden" name="slot_start" x-ref="slot_start">
                                <input type="hidden" name="slot_end" x-ref="slot_end">

                                <template x-if="slots.length === 0">
                                    <p class="text-center text-red-400 text-sm mt-8">No slots available for today.</p>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 flex justify-end gap-4">
                        <button type="button" @click="showBookingModal = false" class="px-8 py-3 text-sm font-bold text-gray-400 hover:text-white transition-all">Discard</button>
                        <button type="submit" class="btn-primary px-12 py-3 text-base shadow-lg shadow-primary-500/20" :disabled="!selectedEmployee">Confirm Appointment</button>
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
