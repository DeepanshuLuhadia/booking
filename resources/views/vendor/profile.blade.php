<x-vendor-layout>
    <div x-data="{ 
        photoPreview: '{{ $vendor->shop_photo ? asset('storage/' . $vendor->shop_photo) : '' }}',
        handleFileChange(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.photoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
    }">
        <div class="mb-12">
            <h1 class="text-4xl font-black italic tracking-tight uppercase text-slate-900">Protocols <span
                    class="text-blue-600">& Config.</span></h1>
            <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] mt-2 italic">INSTITUTIONAL
                REGISTRY SETTINGS</p>
        </div>

        <form action="{{ route('vendor.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <div class="lg:col-span-2 space-y-12">
                    <div
                        class="bg-white p-10 shadow-2xl shadow-slate-200/50 border border-slate-100 rounded-[3rem] space-y-10">
                        <div class="border-b border-slate-50 pb-6">
                            <h3 class="text-xl font-black italic uppercase italic text-slate-900 tracking-tight">
                                Business Intelligence</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Institutional
                                    Class</label>
                                <select name="vendor_type"
                                    class="w-full h-16 bg-slate-50 border-none rounded-2xl px-6 font-black italic text-slate-900 focus:ring-4 focus:ring-blue-50 transition-all appearance-none cursor-pointer">
                                    <option value="doctor" {{ $vendor->vendor_type == 'doctor' ? 'selected' : ''
                                        }}>Healthcare / Medical</option>
                                    <option value="barber" {{ $vendor->vendor_type == 'barber' ? 'selected' : ''
                                        }}>Beauty / Grooming</option>
                                    <option value="activity" {{ $vendor->vendor_type == 'activity' ? 'selected' : ''
                                        }}>Fitness / Coaching</option>
                                    <option value="training" {{ $vendor->vendor_type == 'training' ? 'selected' : ''
                                        }}>Learning / Skills</option>
                                    <option value="consultant" {{ $vendor->vendor_type == 'consultant' ? 'selected' : ''
                                        }}>Professional Services</option>
                                </select>
                            </div>
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Establishment
                                    Name</label>
                                <input type="text" name="business_name" value="{{ $vendor->business_name }}" required
                                    class="w-full h-16 bg-slate-50 border-none rounded-2xl px-6 font-black italic text-slate-900 placeholder:text-slate-100 focus:ring-4 focus:ring-blue-50 transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Principal
                                    Delegate</label>
                                <input type="text" name="owner_name" value="{{ $vendor->owner_name }}" required
                                    class="w-full h-16 bg-slate-50 border-none rounded-2xl px-6 font-black italic text-slate-900 placeholder:text-slate-100 focus:ring-4 focus:ring-blue-50 transition-all">
                            </div>

                            <div class="space-y-4 relative">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Contact Number</label>
                                <input type="text" name="contact_number" value="{{ $vendor->contact_number }}" required
                                    class="w-full h-16 bg-slate-50 border-none rounded-2xl px-6 font-black italic text-slate-900 placeholder:text-slate-100 focus:ring-4 focus:ring-blue-50 transition-all">
                                
                                <div class="flex items-center gap-3 pt-2 ml-4">
                                    <input type="checkbox" name="show_contact_number" value="1" {{ $vendor->show_contact_number ? 'checked' : '' }}
                                        class="w-5 h-5 bg-slate-100 border-none rounded text-blue-600 focus:ring-2 focus:ring-blue-50 transition-all cursor-pointer">
                                    <span class="text-[9px] font-black text-slate-500 uppercase italic tracking-widest cursor-pointer" onclick="this.previousElementSibling.click()">Display to customers</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <label
                                class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Physical
                                Coordinates / Address</label>
                            <textarea name="address" rows="3" required
                                class="w-full bg-slate-50 border-none rounded-[2rem] p-6 font-black italic text-slate-900 placeholder:text-slate-100 focus:ring-4 focus:ring-blue-50 transition-all">{{ $vendor->address }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Latitude
                                    Reference</label>
                                <input type="text" name="latitude" value="{{ $vendor->latitude }}"
                                    class="w-full h-16 bg-slate-50 border-none rounded-2xl px-6 font-black italic text-slate-900 placeholder:text-slate-100 focus:ring-4 focus:ring-blue-50 transition-all">
                            </div>
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Longitude
                                    Reference</label>
                                <input type="text" name="longitude" value="{{ $vendor->longitude }}"
                                    class="w-full h-16 bg-slate-50 border-none rounded-2xl px-6 font-black italic text-slate-900 placeholder:text-slate-100 focus:ring-4 focus:ring-blue-50 transition-all">
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white p-10 shadow-2xl shadow-slate-200/50 border border-slate-100 rounded-[3rem] space-y-10">
                        <div class="border-b border-slate-50 pb-6">
                            <h3 class="text-xl font-black italic uppercase italic text-slate-900 tracking-tight">
                                Financial & Temporal Sync</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Appointment
                                    Mode</label>
                                <select name="appointment_mode"
                                    class="w-full h-16 bg-slate-50 border-none rounded-2xl px-6 font-black italic text-slate-900 focus:ring-4 focus:ring-blue-50 transition-all appearance-none cursor-pointer">
                                    <option value="time_slot" {{ $vendor->appointment_mode == 'time_slot' ? 'selected' :
                                        '' }}>Time Slot System</option>
                                    <option value="token" {{ $vendor->appointment_mode == 'token' ? 'selected' : ''
                                        }}>Token System</option>
                                </select>
                            </div>
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">UPI
                                    Settlement Link</label>
                                <input type="text" name="upi_id" value="{{ $vendor->upi_id }}"
                                    class="w-full h-16 bg-slate-50 border-none rounded-2xl px-6 font-black italic text-slate-900 placeholder:text-slate-100 focus:ring-4 focus:ring-blue-50 transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Global
                                    Opening Time</label>
                                <input type="time" name="global_opening_time"
                                    value="{{ $vendor->global_opening_time ? \Carbon\Carbon::parse($vendor->global_opening_time)->format('H:i') : '' }}"
                                    class="w-full h-16 bg-slate-50 border-none rounded-2xl px-6 font-black italic text-slate-900 focus:ring-4 focus:ring-blue-50 transition-all text-sm uppercase">
                            </div>
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Global
                                    Closing Time</label>
                                <input type="time" name="global_closing_time"
                                    value="{{ $vendor->global_closing_time ? \Carbon\Carbon::parse($vendor->global_closing_time)->format('H:i') : '' }}"
                                    class="w-full h-16 bg-slate-50 border-none rounded-2xl px-6 font-black italic text-slate-900 focus:ring-4 focus:ring-blue-50 transition-all text-sm uppercase">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-12">
                    <!-- Establishment Visual Identification -->
                    <div class="bg-white p-10 shadow-2xl shadow-slate-200/50 border border-slate-100 rounded-[3rem]">
                        <h3 class="text-xl font-black italic uppercase italic text-slate-900 tracking-tight mb-8">Visual
                            ID</h3>

                        <div
                            class="relative w-full aspect-square rounded-[2.5rem] overflow-hidden mb-8 bg-slate-50 border border-slate-100 flex items-center justify-center shadow-inner group">
                            <template x-if="photoPreview">
                                <img :src="photoPreview"
                                    class="w-full h-full object-cover opacity-90 transition-opacity group-hover:opacity-100">
                            </template>
                            <template x-if="!photoPreview">
                                <span
                                    class="text-[9px] font-black text-slate-200 uppercase tracking-widest italic">Awaiting
                                    Visual Input</span>
                            </template>
                            <div
                                class="absolute inset-0 bg-blue-600/0 group-hover:bg-blue-600/5 transition-all pointer-events-none">
                            </div>
                        </div>

                        <input type="file" name="shop_photo" id="shop_photo_input" class="hidden" accept="image/*"
                            @change="handleFileChange($event)">
                        <button type="button" @click="document.getElementById('shop_photo_input').click()"
                            class="w-full h-16 bg-slate-50 text-slate-900 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all flex items-center justify-center italic">
                            <span x-text="photoPreview ? 'REPLACE VISUAL' : 'INITIATE VISUAL UPLOAD'"></span>
                        </button>
                    </div>

                    <div class="bg-white p-10 shadow-2xl shadow-slate-200/50 border border-slate-100 rounded-[3rem]">
                        <h3 class="text-xl font-black italic uppercase italic text-slate-900 tracking-tight mb-2">Tier
                            Sync</h3>
                        <p class="text-[9px] font-black text-slate-300 uppercase italic tracking-[0.2em] mb-8">ACTIVE
                            OPERATIONAL SUB-LOGIC</p>

                        <div class="p-8 bg-slate-900 rounded-[2rem] text-white shadow-xl shadow-slate-900/20 mb-8">
                            <div class="text-[8px] text-white/40 uppercase font-black italic tracking-widest mb-1">
                                CURRENT RANK</div>
                            <div class="text-3xl font-black italic tracking-tighter">{{ $vendor->subscriptionPlan->name
                                }}</div>
                        </div>

                        <ul class="space-y-4 mb-10">
                            @foreach($vendor->subscriptionPlan->features as $feature)
                            <li
                                class="flex items-center gap-4 text-[10px] font-black text-slate-700 italic tracking-tight uppercase">
                                <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $feature }}
                            </li>
                            @endforeach
                        </ul>

                        <a href="{{ route('vendor.plans') }}"
                            class="w-full h-16 border-2 border-slate-100 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all italic text-slate-900 flex items-center justify-center">Upgrade
                            Rank</a>
                    </div>

                    <div class="pt-8">
                        <button type="submit"
                            class="w-full h-24 bg-slate-900 text-white rounded-[2.5rem] text-xl font-black italic uppercase tracking-widest hover:bg-black transition-all shadow-[0_30px_60px_-15px_rgba(0,0,0,0.3)] flex items-center justify-center gap-6 group">
                            Update
                            <svg class="w-8 h-8 transition-transform group-hover:translate-x-3" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-vendor-layout>