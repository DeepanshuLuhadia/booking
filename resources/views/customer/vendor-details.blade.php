<x-app-layout>
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8" x-data="bookingSystem()">
        <!-- Vendor Profile Header -->
        <div class="bg-white rounded-[4rem] shadow-[0_40px_100px_-20px_rgba(0,0,0,0.05)] mb-16 overflow-hidden border border-slate-50 relative group">
            <div class="h-96 bg-slate-900 relative">
                @if($vendor->shop_photo)
                    <img src="{{ asset('storage/' . $vendor->shop_photo) }}" class="w-full h-full object-cover opacity-80 transition-transform duration-1000 group-hover:scale-105">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-slate-800 to-slate-950 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-32 w-32 text-slate-700/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-white via-white/20 to-transparent"></div>
                
                <!-- Advanced Share Action -->
                <button @click="shareShop()" class="absolute top-10 right-10 w-16 h-16 bg-white/10 backdrop-blur-2xl rounded-3xl flex items-center justify-center text-white border border-white/20 hover:bg-white/30 hover:scale-110 active:scale-95 transition-all shadow-2xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                </button>
            </div>
            
            <div class="px-16 pb-16 -mt-32 relative z-10 flex flex-col md:flex-row items-end justify-between gap-12">
                <div class="flex flex-col md:flex-row items-end gap-10">
                    <div class="w-56 h-56 rounded-[3.5rem] bg-white shadow-[0_30px_60px_-15px_rgba(0,0,0,0.1)] p-3 border border-slate-100 shrink-0">
                         <div class="w-full h-full bg-gradient-to-br from-blue-600 to-indigo-600 rounded-[3rem] flex items-center justify-center text-8xl font-black text-white italic shadow-inner">
                             {{ substr($vendor->business_name, 0, 1) }}
                         </div>
                    </div>
                    <div class="mb-6 space-y-4">
                        <div class="flex items-center gap-5">
                            <h1 class="text-6xl font-black text-slate-900 tracking-tight leading-none">{{ $vendor->business_name }}</h1>
                            @php $isAvailable = $vendor->hasAvailableSlotsToday(); @endphp
                            <span class="px-5 py-2 {{ $isAvailable ? 'bg-emerald-100 text-emerald-600 border-emerald-200' : 'bg-rose-100 text-rose-600 border-rose-200' }} text-xs font-black rounded-2xl border uppercase tracking-[0.2em]">
                                {{ $isAvailable ? 'Secure Now' : 'Fully Booked' }}
                            </span>
                        </div>
                        <p class="text-slate-400 font-bold text-xl flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                            </div>
                            {{ $vendor->address ?: 'Downtown Luxury District, Mumbai' }}
                        </p>
                    </div>
                </div>
                
                <div class="mb-6 flex items-center bg-slate-50/50 backdrop-blur-md p-6 rounded-[2.5rem] border border-slate-100 shadow-sm">
                    <div class="px-8 border-r border-slate-200 text-center">
                        <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest mb-1 font-bold">Trust Score</p>
                        <p class="text-3xl font-black text-slate-900 leading-none flex items-center justify-center gap-1">4.9 <span class="text-amber-500 text-2xl">★</span></p>
                    </div>
                    <div class="px-8 text-center">
                        <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest mb-1 font-bold">Starting From</p>
                        <p class="text-3xl font-black text-blue-600 leading-none">₹{{ number_format($vendor->service_fee) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-16">
            <!-- Professional Selection -->
            <div class="lg:col-span-4 space-y-10">
                <div class="flex items-center justify-between">
                    <h3 class="text-4xl font-black tracking-tight text-slate-900 italic">Select Talent</h3>
                    <div class="w-12 h-1 bg-blue-600 rounded-full"></div>
                </div>
                
                <div class="space-y-6">
                    @forelse($vendor->employees as $employee)
                        <button 
                            @click="fetchSlots({{ $employee->id }}, {{ $employee->service_fee_override ?? $vendor->service_fee }})"
                            class="w-full bg-white p-8 flex items-center gap-6 text-left transition-all duration-500 rounded-[2.5rem] border-2 group relative overflow-hidden"
                            :class="selectedEmployee === {{ $employee->id }} ? 'border-blue-600 bg-blue-50/20 shadow-2xl shadow-blue-200/50' : 'border-slate-50 hover:border-blue-100 hover:translate-x-2'"
                        >
                            <div class="w-24 h-24 rounded-3xl bg-slate-50 border-2 overflow-hidden shadow-inner transition-all group-hover:scale-110" :class="selectedEmployee === {{ $employee->id }} ? 'border-white' : 'border-slate-100'">
                                @if($employee->photo)
                                    <img src="{{ asset('storage/' . $employee->photo) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-4xl font-black text-slate-200 italic">
                                        {{ substr($employee->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow">
                                <h4 class="font-black text-2xl text-slate-900 group-hover:text-blue-600 transition-colors">{{ $employee->name }}</h4>
                                <div class="flex items-center gap-2 mt-2">
                                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Production Ready
                                    </p>
                                    @if($employee->service_fee_override)
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-600 text-[8px] font-black rounded uppercase tracking-wider">Premium Rate</span>
                                    @endif
                                </div>
                            </div>
                        </button>
                    @empty
                        <div class="bg-slate-50 rounded-[3rem] p-16 text-center text-slate-300 border-4 border-dashed border-slate-100 font-black italic text-2xl">
                            TALENT OFFLINE
                        </div>
                    @endforelse
                </div>

                <!-- Details collected only after slot selection now -->
            </div>

            <!-- AI-Optimized Slot Grid -->
            <div class="lg:col-span-8 space-y-12">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div class="space-y-2">
                        <h3 class="text-5xl font-black tracking-tight text-slate-900 italic leading-none">Smart Windows</h3>
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Real-time dynamic availability</p>
                    </div>
                    <div class="flex items-center gap-8 bg-white px-8 py-4 rounded-3xl border border-slate-50 shadow-sm">
                        <div class="flex items-center gap-3"><span class="w-4 h-4 rounded-full bg-blue-600 shadow-lg shadow-blue-200"></span> <span class="text-[11px] font-black text-slate-900 uppercase tracking-widest">Premium</span></div>
                        <div class="flex items-center gap-3"><span class="w-4 h-4 rounded-full bg-amber-500 shadow-lg shadow-amber-200"></span> <span class="text-[11px] font-black text-slate-900 uppercase tracking-widest">Express</span></div>
                    </div>
                </div>

                <div x-show="loading" class="py-32 flex items-center justify-center">
                    <div class="relative w-24 h-24">
                        <div class="absolute inset-0 border-8 border-blue-100 rounded-full"></div>
                        <div class="absolute inset-0 border-8 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                    </div>
                </div>

                <div x-show="!loading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-8" x-cloak>
                    <template x-for="slot in slots" :key="slot.start">
                        <button 
                            :disabled="!slot.available && !slot.requires_emergency"
                            @click="initiateBooking(slot)"
                            class="relative p-10 bg-white rounded-[3rem] text-center transition-all duration-500 border-2 group shadow-xl hover:shadow-[0_40px_80px_-20px_rgba(37,99,235,0.2)]"
                            :class="{
                                'opacity-30 grayscale cursor-not-allowed border-slate-50 shadow-none': !slot.available && !slot.requires_emergency,
                                'border-slate-50 hover:border-blue-600 hover:-translate-y-3': slot.available,
                                'border-amber-100 bg-amber-50/30 hover:border-amber-500 hover:-translate-y-3': slot.requires_emergency && !slot.available,
                            }"
                        >
                            <p class="text-3xl font-black mb-2 italic" :class="slot.requires_emergency && !slot.available ? 'text-amber-500' : 'text-blue-600'" x-text="slot.start"></p>
                            <span class="text-[11px] font-black uppercase tracking-[0.25em] px-4 py-1.5 rounded-full" 
                                  :class="slot.requires_emergency && !slot.available ? 'bg-amber-100 text-amber-600' : 'bg-blue-50 text-blue-400' " 
                                  x-text="slot.requires_emergency && !slot.available ? 'Urgent' : 'Elite' "></span>
                            
                            <div x-show="slot.requires_emergency && !slot.available" class="mt-6 text-[10px] bg-amber-500 text-white py-2 rounded-2xl font-black shadow-lg shadow-amber-200/50 uppercase tracking-[0.15em]">+₹{{ number_format($vendor->emergency_fee) }} EXTRA</div>
                        </button>
                    </template>
                </div>
                
                <div x-show="slots.length === 0 && !loading" class="bg-slate-50 rounded-[4rem] p-32 text-center text-slate-300 border-4 border-dashed border-slate-100 font-bold italic">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-32 w-32 mx-auto mb-8 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-3xl uppercase tracking-widest font-black">Looking for available slots...</p>
                </div>
            </div>
        </div>

        <!-- Multi-Stage Payment & Confirmation Modal -->
        <div x-show="bookingModal" class="fixed inset-0 z-[200] flex items-center justify-center p-6" x-cloak x-transition>
            <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-2xl"></div>
            <div class="relative bg-white rounded-[4rem] p-16 text-center max-w-2xl shadow-[0_50px_100px_-20px_rgba(0,0,0,0.5)] border border-white overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-600 to-sky-400"></div>
                
                <div class="w-32 h-32 bg-blue-50 text-blue-600 rounded-[2.5rem] flex items-center justify-center mx-auto mb-10 shadow-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                
                <h2 class="text-5xl font-black text-slate-900 mb-6 tracking-tight">Final Step: Secure Booking</h2>
                
                <!-- Guest details collected after selecting slot -->
                <div class="space-y-6 mb-10 text-left">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-4">Full Name</label>
                        <input type="text" x-model="guestName" class="w-full h-16 px-8 bg-slate-50 border-2 border-slate-100 rounded-3xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all font-bold text-slate-800 placeholder:text-slate-300" placeholder="e.g. John Doe">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-4">Mobile Number</label>
                        <input type="tel" x-model="guestPhone" maxlength="10" class="w-full h-16 px-8 bg-slate-50 border-2 border-slate-100 rounded-3xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all font-bold text-slate-800 placeholder:text-slate-300" placeholder="10 Digit Number">
                    </div>
                </div>

                <div class="bg-slate-50 rounded-[2.5rem] p-10 mb-10 text-left space-y-4">
                    <div class="flex justify-between items-center text-xl">
                        <span class="text-slate-400 font-bold uppercase tracking-widest text-xs">Service Fee</span>
                        <span class="text-slate-900 font-black" x-text="'₹' + selectedServiceFee"></span>
                    </div>
                    <div class="flex justify-between items-center text-xl" x-show="selectedSlot?.requires_emergency">
                        <span class="text-amber-500 font-bold uppercase tracking-widest text-xs italic">Express Priority</span>
                        <span class="text-amber-500 font-black">₹{{ number_format($vendor->emergency_fee) }}</span>
                    </div>
                    <div class="pt-6 border-t border-slate-200 flex justify-between items-center">
                        <span class="text-slate-900 font-black text-2xl tracking-tighter uppercase italic">Amount to Pay Now</span>
                        <span class="text-4xl font-black text-blue-600" x-text="'₹' + totalAmount"></span>
                    </div>
                    <p class="text-[10px] text-center text-slate-400 font-bold uppercase tracking-widest mt-4">Total booking value will be adjusted at venue</p>
                </div>

                <button @click="confirmBooking()" class="w-full h-20 bg-blue-600 text-white text-2xl font-black rounded-[2.2rem] shadow-2xl shadow-blue-200 hover:bg-blue-700 transition-all flex items-center justify-center gap-4">
                    <span x-text="isTokenEnabled ? 'PAY & CONFIRM' : 'CONFIRM APPOINTMENT' "></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
                <button @click="bookingModal = false" class="mt-6 text-slate-400 font-black uppercase tracking-widest text-xs hover:text-slate-600">Cancel Request</button>
            </div>
        </div>

        <!-- Success Confirmation -->
        <div x-show="successModal" class="fixed inset-0 z-[300] flex items-center justify-center p-6" x-cloak x-transition>
            <div class="absolute inset-0 bg-emerald-600/10 backdrop-blur-3xl"></div>
            <div class="relative bg-white rounded-[4rem] p-16 text-center max-w-xl shadow-2xl border-4 border-emerald-100">
                <div class="w-40 h-40 bg-emerald-100 text-emerald-600 rounded-[3rem] flex items-center justify-center mx-auto mb-10 animate-bounce">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="text-5xl font-black text-slate-900 mb-4 italic tracking-tight uppercase">Booking Confirmed!</h2>
                <p class="text-slate-500 text-xl font-medium mb-12" x-text="successMsg"></p>
                <button @click="window.location.href='/'" class="w-full h-20 bg-slate-900 text-white text-2xl font-black rounded-[2rem] hover:bg-black transition-all">BACK TO MARKETPLACE</button>
            </div>
        </div>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        function bookingSystem() {
            return {
                selectedEmployee: {{ $selectedEmployee ? $selectedEmployee->id : 'null' }},
                selectedServiceFee: {{ $selectedEmployee ? ($selectedEmployee->service_fee_override ?? $vendor->service_fee) : 0 }},
                slots: @js($slots),
                loading: false,
                bookingModal: false,
                successModal: false,
                successMsg: '',
                selectedSlot: null,
                guestName: '',
                guestPhone: '',
                isTokenEnabled: {{ $vendor->token_booking_enabled ? 'true' : 'false' }},
                baseToken: {{ $vendor->token_amount ?: 0 }},
                emergencyFee: {{ $vendor->emergency_fee ?: 0 }},
                totalAmount: 0,
                
                async fetchSlots(id, fee = 0) {
                    this.loading = true;
                    this.selectedEmployee = id;
                    this.selectedServiceFee = fee;
                    try {
                        const res = await fetch(`/api/vendors/{{ $vendor->id }}/employees/${id}/slots`);
                        if (!res.ok) throw new Error('API ERROR');
                        this.slots = await res.json();
                    } catch (e) { 
                        console.error(e); 
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'FAILED TO SYNC SLOTS', type: 'error' } }));
                    }
                    this.loading = false;
                },

                initiateBooking(slot) {
                    this.selectedSlot = slot;
                    const expressFee = (slot.requires_emergency && !slot.available) ? this.emergencyFee : 0;
                    // Formula: Token (if any) + Emergency Fee (if any)
                    this.totalAmount = (this.isTokenEnabled ? this.baseToken : 0) + expressFee;
                    this.bookingModal = true;
                },

                async confirmBooking() {
                    if (!this.guestName || this.guestPhone.length < 10) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'ENTER VALID CONTACT DETAILS', type: 'error' } }));
                        return;
                    }

                    if (this.totalAmount > 0) {
                        // Mocking Razorpay Flow
                        const options = {
                            "key": "rzp_test_mock", 
                            "amount": this.totalAmount * 100,
                            "name": "BOOKAI Premium",
                            "handler": (response) => this.submitBooking(response.razorpay_payment_id),
                            "modal": { "ondismiss": () => { console.log('Payment dismissed'); } }
                        };
                        // In a real app we'd call Razorpay(options).open()
                        // For demo, we auto-confirm after payment "simulation"
                        this.submitBooking('pay_mock_' + Math.random().toString(36).substr(2, 9));
                    } else {
                        this.submitBooking(null);
                    }
                },

                async submitBooking(paymentId) {
                    this.bookingModal = false;
                    this.loading = true;
                    
                    try {
                        const res = await fetch('/bookings', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                vendor_id: {{ $vendor->id }},
                                employee_id: this.selectedEmployee,
                                slot_start: this.selectedSlot.start,
                                slot_end: this.selectedSlot.end,
                                booking_type: this.selectedSlot.requires_emergency && !this.selectedSlot.available ? 'emergency' : 'normal',
                                customer_name: this.guestName,
                                customer_phone: this.guestPhone,
                                payment_id: paymentId
                            })
                        });
                        
                        const data = await res.json();
                        if (data.success) {
                            this.successMsg = data.message;
                            this.successModal = true;
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'BOOKING SECURED SUCCESSFULLY', type: 'success' } }));
                        } else {
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.error || 'VALIDATION FAILED', type: 'error' } }));
                        }
                    } catch (e) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'NETWORK CONNECTIVITY ISSUES', type: 'error' } }));
                    }
                    this.loading = false;
                },

                shareShop() {
                    const url = window.location.href;
                    if (navigator.share) {
                        navigator.share({ title: 'Book AI Elite: {{ $vendor->business_name }}', url: url });
                    } else {
                        navigator.clipboard.writeText(url);
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'LINK COPIED TO CLIPBOARD', type: 'success' } }));
                    }
                }
            }
        }
    </script>
</x-app-layout>
