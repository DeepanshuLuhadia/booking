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
            <h1 class="text-4xl font-black italic tracking-tight uppercase text-white">Protocols <span
                    class="text-blue-600">& Config.</span></h1>
            <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] mt-2 italic">INSTITUTIONAL
                REGISTRY SETTINGS</p>
        </div>

        <form action="{{ route('vendor.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <div class="lg:col-span-2 space-y-12">
                    <div
                        class="glass-card p-6 sm:p-10 space-y-10">
                        <div class="border-b border-slate-50 pb-6">
                            <h3 class="text-xl font-black italic uppercase italic text-white tracking-tight">
                                Business Intelligence</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Institutional
                                    Class</label>
                                <div class="relative" x-data="{
                                    open: false,
                                    selected: '{{ $vendor->vendor_type ?? 'doctor' }}',
                                    options: {
                                        'doctor': { label: 'Health', icon: '⚕️' },
                                        'barber': { label: 'Beauty', icon: '✨' },
                                        'activity': { label: 'Sports', icon: '🏆' },
                                        'training': { label: 'Education', icon: '📘' },
                                        'consultant': { label: 'Consultant', icon: '🖊️' }
                                    },
                                    get selectedLabel() {
                                        return this.options[this.selected]?.label || 'Select Category';
                                    },
                                    get selectedIcon() {
                                        return this.options[this.selected]?.icon || '';
                                    }
                                }" @click.away="open = false">
                                    <input type="hidden" name="vendor_type" x-model="selected">
                                    
                                    <div @click="open = !open" 
                                         class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium flex items-center justify-between cursor-pointer transition-all"
                                         :class="open ? 'border-blue-500/50 bg-white/5' : ''">
                                        <div class="flex items-center gap-2">
                                            <span x-text="selectedIcon" class="opacity-90"></span>
                                            <span x-text="selectedLabel" class="text-white text-sm"></span>
                                        </div>
                                        <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-300 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                
                                    <div x-cloak x-show="open" 
                                         x-transition:enter="transition ease-out duration-300"
                                         x-transition:enter-start="opacity-0 -translate-y-2"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-200"
                                         x-transition:leave-start="opacity-100 translate-y-0"
                                         x-transition:leave-end="opacity-0 -translate-y-2"
                                         class="absolute z-[100] w-full mt-2 bg-slate-900 border border-white/10 rounded-2xl p-2 shadow-2xl left-0">
                                        
                                        <template x-for="(data, key) in options" :key="key">
                                            <div @click="selected = key; open = false"
                                                 class="px-4 py-2.5 flex items-center gap-2.5 rounded-lg cursor-pointer transition-all duration-200"
                                                 :class="selected === key 
                                                     ? 'bg-blue-500/10 border-l-4 border-blue-500 text-blue-500' 
                                                     : 'text-white/80 hover:bg-white/5 hover:text-white hover:translate-x-1'">
                                                <span x-text="data.icon" class="text-base"></span>
                                                <span x-text="data.label" class="font-semibold text-sm whitespace-nowrap"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Establishment
                                    Name</label>
                                <input type="text" name="business_name" value="{{ $vendor->business_name }}" required
                                    class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Principal
                                    Delegate</label>
                                <input type="text" name="owner_name" value="{{ $vendor->owner_name }}" required
                                    class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium">
                            </div>

                            <div class="space-y-4 relative">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Contact Number</label>
                                <input type="text" name="contact_number" value="{{ $vendor->contact_number }}" required
                                    class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium">
                                
                                <div class="flex items-center gap-3 pt-2 ml-4">
                                    <input type="checkbox" name="show_contact_number" value="1" {{ $vendor->show_contact_number ? 'checked' : '' }}
                                        class="w-5 h-5 bg-white/10 border-none rounded text-blue-600 focus:ring-2 focus:ring-blue-50 transition-all cursor-pointer">
                                    <span class="text-[9px] font-black text-slate-400 uppercase italic tracking-widest cursor-pointer" onclick="this.previousElementSibling.click()">Display to customers</span>
                                </div>
                            </div>

                            {{-- <div class="space-y-4 relative">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Contact Number</label>
                                <input type="text" name="contact_number" value="{{ $vendor->contact_number }}" required
                                    class="w-full h-16 bg-slate-50 border-none rounded-2xl px-6 font-black italic text-slate-900 placeholder:text-slate-100 focus:ring-4 focus:ring-blue-50 transition-all">
                                
                                <div class="flex items-center gap-3 pt-2 ml-4">
                                    <input type="checkbox" name="show_contact_number" value="1" {{ $vendor->show_contact_number ? 'checked' : '' }}
                                        class="w-5 h-5 bg-slate-100 border-none rounded text-blue-600 focus:ring-2 focus:ring-blue-50 transition-all cursor-pointer">
                                    <span class="text-[9px] font-black text-slate-500 uppercase italic tracking-widest cursor-pointer" onclick="this.previousElementSibling.click()">Display to customers</span>
                                </div>
                            </div>--}}
                        </div>

                        <div class="space-y-4">
                            <label
                                class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Physical
                                Coordinates / Address</label>
                            <textarea name="address" rows="3" required
                                class="glass-input w-full rounded-2xl p-4 font-medium">{{ $vendor->address }}</textarea>
                        </div>

                        {{-- Coordinates. These drive the "N km away" chip on the
                             customer listing, so an unfilled pair means the shop
                             shows no distance at all — hence the one-tap capture
                             on the right rather than expecting hand-typed decimals. --}}
                        <div class="grid grid-cols-1 md:grid-cols-[1fr_1fr_auto] gap-6 md:gap-8 items-end"
                            x-data="{
                                locating: false,
                                status: '',
                                ok: false,
                                detect() {
                                    if (!navigator.geolocation) {
                                        this.ok = false;
                                        this.status = 'This browser cannot report a location.';
                                        return;
                                    }
                                    // Geolocation is refused outright on plain HTTP, and the
                                    // failure is silent enough to look like a broken button.
                                    if (!window.isSecureContext) {
                                        this.ok = false;
                                        this.status = 'Needs a secure (https) connection.';
                                        return;
                                    }
                                    this.locating = true;
                                    this.status = '';
                                    navigator.geolocation.getCurrentPosition(
                                        (pos) => {
                                            // 7dp matches the decimal(10,7) columns exactly.
                                            this.$refs.lat.value = pos.coords.latitude.toFixed(7);
                                            this.$refs.lng.value = pos.coords.longitude.toFixed(7);
                                            this.$refs.lat.dispatchEvent(new Event('input'));
                                            this.$refs.lng.dispatchEvent(new Event('input'));
                                            this.locating = false;
                                            this.ok = true;
                                            this.status = 'Captured — accurate to about ' +
                                                Math.round(pos.coords.accuracy) + ' m. Save to apply.';
                                        },
                                        (err) => {
                                            this.locating = false;
                                            this.ok = false;
                                            this.status = err.code === 1
                                                ? 'Permission denied — allow location access for this site and retry.'
                                                : (err.code === 3
                                                    ? 'Timed out while locating. Please try again.'
                                                    : 'Location unavailable right now.');
                                        },
                                        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                                    );
                                }
                            }">
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Latitude
                                    Reference</label>
                                <input type="text" name="latitude" x-ref="lat" value="{{ $vendor->latitude }}"
                                    class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium">
                            </div>
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Longitude
                                    Reference</label>
                                <input type="text" name="longitude" x-ref="lng" value="{{ $vendor->longitude }}"
                                    class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium">
                            </div>

                            <button type="button" @click="detect()" :disabled="locating"
                                title="Fill both fields from this device's current location"
                                class="btn-outline w-full md:w-auto min-h-[2.75rem] px-5 justify-center gap-2 whitespace-nowrap disabled:opacity-60 disabled:cursor-wait">
                                {{-- Two separate <svg>s rather than one with x-if inside:
                                     a <template> in the SVG namespace is parsed as an SVG
                                     element, not an HTML template, so Alpine never sees it. --}}
                                <svg x-show="!locating" class="w-4 h-4 shrink-0" fill="none"
                                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="7" />
                                    <circle cx="12" cy="12" r="1.5" fill="currentColor" stroke="none" />
                                    <path stroke-linecap="round" d="M12 2v3M12 19v3M2 12h3M19 12h3" />
                                </svg>
                                <svg x-show="locating" x-cloak class="w-4 h-4 shrink-0 animate-spin" fill="none"
                                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" d="M12 3a9 9 0 1 0 9 9" />
                                </svg>
                                <span x-text="locating ? 'LOCATING…' : 'USE MY LOCATION'"></span>
                            </button>

                            <p x-show="status" x-cloak x-text="status"
                                class="md:col-span-3 text-[9px] font-black uppercase italic tracking-widest ml-4"
                                :class="ok ? 'text-emerald-400' : 'text-amber-400'"></p>
                        </div>
                    </div>

                    <div
                        class="glass-card p-6 sm:p-10 space-y-10">
                        <div class="border-b border-slate-50 pb-6">
                            <h3 class="text-xl font-black italic uppercase italic text-white tracking-tight">
                                Financial & Temporal Sync</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Appointment
                                    Mode</label>
                                <select name="appointment_mode"
                                    class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium appearance-none cursor-pointer">
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
                                    class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Global
                                    Opening Time</label>
                                <input type="time" name="global_opening_time"
                                    value="{{ $vendor->global_opening_time ? \Carbon\Carbon::parse($vendor->global_opening_time)->format('H:i') : '' }}"
                                    class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium uppercase">
                            </div>
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Global
                                    Closing Time</label>
                                <input type="time" name="global_closing_time"
                                    value="{{ $vendor->global_closing_time ? \Carbon\Carbon::parse($vendor->global_closing_time)->format('H:i') : '' }}"
                                    class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium uppercase">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-12">
                    <!-- Establishment Visual Identification -->
                    <div class="glass-card p-6 sm:p-10">
                        <h3 class="text-xl font-black italic uppercase italic text-white tracking-tight mb-8">Visual
                            ID</h3>

                        <div
                            class="relative w-full aspect-square rounded-[2.5rem] overflow-hidden mb-8 bg-white/5 border border-white/10 flex items-center justify-center shadow-inner group">
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
                            class="btn-outline w-full h-14 justify-center">
                            <span x-text="photoPreview ? 'REPLACE VISUAL' : 'INITIATE VISUAL UPLOAD'"></span>
                        </button>
                    </div>

                    <div class="glass-card p-6 sm:p-10">
                        <h3 class="text-xl font-black italic uppercase italic text-white tracking-tight mb-2">Tier
                            Sync</h3>
                        <p class="text-[9px] font-black text-slate-300 uppercase italic tracking-[0.2em] mb-8">ACTIVE
                            OPERATIONAL SUB-LOGIC</p>

                        <div class="p-8 bg-slate-900 rounded-[2rem] text-white shadow-xl shadow-slate-900/20 mb-4">
                            <div class="text-[8px] text-white/40 uppercase font-black italic tracking-widest mb-1">
                                CURRENT RANK</div>
                            <div class="text-3xl font-black italic tracking-tighter">{{ $vendor->subscriptionPlan->name }}</div>
                        </div>

                        {{-- Subscription Expiry Block --}}
                        @php
                            $expiresAt = $vendor->subscription_expires_at;
                            $daysLeft  = $expiresAt ? (int) now()->diffInDays($expiresAt, false) : null;
                            if ($expiresAt === null) {
                                $expiryLabel  = 'Lifetime / No Expiry';
                                $expiryColor  = 'text-emerald-400';
                                $bgColor      = 'bg-emerald-500/10 border-emerald-500/20';
                                $dotColor     = 'bg-emerald-500';
                            } elseif ($daysLeft < 0) {
                                $expiryLabel  = 'Expired on ' . $expiresAt->format('d M Y');
                                $expiryColor  = 'text-red-400';
                                $bgColor      = 'bg-red-500/10 border-red-500/20';
                                $dotColor     = 'bg-red-500';
                            } elseif ($daysLeft <= 7) {
                                $expiryLabel  = 'Expires in ' . $daysLeft . ' day' . ($daysLeft === 1 ? '' : 's') . ' (' . $expiresAt->format('d M Y') . ')';
                                $expiryColor  = 'text-amber-400';
                                $bgColor      = 'bg-amber-500/10 border-amber-500/20';
                                $dotColor     = 'bg-amber-400 animate-pulse';
                            } else {
                                $expiryLabel  = $expiresAt->format('d M Y');
                                $expiryColor  = 'text-emerald-400';
                                $bgColor      = 'bg-emerald-500/10 border-emerald-500/20';
                                $dotColor     = 'bg-emerald-500';
                            }
                        @endphp
                        <div class="flex items-center gap-4 px-5 py-4 rounded-2xl border {{ $bgColor }} mb-6">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ $dotColor }}"></span>
                            <div>
                                <div class="text-[8px] font-black uppercase tracking-widest text-white/40 mb-0.5">
                                    PLAN EXPIRY
                                </div>
                                <div class="text-sm font-black italic {{ $expiryColor }}">
                                    {{ $expiryLabel }}
                                    @if($daysLeft !== null && $daysLeft >= 0)
                                        <span class="text-white/30 font-medium text-[10px] ml-1">({{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} left)</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <ul class="space-y-4 mb-10">
                            @foreach($vendor->subscriptionPlan->features as $feature)
                            <li
                                class="flex items-center gap-4 text-[10px] font-black text-slate-200 italic tracking-tight uppercase">
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
                            class="w-full h-16 border-2 border-white/10 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-white/5 transition-all italic text-white flex items-center justify-center">Upgrade
                            Rank</a>
                    </div>

                    <div class="pt-6">
                        <button type="submit"
                            class="btn-primary w-full h-14 justify-center text-lg gap-4 group">
                            Update
                            <svg class="w-6 h-6 transition-transform group-hover:translate-x-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-vendor-layout>