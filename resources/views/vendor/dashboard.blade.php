<x-vendor-layout>
    <div x-data="{ 
        showBookingModal: false,
        bookingStep: 1,
        selectedEmployee: null,
        selectedEmployeeName: '',
        selectedSlot: null,
        slots: [],
        loadingSlots: false,
        isOffline: false,
        opensAt: '',
        customerName: '',
        customerPhone: '',
        
        async loadSlots(id, name) {
            this.loadingSlots = true;
            this.selectedEmployee = id;
            this.selectedEmployeeName = name;
            this.selectedSlot = null;
            try {
                const res = await fetch(`/api/vendors/{{ $vendor->id }}/employees/${id}/slots`);
                if (!res.ok) throw new Error('API Error');
                const data = await res.json();
                this.isOffline = data.offline || false;
                this.opensAt = data.opens_at || '';
                this.slots = data.slots || [];
            } catch (e) {
                console.error('Failed to load slots', e);
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'FAILED TO LOAD SLOTS', type: 'error' } }));
            }
            this.loadingSlots = false;
        },

        resetModal() {
            this.showBookingModal = false;
            this.bookingStep = 1;
            this.selectedEmployee = null;
            this.selectedSlot = null;
            this.customerName = '';
            this.customerPhone = '';
        },

        selectSlot(slot) {
            this.selectedSlot = slot;
        },

        proceedToConfirmation() {
            if (this.selectedEmployee && this.selectedSlot) {
                this.bookingStep = 2;
            }
        }
    }">

        <!-- Header Operational Intel -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <div class="bg-white p-8 shadow-2xl shadow-slate-200/50 border border-slate-100 rounded-[2.5rem]">
                <p class="text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] mb-4 italic">Total Payload Today</p>
                <h2 class="text-5xl font-black text-slate-900 italic tracking-tighter">{{ $stats['today_bookings'] }}</h2>
            </div>
            <div class="bg-white p-8 shadow-2xl shadow-slate-200/50 border border-slate-100 rounded-[2.5rem]">
                <p class="text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] mb-4 italic">Active Matrix</p>
                <div class="flex items-end gap-3">
                    <h2 class="text-5xl font-black text-slate-900 italic tracking-tighter">{{ $stats['active_employees'] }}</h2>
                    <span class="text-xs font-black text-slate-500 mb-2 italic">/ {{ $stats['plan_limit'] }} ACTIVE SLOTS</span>
                </div>
            </div>
            <div class="bg-white p-8 shadow-2xl shadow-slate-200/50 border border-slate-100 rounded-[2.5rem]">
                <p class="text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] mb-4 italic">Revenue Sync</p>
                <h2 class="text-5xl font-black text-blue-600 italic tracking-tighter">₹{{ number_format($stats['today_revenue']) }}</h2>
            </div>
        </div>

        <div class="space-y-12">
            <!-- Full Width Recent Bookings -->
            <div class="bg-white shadow-2xl shadow-slate-200/50 border border-slate-100 rounded-[3rem] overflow-hidden">
                <div class="p-10 border-b border-slate-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-3xl font-black text-slate-900 italic tracking-tight uppercase">Registry <span class="text-blue-600">Feed.</span></h3>
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] mt-2 italic">REAL-TIME TRANSACTION STREAM</p>
                    </div>
                    <a href="{{ route('vendor.bookings.index') }}" class="px-8 py-3 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all italic">Full Logs</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="text-left bg-slate-50">
                            <tr>
                                <th class="px-10 py-6 text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] italic">Principal</th>
                                <th class="px-10 py-6 text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] italic">Specialist</th>
                                <th class="px-10 py-6 text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] italic">Time Matrix</th>
                                <th class="px-10 py-6 text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] italic">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($recentBookings as $booking)
                                <tr class="hover:bg-slate-50/50 transition-all">
                                    <td class="px-10 py-8">
                                        <div class="font-black text-slate-900 text-lg tracking-tight uppercase italic">{{ $booking->customer_name }}</div>
                                        <div class="text-[9px] text-slate-500 font-black mt-1 uppercase tracking-widest italic">{{ $booking->customer_phone }}</div>
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
                            <h3 class="text-4xl font-black text-slate-900 italic mb-3 uppercase tracking-tight">Walk-in <span class="text-blue-600">Manual.</span></h3>
                            <p class="text-slate-500 text-lg italic leading-relaxed">Direct synchronization of unplanned arrivals.</p>
                        </div>
                        <button @click="showBookingModal = true" {{ !$vendor->isProfileComplete() ? 'disabled' : '' }}
                            class="relative z-10 w-full md:w-auto px-12 py-6 bg-slate-900 text-white rounded-[2rem] text-xl font-black italic uppercase tracking-widest hover:bg-black transition-all flex items-center justify-center gap-6 group {{ !$vendor->isProfileComplete() ? 'opacity-20 cursor-not-allowed grayscale' : '' }}">
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
                                <h4 class="text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] italic">Growth Incentive</h4>
                            </div>
                            <p class="text-sm text-slate-500 mb-8 font-medium italic">Bounty: <span class="text-slate-900 font-black italic">₹150</span> per verified referral.</p>
                            
                            <div class="flex flex-col gap-4">
                                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between group">
                                    <span class="text-base font-black text-slate-900 tracking-widest">{{ $vendor->referral_code }}</span>
                                    <button @click="navigator.clipboard.writeText('{{ $vendor->referral_code }}'); window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'REFERRAL CODE COPIED', type: 'success' } }))" class="text-slate-500 hover:text-blue-600 transition-colors">
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
        <div x-show="showBookingModal" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" 
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <div @click="resetModal()" class="absolute inset-0 bg-slate-900/60 backdrop-blur-xl"></div>
            
            <div class="relative bg-slate-900 border border-white/10 w-full max-w-5xl max-h-[90vh] overflow-hidden flex flex-col shadow-[0_50px_100px_-20px_rgba(0,0,0,0.5)] rounded-[2.5rem] sm:rounded-[4rem] text-white">
                
                <!-- Modal Header -->
                <div class="p-8 sm:p-10 border-b border-white/5 flex items-center justify-between shrink-0 bg-white/5">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="w-8 h-1 bg-blue-500 rounded-full"></span>
                            <span class="text-blue-500 font-black text-[9px] uppercase tracking-widest italic" x-text="'STEP ' + (bookingStep === 1 ? '01' : '02')"></span>
                        </div>
                        <h3 class="text-2xl sm:text-3xl font-black italic tracking-tighter uppercase">
                            Registry <span class="text-blue-500" x-text="bookingStep === 1 ? 'Selection' : 'Confirmation'"></span>
                        </h3>
                    </div>
                    <button @click="resetModal()" class="w-12 h-12 rounded-2xl bg-white/5 flex items-center justify-center text-white/50 hover:text-rose-500 hover:bg-rose-500/10 transition-all border border-white/5">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="flex-grow w-full overflow-y-auto custom-scrollbar">
                    
                    <!-- STEP 1: SELECTION -->
                    <div x-show="bookingStep === 1" class="p-8 sm:p-12 space-y-12 animate-reveal">
                        <div class="flex flex-col lg:flex-row gap-12">
                            
                            <!-- Specialist Selection -->
                            <div class="w-full lg:w-3/5 space-y-8">
                                <h4 class="text-[10px] font-black text-white/40 uppercase tracking-[0.3em] italic">01. Select Matrix Specialist</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($vendor->employees as $employee)
                                        <button @click="loadSlots({{ $employee->id }}, '{{ $employee->name }}')"
                                                class="w-full p-5 flex items-center gap-4 text-left transition-all duration-300 rounded-3xl border-2 group relative overflow-hidden bg-white/5"
                                                :class="selectedEmployee === {{ $employee->id }} ? 'border-blue-500 bg-blue-500/10 shadow-lg shadow-blue-500/10' : 'border-white/5 hover:border-blue-500/50 hover:bg-white/10'">
                                            
                                            <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center border border-white/10 shrink-0">
                                                @if($employee->photo)
                                                    <img src="{{ asset('storage/' . $employee->photo) }}" class="w-full h-full object-cover rounded-2xl">
                                                @else
                                                    <span class="text-lg font-black text-white italic opacity-30">{{ substr($employee->name, 0, 1) }}</span>
                                                @endif
                                            </div>

                                            <div class="flex-grow min-w-0">
                                                <h5 class="text-sm font-black italic uppercase truncate" :class="selectedEmployee === {{ $employee->id }} ? 'text-blue-500' : 'text-white'">{{ $employee->name }}</h5>
                                                <p class="text-[8px] font-black text-white/30 uppercase tracking-widest mt-1">Available Today</p>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Temporal Selection -->
                            <div class="w-full lg:w-2/5 space-y-8">
                                <h4 class="text-[10px] font-black text-white/40 uppercase tracking-[0.3em] italic">02. Temporal Matrix</h4>
                                
                                <div class="bg-black/20 rounded-[2.5rem] p-8 border border-white/5 min-h-[400px] flex flex-col">
                                    <div x-show="!selectedEmployee" class="flex-grow flex flex-col items-center justify-center text-center opacity-20 py-10">
                                        <svg class="h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <p class="text-[10px] font-black uppercase tracking-widest italic leading-relaxed">Awaiting Specialist Selection</p>
                                    </div>

                                    <div x-show="loadingSlots" class="flex-grow flex items-center justify-center">
                                        <div class="w-10 h-10 border-4 border-white/10 border-t-blue-500 rounded-full animate-spin"></div>
                                    </div>

                                    <div x-show="selectedEmployee && !loadingSlots" class="flex flex-col gap-4">
                                        <template x-if="isOffline">
                                            <div class="flex-grow flex flex-col items-center justify-center text-center py-20 animate-reveal">
                                                <div class="w-16 h-16 rounded-full bg-rose-500/10 flex items-center justify-center mb-6 border border-rose-500/20">
                                                    <svg class="h-8 w-8 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                                <h5 class="text-xs font-black text-rose-500 uppercase tracking-widest italic mb-2">Outside Service Window</h5>
                                                <p class="text-[9px] font-black text-white/30 uppercase tracking-[0.2em] italic">Registry re-opens at <span class="text-white" x-text="opensAt"></span></p>
                                            </div>
                                        </template>

                                        <template x-if="!isOffline">
                                            <div class="flex flex-wrap gap-3">
                                                <template x-for="slot in slots" :key="slot.start">
                                                    <button @click="selectSlot(slot)"
                                                            :disabled="!slot.available"
                                                            class="min-w-[100px] flex-grow p-4 rounded-2xl border-2 text-center transition-all duration-300 relative bg-white/5"
                                                            :class="{
                                                                'opacity-20 cursor-not-allowed': !slot.available,
                                                                'border-blue-500 bg-blue-500 text-white shadow-lg shadow-blue-500/20': selectedSlot && selectedSlot.start === slot.start,
                                                                'border-white/10 hover:border-blue-500/50 hover:bg-white/10': slot.available && (!selectedSlot || selectedSlot.start !== slot.start)
                                                            }">
                                                        <span class="text-xs font-black italic tracking-tighter block" x-text="slot.start"></span>
                                                        <span class="text-[7px] font-black uppercase tracking-widest opacity-40" x-text="slot.available ? 'Select' : 'Booked'"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </template>
                                        
                                        <template x-if="!isOffline && slots.length === 0">
                                            <div class="py-10 text-center opacity-30 italic">
                                                <p class="text-[9px] font-black uppercase tracking-widest">No Active Slots Found</p>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: CUSTOMER DETAILS -->
                    <div x-show="bookingStep === 2" class="p-8 sm:p-12 max-w-2xl mx-auto space-y-10 animate-reveal">
                        <section class="space-y-8">
                            <div class="text-center">
                                <h4 class="text-[10px] font-black text-blue-500 uppercase tracking-[0.3em] mb-4 italic">Customer Intelligence</h4>
                                <p class="text-white/40 text-sm italic">Synchronize the physical arrival with our digital registry.</p>
                            </div>

                            <form action="{{ route('vendor.bookings.store') }}" method="POST" id="manualBookingForm" class="space-y-6">
                                @csrf
                                <input type="hidden" name="employee_id" :value="selectedEmployee">
                                <input type="hidden" name="slot_start" :value="selectedSlot ? selectedSlot.start : ''">
                                <input type="hidden" name="slot_end" :value="selectedSlot ? selectedSlot.end : ''">

                                <div class="space-y-6">
                                    <div class="space-y-2">
                                        <label class="text-[9px] font-black uppercase tracking-widest text-white/30 ml-4 italic">Full Name</label>
                                        <div class="relative group">
                                            <input type="text" name="customer_name" x-model="customerName" required 
                                                   class="w-full h-16 bg-white/5 border-2 border-white/10 rounded-2xl px-6 font-black italic text-white focus:border-blue-500 focus:bg-white/10 transition-all outline-none">
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[9px] font-black uppercase tracking-widest text-white/30 ml-4 italic">Contact Number</label>
                                        <div class="relative group">
                                            <input type="tel" name="customer_phone" x-model="customerPhone" maxlength="10"
                                                   class="w-full h-16 bg-white/5 border-2 border-white/10 rounded-2xl px-6 font-black italic text-white focus:border-blue-500 focus:bg-white/10 transition-all outline-none"
                                                   placeholder="10 digit number">
                                        </div>
                                    </div>
                                </div>

                                <!-- Summary Card -->
                                <div class="bg-black/40 rounded-[2.5rem] p-8 mt-10 border border-white/5 relative overflow-hidden">
                                    <div class="absolute inset-0 bg-blue-500 opacity-5"></div>
                                    <div class="relative z-10 space-y-4">
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-white/30 font-black uppercase tracking-widest italic">Specialist</span>
                                            <span class="font-black italic uppercase" x-text="selectedEmployeeName"></span>
                                        </div>
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-white/30 font-black uppercase tracking-widest italic">Temporal Slot</span>
                                            <span class="font-black italic uppercase" x-text="selectedSlot ? selectedSlot.start : ''"></span>
                                        </div>
                                        <div class="pt-4 border-t border-white/5 flex justify-between items-center">
                                            <span class="text-lg font-black italic uppercase tracking-tighter">Status</span>
                                            <span class="px-4 py-1.5 bg-emerald-500/10 text-emerald-500 rounded-xl text-[9px] font-black uppercase tracking-widest border border-emerald-500/20 italic">WALK-IN READY</span>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-8 sm:p-10 border-t border-white/5 flex flex-col sm:flex-row items-center justify-between gap-6 shrink-0 bg-white/5">
                    <div class="flex items-center gap-4">
                        <template x-if="bookingStep === 2">
                            <button @click="bookingStep = 1" class="text-[10px] font-black text-white/30 uppercase tracking-widest hover:text-white transition-colors italic flex items-center gap-2 group">
                                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                Back to Matrix
                            </button>
                        </template>
                        <template x-if="bookingStep === 1">
                            <button @click="resetModal()" class="text-[10px] font-black text-white/30 uppercase tracking-widest hover:text-rose-500 transition-colors italic">Abort Sequence</button>
                        </template>
                    </div>

                    <div class="w-full sm:w-auto flex items-center gap-4">
                        <template x-if="bookingStep === 1">
                            <button @click="proceedToConfirmation()" 
                                    :disabled="!selectedEmployee || !selectedSlot"
                                    class="w-full sm:w-auto px-12 py-5 bg-blue-600 text-white rounded-3xl text-sm font-black italic uppercase tracking-widest hover:bg-blue-500 transition-all shadow-xl shadow-blue-500/20 disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center gap-3 group">
                                Next Phase
                                <svg class="w-5 h-5 transition-transform group-hover:translate-x-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </template>
                        <template x-if="bookingStep === 2">
                            <button @click="document.getElementById('manualBookingForm').submit()"
                                    :disabled="!customerName || customerPhone.length < 10"
                                    class="w-full sm:w-auto px-12 py-5 bg-emerald-600 text-white rounded-3xl text-sm font-black italic uppercase tracking-widest hover:bg-emerald-500 transition-all shadow-xl shadow-emerald-500/20 disabled:opacity-30 disabled:cursor-not-allowed group">
                                Commit Allocation
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-vendor-layout>

<style>
/* Custom Scrollbar for a premium feel */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.02);
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(59, 130, 246, 0.3);
}

.animate-reveal {
    animation: reveal 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes reveal {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
}

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
