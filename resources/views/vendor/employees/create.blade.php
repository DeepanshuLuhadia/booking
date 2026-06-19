<x-vendor-layout>
    <div x-data="{ 
        photoPreview: null,
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
            <h1 class="text-4xl font-black italic tracking-tight uppercase text-white">Create <span class="text-blue-600">Specialist.</span></h1>
            <!-- <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] mt-2 italic">ROSTER INITIALIZATION PROTOCOL</p> -->
        </div>
        
        <form action="{{ route('vendor.employees.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <div class="lg:col-span-2 space-y-10">
                    <div class="glass-card p-6 sm:p-10 space-y-8">
                        <div class="border-b border-slate-50 pb-6">
                            <h3 class="text-xl font-black italic uppercase italic text-white tracking-tight">Employee Identity</h3>
                        </div>

                        <div class="space-y-4">
                            <label class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Full Name (Display)</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium" placeholder="e.g. Rahul Sharma">
                        </div>

                        <div class="border-t border-white/10 pt-6 mt-6">
                            <h4 class="text-[10px] font-black italic uppercase text-slate-300 tracking-widest mb-4">Employee Portal Credentials (Optional)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-4">
                                    <label class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Email Address</label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium" placeholder="For employee login">
                                </div>
                                <div class="space-y-4">
                                    <label class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Password</label>
                                    <input type="password" name="password" class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium" placeholder="Min 8 characters">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Shift Start</label>
                                <input type="time" name="working_start_time" value="{{ old('working_start_time',$vendor->global_opening_time) }}" min="{{ $vendor->global_opening_time }}" max="{{ $vendor->global_closing_time }}" required class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium">
                            </div>
                            <div class="space-y-4">
                                <label class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Shift End</label>
                                <input type="time" name="working_end_time" value="{{ old('working_end_time',$vendor->global_closing_time) }}" min="{{ $vendor->global_opening_time }}" max="{{ $vendor->global_closing_time }}" required class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Service Fee (₹)</label>
                                <input type="number" name="service_fee_override" value="{{ old('service_fee_override') }}" step="1" class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium" placeholder="Service Fee">
                            </div>
                            <div class="space-y-4">
                                <label class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Appointment Slot Duration (MIN)</label>
                                <input type="number" name="slot_duration" value="{{ old('slot_duration')??15 }}" step="15" min="15" required class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Premium Fee (₹)</label>
                                <input type="number" name="premium_fee" value="{{ old('premium_fee')??1 }}" step="1" class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium" placeholder="Premium Fee">
                                <p class="text-[8px] font-black text-slate-200 uppercase tracking-widest ml-4 mt-1 italic">EXTRA SERVICE FEE</p>
                            </div>
                            <div class="space-y-4">
                                <label class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Premium Bookings (N Upcoming)</label>
                                <input type="number" name="premium_bookings_count" value="{{ old('premium_bookings_count')??0 }}" min="0" required class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium">
                                <p class="text-[8px] font-black text-slate-200 uppercase tracking-widest ml-4 mt-1 italic">NEXT N SLOTS CHARGED PREMIUM FEE</p>
                            </div>
                        </div>


                    </div>
                </div>

                <div class="space-y-10">
                    <div class="glass-card p-6 sm:p-10">
                        <h3 class="text-xl font-black italic uppercase italic text-white tracking-tight mb-8">Profile</h3>
                        
                        <div class="relative w-full aspect-square rounded-[2.5rem] overflow-hidden mb-8 bg-white/5 border border-white/10 flex items-center justify-center shadow-inner group">
                            <template x-if="photoPreview">
                                <img :src="photoPreview" class="w-full h-full object-cover opacity-90 transition-opacity group-hover:opacity-100">
                            </template>
                            <template x-if="!photoPreview">
                                <span class="text-[9px] font-black text-slate-200 uppercase tracking-widest italic text-center px-4">profile picture</span>
                            </template>
                        </div>

                        <input type="file" name="photo" id="photo_input" class="hidden" accept="image/*" @change="handleFileChange($event)">
                        <button type="button" @click="document.getElementById('photo_input').click()" class="btn-outline w-full h-14 justify-center">
                            UPLOAD PROFILE
                        </button>
                    </div>

                    <div class="pt-6">
                        <button type="submit" class="btn-primary w-full h-14 justify-center text-lg gap-4 group">
                            CREATE EMPLOYEE
                            <svg class="w-6 h-6 transition-transform group-hover:translate-x-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                        <a href="{{ route('vendor.employees.index') }}" class="block w-full text-center mt-8 text-[10px] font-black text-slate-300 uppercase tracking-widest hover:text-rose-500 transition-colors italic">Abort Sequence</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-vendor-layout>
