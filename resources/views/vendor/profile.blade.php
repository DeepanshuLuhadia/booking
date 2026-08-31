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

        {{-- Stage one: why an approved vendor is sitting on this page.

             EnsureSubscriptionActive holds every newly approved shop here until
             the fields below are filled in — the same ones the public listing,
             the slot generator and the booking flow all read. Without a line
             saying so, the redirect looks like a bug.

             $setupBlockers is this page's own half of the setup (business
             details + map pin). The specialist requirement is deliberately not
             in it: there is no staff section on this form, so listing it here
             asked for something the vendor could not do without leaving. It
             becomes the whole of the banner below once this stage is saved. --}}
        @if($profileIncomplete)
        <div class="mb-12 p-6 sm:p-8 rounded-[2rem] bg-orange-500/10 border border-orange-500/30">
            <div class="flex items-start gap-4">
                <span class="w-12 h-12 shrink-0 bg-orange-500/10 text-orange-400 rounded-2xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v3.75m0 3h.01M4.5 19.5h15L12 4.5l-7.5 15z"/></svg>
                </span>
                <div class="min-w-0">
                    <h2 class="text-lg sm:text-xl font-black italic uppercase tracking-tight text-white mb-2">
                        Your Account Is Approved — Finish Your Setup
                    </h2>
                    <p class="text-xs sm:text-sm font-medium text-white/70 leading-relaxed mb-4">
                        Complete the details below and save them to unlock your dashboard. Customers cannot find or
                        book your business until this is done.
                    </p>
                    {{-- Each chip is the shortcut to its own field: clicking
                         scrolls to and focuses the exact input (the #field-*
                         handler in app-layout), and the chip disappears the
                         moment that input is filled in — so what is left in
                         this list is always exactly what is left to do.

                         Every chip here belongs to an input on this page, which
                         is the point of the split: all of them can be cleared
                         without leaving the form. --}}
                    <ul id="setup-chips" class="flex flex-wrap gap-2">
                        @foreach($setupBlockers as $blocker)
                            @php
                                // The map is one chip over two inputs; everything
                                // else watches the single field it names.
                                $chipWatch = $blocker['field'] === 'map'
                                    ? 'latitude,longitude'
                                    : $blocker['field'];
                            @endphp
                            <li data-watch="{{ $chipWatch }}">
                                <a href="#field-{{ $blocker['field'] }}"
                                   class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-[10px] font-black uppercase tracking-widest text-white/70 hover:text-white hover:bg-white/10 transition-all">
                                    {{ $blocker['label'] }}
                                    <svg class="w-3 h-3 shrink-0 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <script>
                        /*
                        | Live chip hiding.
                        |
                        | Each chip watches the form inputs named in data-watch
                        | and hides itself once ALL of them hold a real value
                        | (for the map pair, a coordinate the listing would not
                        | treat as unset). Display only — the saved truth is
                        | still recomputed server-side on submit, so a chip
                        | hidden here and then emptied again simply comes back
                        | on the next page load.
                        */
                        (function () {
                            var chips = document.querySelectorAll('#setup-chips [data-watch]');
                            if (!chips.length) return;

                            var filled = function (name) {
                                var el = document.querySelector('[name="' + name + '"]');
                                if (!el) return false;
                                var v = (el.value || '').trim();
                                if (name === 'latitude' || name === 'longitude') {
                                    return v !== '' && !isNaN(v) && Math.abs(parseFloat(v)) >= 0.00001;
                                }
                                return v !== '';
                            };

                            var refresh = function () {
                                chips.forEach(function (chip) {
                                    var done = chip.dataset.watch.split(',').every(filled);
                                    chip.style.display = done ? 'none' : '';
                                });
                            };

                            // input covers typing; change covers selects, the
                            // time pickers and "Use My Location" filling the
                            // coordinate boxes programmatically.
                            document.addEventListener('input', refresh, true);
                            document.addEventListener('change', refresh, true);
                            refresh();
                        })();
                    </script>
                </div>
            </div>
        </div>
        @endif

        {{-- Stage two: the settings are saved, and one thing is left.

             This is the banner the vendor meets on the reload after their first
             successful save. It replaces the checklist above rather than sitting
             alongside it — the point of staging the setup is that the panel asks
             for one thing at a time.

             The modal at the foot of this page makes the same request the moment
             the save lands; this is the standing blocker that outlives dismissing
             it, so the requirement cannot be closed away and forgotten. Both
             point at the staff section, because that is where it can be done.

             Wider than the modal on purpose: a shop whose only specialist is off
             duty or unpriced still has nothing bookable, and still belongs here.
             The link says "add" only when there is genuinely nobody yet. --}}
        @if($employeeBlocker)
        <div class="mb-12 p-6 sm:p-8 rounded-[2rem] bg-orange-500/10 border border-orange-500/30">
            <div class="flex items-start gap-4">
                <span class="w-12 h-12 shrink-0 bg-orange-500/10 text-orange-400 rounded-2xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v3.75m0 3h.01M4.5 19.5h15L12 4.5l-7.5 15z"/></svg>
                </span>
                <div class="min-w-0">
                    <h2 class="text-lg sm:text-xl font-black italic uppercase tracking-tight text-white mb-2">
                        Business Details Saved — One Step Left
                    </h2>
                    <p class="text-xs sm:text-sm font-medium text-white/70 leading-relaxed mb-4">
                        Customers book a <span class="text-white font-bold">person</span>, not a shop. You need at least
                        one active specialist with their working hours and service fee — until then there are no slots to
                        book and your business stays hidden from the listing.
                    </p>
                    <a href="{{ $needsFirstEmployee ? route('vendor.employees.create') : route('vendor.employees.index') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-[10px] font-black uppercase tracking-widest text-white/70 hover:text-white hover:bg-white/10 transition-all">
                        {{ $needsFirstEmployee ? 'Add Your First Specialist' : 'Finish Your Specialist Setup' }}
                        <svg class="w-3 h-3 shrink-0 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
        @endif

        <form action="{{ route('vendor.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <div class="lg:col-span-2 space-y-12">
                    <div
                        class="glass-card p-6 sm:p-10 space-y-10">
                        <div class="border-b border-slate-50 pb-6">
                            <h3 class="text-xl font-black italic uppercase italic text-white tracking-tight">
                                Business Information</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Business
                                    Category</label>
                                @php
                                    // Same source as the register form: categories from the
                                    // vendor_categories table, labels/emojis from ThemeService.
                                    $categoryOptions = $vendorCategories->mapWithKeys(function ($category) {
                                        $themeConfig = \App\Services\ThemeService::getTheme($category->slug);
                                        return [$category->slug => [
                                            'label' => $themeConfig['label'] ?? $category->name,
                                            'icon'  => $themeConfig['emoji'] ?? '✨',
                                        ]];
                                    });
                                @endphp
                                <div id="field-vendor_type" class="relative field-anchor" x-data="{
                                    open: false,
                                    selected: '{{ $vendor->vendor_type ?? 'doctor' }}',
                                    options: {{ Js::from($categoryOptions) }},
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
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Business
                                    Name</label>
                                <input type="text" name="business_name" value="{{ $vendor->business_name }}" required
                                    class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Owner
                                    Name</label>
                                <input type="text" name="owner_name" value="{{ $vendor->owner_name }}" required
                                    class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium">
                            </div>

                            <div class="space-y-4 relative">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Mobile Number</label>
                                <input type="text" id="field-contact_number" name="contact_number" value="{{ $vendor->contact_number }}" required
                                    class="field-anchor glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium">

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
                                Coordinates / Address <span class="text-slate-400 normal-case">(optional)</span></label>
                            <textarea name="address" rows="3"
                                placeholder="Optional if you set the map location below"
                                class="glass-input w-full rounded-2xl p-4 font-medium">{{ $vendor->address }}</textarea>
                            <p class="text-[8px] font-black text-slate-200 uppercase tracking-widest ml-4 mt-1 italic">
                                Optional — the map location below is what customers are actually sent to.
                                Leave this blank and your page shows a "Go to Map" link instead.
                            </p>
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
                                            // A programmatic .value writes no event, and the setup
                                            // banner's chips only re-check on input/change — announce
                                            // the fill so the 'Shop location on map' chip clears.
                                            this.$refs.lat.dispatchEvent(new Event('change', { bubbles: true }));
                                            this.$refs.lng.dispatchEvent(new Event('change', { bubbles: true }));
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
                                    Reference <span class="text-amber-400">*</span></label>
                                <input type="text" id="field-map" name="latitude" x-ref="lat" value="{{ old('latitude', $vendor->latitude) }}"
                                    required inputmode="decimal" placeholder="e.g. 26.9124000"
                                    class="field-anchor glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium">
                                @error('latitude')
                                    <p class="text-[9px] font-black uppercase italic tracking-widest ml-4 text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Longitude
                                    Reference <span class="text-amber-400">*</span></label>
                                <input type="text" name="longitude" x-ref="lng" value="{{ old('longitude', $vendor->longitude) }}"
                                    required inputmode="decimal" placeholder="e.g. 75.7873000"
                                    class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium">
                                @error('longitude')
                                    <p class="text-[9px] font-black uppercase italic tracking-widest ml-4 text-rose-400">{{ $message }}</p>
                                @enderror
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

                            {{-- Says why the pair is compulsory. Customers are ranked
                                 nearest-first from their own GPS, so a shop without
                                 coordinates cannot be placed on that list at all. --}}
                            <p class="md:col-span-3 text-[9px] font-black uppercase italic tracking-widest ml-4 text-slate-400">
                                Required — customers are shown the nearest shops first, measured from these coordinates.
                            </p>
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
                                <select id="field-appointment_mode" name="appointment_mode"
                                    class="field-anchor glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium appearance-none cursor-pointer">
                                    <option value="time_slot" {{ $vendor->appointment_mode == 'time_slot' ? 'selected' :
                                        '' }}>Time Slot System</option>
                                    <option value="token" {{ $vendor->appointment_mode == 'token' ? 'selected' : ''
                                        }}>Token System</option>
                                </select>
                            </div>
                            {{-- The UPI ID field used to sit here as a loose
                                 "settlement link". It now lives in the Direct
                                 UPI Advance card below, alongside the payee
                                 name and the fee it is collected with — a
                                 second input of the same name in this form
                                 would silently overwrite that one. --}}
                        </div>

                        {{-- Booking intake.

                             Shop-wide by design: it governs every employee of
                             this business at once, so a customer scanning any
                             specialist's QR code meets the same flow.

                             The hidden 0 is what makes unticking the box mean
                             something — an unchecked checkbox posts nothing at
                             all, which would otherwise be indistinguishable
                             from a form that never carried the field. --}}
                        <div class="space-y-4">
                            <label
                                class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Booking
                                Intake</label>
                            <label
                                class="flex items-start gap-4 p-5 rounded-2xl bg-white/5 border border-white/10 cursor-pointer transition-all hover:bg-white/[0.07]">
                                <input type="hidden" name="require_customer_details" value="0">
                                <input type="checkbox" name="require_customer_details" value="1"
                                    {{ $vendor->require_customer_details ? 'checked' : '' }}
                                    class="w-5 h-5 mt-0.5 shrink-0 bg-white/10 border-none rounded text-blue-600 focus:ring-2 focus:ring-blue-50 transition-all cursor-pointer">
                                <span>
                                    <span
                                        class="block text-[10px] font-black text-white uppercase italic tracking-widest">Ask
                                        for customer details before booking</span>
                                    <span class="block text-[10px] font-medium text-slate-400 mt-1.5 leading-relaxed">
                                        On — the customer enters their name, phone number and (optionally) email
                                        before the appointment is confirmed.
                                        Off — scanning your QR code and tapping "book" creates the appointment
                                        straight away, with no form. Applies to every employee of this business.
                                    </span>
                                </span>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Global
                                    Opening Time</label>
                                <input type="time" id="field-global_opening_time" name="global_opening_time"
                                    value="{{ $vendor->global_opening_time ? \Carbon\Carbon::parse($vendor->global_opening_time)->format('H:i') : '' }}"
                                    class="field-anchor glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium uppercase">
                            </div>
                            <div class="space-y-4">
                                <label
                                    class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Global
                                    Closing Time</label>
                                <input type="time" id="field-global_closing_time" name="global_closing_time"
                                    value="{{ $vendor->global_closing_time ? \Carbon\Carbon::parse($vendor->global_closing_time)->format('H:i') : '' }}"
                                    class="field-anchor glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium uppercase">
                            </div>
                        </div>
                    </div>

                    {{-- Direct-to-vendor UPI advances. Its own card because it
                         is the one block of settings that puts a customer in
                         front of a payment screen, and it carries its own
                         warnings and live QR preview. --}}
                    @include('vendor.partials.direct-upi-settings')
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

        {{-- Login & Security.

             Its own <form>, outside the profile one — a form cannot be nested,
             and a password must never ride along with a multipart post of
             business details anyway.

             This is the vendor's half of the two-way login: a shop that signed
             up with Google sets its first password here (nothing to prove — the
             random one on the row was never shown to anybody), and can from then
             on sign in either way. Everyone else has to prove the current
             password first, so a borrowed session cannot take the account over.
        --}}
        <div class="mt-12 glass-card p-6 sm:p-10" x-data="{ showPasswordForm: {{ $errors->has('password') || $errors->has('current_password') ? 'true' : 'false' }} }">
            <div class="border-b border-slate-50 pb-6 mb-8">
                <h3 class="text-xl font-black italic uppercase text-white tracking-tight">Login &amp; Security</h3>
                <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] mt-2 italic">HOW YOU SIGN IN TO THIS PANEL</p>
            </div>

            {{-- What is switched on today, so the vendor can see what setting a
                 password would actually buy them. --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                <div class="flex items-center gap-4 p-5 rounded-2xl bg-white/5 border border-white/10">
                    <span class="w-11 h-11 shrink-0 rounded-xl flex items-center justify-center {{ $user->usesGoogleSignIn() ? 'bg-emerald-500/10 text-emerald-400' : 'bg-white/5 text-white/30' }}">
                        <svg class="w-5 h-5" viewBox="0 0 48 48" aria-hidden="true"><path fill="currentColor" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.1 8 3l5.7-5.7C34.5 6.1 29.5 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.6-.4-3.9z"/></svg>
                    </span>
                    <div class="min-w-0">
                        <div class="text-[9px] font-black uppercase tracking-widest text-slate-300 italic">Google Sign-In</div>
                        <div class="text-sm font-black text-white truncate">
                            {{ $user->usesGoogleSignIn() ? 'Enabled' : 'Not linked' }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 p-5 rounded-2xl bg-white/5 border border-white/10">
                    <span class="w-11 h-11 shrink-0 rounded-xl flex items-center justify-center {{ $user->hasPassword() ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </span>
                    <div class="min-w-0">
                        <div class="text-[9px] font-black uppercase tracking-widest text-slate-300 italic">Email &amp; Password</div>
                        <div class="text-sm font-black text-white truncate">
                            {{ $user->hasPassword() ? 'Enabled' : 'Not set yet' }}
                        </div>
                    </div>
                </div>
            </div>

            @unless($user->hasPassword())
            <div class="mb-8 p-5 rounded-2xl bg-amber-500/10 border border-amber-500/20">
                <p class="text-xs sm:text-sm font-bold text-amber-300 leading-relaxed">
                    You signed up with Google, so this account has no password yet. Set one below and you'll be able to
                    sign in <span class="text-white">either</span> with Google <span class="text-white">or</span> with
                    <span class="text-white">{{ $user->email }}</span> and your new password.
                </p>
            </div>
            @endunless

            <button type="button" x-show="!showPasswordForm" @click="showPasswordForm = true"
                class="btn-outline h-14 px-8 justify-center">
                {{ $user->hasPassword() ? 'Change Password' : 'Set A Password' }}
            </button>

            <form x-show="showPasswordForm" x-cloak method="POST" action="{{ route('vendor.profile.password') }}"
                  class="space-y-8 max-w-xl">
                @csrf

                @if($user->hasPassword())
                <div class="space-y-4">
                    <label class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Current Password</label>
                    <input type="password" name="current_password" required autocomplete="current-password"
                        class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl font-bold text-base text-white placeholder:text-white/30"
                        placeholder="••••••••">
                    @error('current_password')
                        <p class="text-rose-400 text-[10px] font-black uppercase tracking-widest ml-4">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                <div class="space-y-4">
                    <label class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">New Password</label>
                    <input type="password" name="password" required minlength="8" autocomplete="new-password"
                        class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl font-bold text-base text-white placeholder:text-white/30"
                        placeholder="At least 8 characters">
                    @error('password')
                        <p class="text-rose-400 text-[10px] font-black uppercase tracking-widest ml-4">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-4">
                    <label class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"
                        class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl font-bold text-base text-white placeholder:text-white/30"
                        placeholder="Re-enter the new password">
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <button type="submit" class="btn-primary h-14 px-10 justify-center">
                        {{ $user->hasPassword() ? 'Update Password' : 'Save Password' }}
                    </button>
                    <button type="button" @click="showPasswordForm = false"
                        class="h-14 px-8 rounded-2xl border-2 border-white/10 text-[10px] font-black uppercase tracking-widest text-white/50 hover:text-white hover:bg-white/5 transition-all italic">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- The step straight after the business details: at least one specialist.

         A shop with no staff has no working hours, no fee and no slots, so
         nothing on it can be booked — it is the last thing standing between an
         approved vendor and a listing customers can actually use. Shown on
         every visit to this page (including the reload after a successful save)
         until a specialist exists, which is exactly when it stops mattering.

         Outside the settings <div> so the profile form's Alpine scope has
         nothing to do with it. --}}
    @if($needsFirstEmployee)
    <div x-data="{ showEmployeePrompt: true }"
         x-show="showEmployeePrompt"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="app-modal">

        <div @click="showEmployeePrompt = false" class="app-modal__backdrop bg-slate-900/70 backdrop-blur-xl"></div>

        <div class="app-modal__panel custom-scrollbar max-w-lg border border-white/10 rounded-[2.5rem] p-6 sm:p-8 shadow-2xl text-white" style="background-color:#0a0f2c;">
            <button @click="showEmployeePrompt = false" class="absolute top-5 right-5 w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-white/50 hover:text-rose-500 hover:bg-rose-500/10 transition-all border border-white/5">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="flex items-center gap-3 mb-2 pr-10">
                <span class="w-8 h-1 bg-orange-500 rounded-full"></span>
                <span class="text-orange-500 font-black text-[9px] uppercase tracking-widest italic">One Last Step</span>
            </div>

            <h3 class="text-2xl sm:text-3xl font-black italic tracking-tighter uppercase mb-3">
                Add At Least <span class="text-orange-500">One Employee.</span>
            </h3>
            <p class="text-xs sm:text-sm font-medium text-white/70 leading-relaxed mb-6">
                Your business details are saved. Customers book a <span class="text-white font-bold">person</span>, not
                a shop — so until you add one specialist with their working hours and service fee, there are no slots to
                book and your business stays hidden from the listing.
            </p>

            {{-- Each row opens the employee form at exactly that field —
                 same #field-* anchors as the settings banner. --}}
            <ul class="space-y-2.5 mb-7">
                @foreach([
                    'Name of the specialist'    => 'name',
                    'Working start & end time'  => 'working_start_time',
                    'Service fee'               => 'service_fee_override',
                    'Slot duration'             => 'slot_duration',
                ] as $needed => $field)
                <li>
                    <a href="{{ route('vendor.employees.create') }}#field-{{ $field }}"
                       class="flex items-center gap-3 p-3 bg-white/5 border border-white/10 rounded-xl hover:bg-white/10 transition-all group">
                        <span class="w-7 h-7 shrink-0 bg-orange-500/10 text-orange-400 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <span class="flex-1 text-xs sm:text-sm font-bold text-white/90">{{ $needed }}</span>
                        <svg class="w-4 h-4 shrink-0 text-white/20 group-hover:text-orange-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </li>
                @endforeach
            </ul>

            <div class="space-y-3">
                <a href="{{ route('vendor.employees.create') }}"
                   class="w-full h-14 rounded-xl bg-gradient-to-r from-orange-500 to-amber-400 text-slate-900 font-black uppercase tracking-widest text-xs flex items-center justify-center transition-all hover:opacity-90">
                    Add Employee Now
                </a>
                <button @click="showEmployeePrompt = false"
                        class="w-full h-12 rounded-xl bg-white/5 border border-white/10 text-white/40 hover:text-white/70 font-black uppercase tracking-widest text-[10px] flex items-center justify-center transition-all">
                    I'll Do It Later
                </button>
            </div>
        </div>
    </div>
    @endif
</x-vendor-layout>