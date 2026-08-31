<x-app-layout page-title="Vendor Onboarding | Appointment Platform">
    <div class="relative min-h-screen pb-32 overflow-hidden" style="background: linear-gradient(180deg,#0a0f2c 0%,#0d1333 100%);">
        <!-- Glowing Orbs (From Index) -->
        <div style="position:absolute; top:0; left:25%; width:500px; height:500px; background:rgba(255,109,0,.08); border-radius:50%; filter:blur(120px); pointer-events:none;"></div>
        <div style="position:absolute; bottom:0; right:25%; width:600px; height:600px; background:rgba(255,109,0,.04); border-radius:50%; filter:blur(150px); pointer-events:none;"></div>
        <!-- Subtle Institutional Pattern -->
        <div class="absolute inset-0 z-0 bg-dot-pattern opacity-30"></div>

        <div class="reg-wrap relative z-10 max-w-5xl mx-auto px-6 pt-24">
            <!-- Header Section -->
            <div class="text-center mb-12 md:mb-20 animate-text-reveal">
                {{-- <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-slate-900 border border-slate-800 rounded-full text-white text-[9px] font-black uppercase tracking-widest mb-6 md:mb-8">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Enterprise Onboarding Protocol
                </div> --}}
                <h1
                    class="text-4xl sm:text-5xl md:text-6xl lg:text-[5.5rem] font-black text-white mb-6 tracking-tighter leading-[1.1] md:leading-[0.9] italic">
                    Take Your Business <span style="color:transparent; background:linear-gradient(135deg,#ff8c42,#ffab40); -webkit-background-clip:text; background-clip:text;">Digital.</span>
                </h1>
                <p class="text-base md:text-xl font-medium text-white/50 max-w-xl mx-auto italic leading-relaxed px-4">Create your business profile and start managing appointments online.</p>
            </div>

            <style>
                .plan-card-input:checked + .plan-card-content {
                    background-color: rgba(255, 109, 0, 0.08) !important;
                    border-color: #ffab40 !important;
                    box-shadow: 0 0 40px rgba(255, 109, 0, 0.25), inset 0 0 20px rgba(255, 109, 0, 0.05) !important;
                    transform: translateY(-8px) scale(1.02) !important;
                }
                .plan-card-input:checked + .plan-card-content .pricing-cycle {
                    color: rgba(255, 255, 255, 0.7) !important;
                }
                .plan-card-input:checked + .plan-card-content .check-icon {
                    opacity: 1 !important;
                    transform: scale(1.1) !important;
                    background: linear-gradient(135deg, #ff6d00, #ffab40) !important;
                    box-shadow: 0 6px 16px rgba(255, 109, 0, 0.4) !important;
                }
                .plan-card-input:checked + .plan-card-content h4,
                .plan-card-input:checked + .plan-card-content span {
                    color: #ffffff !important;
                }
                .bottom-space {
                    margin-bottom: 20px !important;
                }

                /* ── Mobile refinements (≤600px). Tighter rhythm + smaller radii so the
                      long form reads comfortably on a phone. Desktop is untouched. ── */
                @media (max-width: 600px) {
                    .reg-wrap {
                        padding-top: 80px !important;
                        padding-left: 16px !important;
                        padding-right: 16px !important;
                    }
                    /* space-y-16 sets 4rem between the cards — halve it on mobile. */
                    .reg-form > :not([hidden]) ~ :not([hidden]) {
                        margin-top: 2.25rem !important;
                    }
                    .reg-card-inner {
                        padding: 22px !important;
                        border-radius: 26px !important;
                    }
                    /* gap-y-10 (2.5rem) between stacked fields is too airy on mobile. */
                    .reg-field-grid {
                        row-gap: 1.5rem !important;
                    }
                    /* gap-8 (2rem) between stacked plan cards -> tighter. */
                    .reg-plan-grid {
                        gap: 16px !important;
                    }
                    .plan-card-content {
                        padding: 22px !important;
                        border-radius: 24px !important;
                    }
                    /* Keep the selected plan's lift subtle so it doesn't overflow. */
                    .plan-card-input:checked + .plan-card-content {
                        transform: translateY(-4px) scale(1.0) !important;
                    }
                }
            </style>

            @php
                // Google sign-up is optional: with no client id configured the
                // tab is not offered and this page is the form it has always
                // been. The modal starts on the first category for the same
                // reason the form's <select> does.
                $googleClientId  = config('services.google.client_id');
                $defaultCategory = $vendorCategories->first()?->slug;
            @endphp

            {{-- The Alpine component only exists when Google does — see the
                 same note on the login page. --}}
            <div @if($googleClientId)
                 x-data="vendorGoogleAuth({
                    mode: 'register',
                    clientId: @js($googleClientId),
                    endpoint: @js(route('auth.google.vendor.register')),
                    defaultCategory: @js($defaultCategory),
                    referral: @js(request('ref')),
                 })"
                 @endif>

            @if($googleClientId)
            <div class="max-w-xl mx-auto mb-12 md:mb-16">
                <div class="flex gap-2 p-2 bg-white/5 border border-white/10 rounded-2xl">
                    <button type="button" @click="tab = 'email'"
                        class="flex-1 h-12 rounded-xl text-[9px] md:text-[10px] font-black uppercase tracking-widest transition-all"
                        :class="tab === 'email' ? 'bg-white/15 text-white shadow-lg' : 'text-white/40 hover:text-white/70'">
                        Register With Email
                    </button>
                    <button type="button" @click="tab = 'google'"
                        class="flex-1 h-12 rounded-xl text-[9px] md:text-[10px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2"
                        :class="tab === 'google' ? 'bg-white/15 text-white shadow-lg' : 'text-white/40 hover:text-white/70'">
                        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.1 8 3l5.7-5.7C34.5 6.1 29.5 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.6-.4-3.9z"/><path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.1 8 3l5.7-5.7C34.5 6.1 29.5 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.4 0 10.3-2.1 14-5.4l-6.2-5.2C29.9 34.9 27.1 36 24 36c-5.2 0-9.6-3.3-11.3-7.9l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.3-2.3 4.2-4.2 5.5l6.2 5.2C36.9 40.2 44 35 44 24c0-1.3-.1-2.6-.4-3.9z"/></svg>
                        Sign Up With Google
                    </button>
                </div>
            </div>
            @endif

            <form @if($googleClientId) x-show="tab === 'email'" @endif
                  method="POST" action="/register/vendor" class="reg-form space-y-16 animate-reveal delay-100">
                @csrf

                <!-- STEP 1: IDENTITY -->
                <div class="glass-card overflow-hidden shadow-2xl">
                    <div class="reg-card-inner p-6 md:p-14 rounded-[3rem]">
                        <div class="flex items-center gap-4 md:gap-6 mb-8 md:mb-12">
                            <div class="w-12 h-12 md:w-14 md:h-14 bg-white/10 text-white border border-white/20 rounded-2xl flex items-center justify-center text-lg md:text-xl font-black italic shadow-xl shrink-0">01</div>
                            <div>
                                <h3 class="text-2xl md:text-3xl font-black italic tracking-tight uppercase text-white ">Business Information</h3>
                                <p class="text-[10px] md:text-xs font-black uppercase tracking-[0.2em] text-white/40">Enter your business and contact details</p>
                            </div>
                        </div>

                        <div class="reg-field-grid grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10">
                            <!-- Category -->
                            <div class="space-y-2 group">
                                <label class="text-[9px] font-black uppercase tracking-[0.2em] text-white/60 ml-6">Business Category</label>
                                {{-- A plain <select>: the shared dropdown in
                                     partials/custom-select turns it into the same
                                     control used everywhere else on the site,
                                     including the mobile bottom sheet. --}}
                                <select name="vendor_type" required
                                    class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl font-bold text-base text-white">
                                    @foreach($vendorCategories as $category)
                                        @php $themeConfig = \App\Services\ThemeService::getTheme($category->slug); @endphp
                                        <option value="{{ $category->slug }}" class="bg-[#0d1333]" @selected(old('vendor_type') === $category->slug)>
                                            {{ ($themeConfig['emoji'] ?? '✨') . ' ' . ($themeConfig['label'] ?? $category->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Business Name -->
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-6">Business Name</label>
                                <input type="text" name="business_name" value="{{ old('business_name') }}" required
                                    class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base text-white placeholder:text-white/30 transition-all focus:bg-white/10"
                                    placeholder="Enter Business Name">
                            </div>

                            <!-- Owner Name -->
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-6">Owner Name</label>
                                <input type="text" name="owner_name" value="{{ old('owner_name') }}" required
                                    class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base text-white placeholder:text-white/30 transition-all focus:bg-white/10"
                                    placeholder="Enter Owner Full Name">
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-6">Email Address</label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                    class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base text-white placeholder:text-white/30 transition-all focus:bg-white/10"
                                    placeholder="Enter Email Address">
                            </div>

                            <!-- Mobile -->
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-6">Mobile Number</label>
                                <input type="tel" name="mobile" value="{{ old('mobile') }}" required maxlength="10"
                                    class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base text-white placeholder:text-white/30 transition-all focus:bg-white/10"
                                    placeholder="Enter 10-digit mobile number">
                            </div>

                            <!-- Referral -->
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-6">Referral Code</label>
                                <input type="text" name="referral_code" value="{{ old('referral_code', request('ref')) }}"
                                    class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base text-white placeholder:text-white/30 transition-all focus:bg-white/10"
                                    placeholder="Enter Referral Code (Optional)">
                            </div>

                            {{-- Password fields carry a show/hide eye. `pr-14`
                                 rather than `px-6` on the right: the toggle sits
                                 inside the field, and without the extra room a
                                 long password types straight under it. --}}
                            <!-- Password -->
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-6">Password</label>
                                <x-password-input name="password" required autocomplete="new-password"
                                    class="pw-field premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base text-white placeholder:text-white/30 transition-all focus:bg-white/10"
                                    placeholder="Create Password" />
                            </div>

                            <!-- Confirm -->
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-6">Confirm Password</label>
                                <x-password-input name="password_confirmation" required autocomplete="new-password"
                                    toggle-label="confirmed password"
                                    class="pw-field premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base text-white placeholder:text-white/30 transition-all focus:bg-white/10"
                                    placeholder="Re-Enter Your Password" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: PLAN -->
                <div class="glass-card overflow-hidden shadow-2xl bottom-space">
                    <div class="reg-card-inner p-6 md:p-14 rounded-[3rem]">
                        <div class="flex items-center gap-4 md:gap-6 mb-8 md:mb-12">
                            <div class="w-12 h-12 md:w-14 md:h-14 bg-white/10 text-white border border-white/20 rounded-2xl flex items-center justify-center text-lg md:text-xl font-black italic shadow-xl shrink-0">02</div>
                            <div>
                                <h3 class="text-2xl md:text-3xl font-black italic tracking-tight uppercase text-white">Choose Your Plan</h3>
                                <p class="text-[10px] md:text-[9px] font-black uppercase tracking-[0.2em] text-white/40">Choose a plan that fits your business</p>
                            </div>
                        </div>

                        <div class="reg-plan-grid grid grid-cols-1 md:grid-cols-3 gap-8">
                            @foreach($plans as $plan)
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="subscription_plan_id" value="{{ $plan->id }}"
                                    class="peer sr-only plan-card-input" {{ $loop->first ? 'checked' : '' }}>
                                <div class="plan-card-content h-full p-8 bg-white/5 border border-white/10 rounded-[2.5rem] transition-all duration-300 text-white group-hover:translate-y-[-4px] group-hover:shadow-xl antialiased focus-within:ring-4 focus-within:ring-theme-primary/10 hover:bg-white/10">
                                    <h4 class="text-xl font-black mb-2 italic">{{ $plan->name }}</h4>
                                    <div class="flex items-baseline gap-1 mb-6">
                                        @if($plan->price == 0)
                                            <span class="text-4xl font-black tracking-tighter italic">FREE</span>
                                            <span class="pricing-cycle text-[9px] font-black uppercase tracking-widest text-white/40">/ 1 MONTH TRIAL</span>
                                        @else
                                            <span class="text-4xl font-black tracking-tighter italic">₹{{ number_format($plan->price) }}</span>
                                            <span class="pricing-cycle text-[9px] font-black uppercase tracking-widest text-white/40">/ YEAR</span>
                                        @endif
                                    </div>

                                    <div class="space-y-4 mb-4">
                                        {{-- Feature list comes from the admin panel (Plans →
                                             features[]), same source the admin pages render.
                                             The two staple lines below are only a fallback for
                                             a plan saved with no features at all. --}}
                                        @forelse(array_filter($plan->features ?? []) as $feature)
                                            <div class="flex items-center gap-3">
                                                <svg class="w-4 h-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" /></svg>
                                                <span class="text-[10px] font-black uppercase tracking-widest">{{ $feature }}</span>
                                            </div>
                                        @empty
                                            <div class="flex items-center gap-3">
                                                <svg class="w-4 h-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" /></svg>
                                                <span class="text-[10px] font-black uppercase tracking-widest">Up to {{ $plan->max_employees }} Staff Members</span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <svg class="w-4 h-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" /></svg>
                                                <span class="text-[10px] font-black uppercase tracking-widest">Online Appointment Booking</span>
                                            </div>
                                        @endforelse
                                    </div>

                                    <div class="check-icon absolute top-8 right-8 w-10 h-10 rounded-xl bg-theme-primary text-white flex items-center justify-center opacity-0 transition-all transform scale-90 shadow-xl shadow-theme-primary/10">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- SUBMIT -->
                <div class="flex flex-col md:flex-row items-start justify-between gap-8 md:gap-12 pt-0">
                    {{-- Consent is a hard gate, not a footnote: the box has to be
                         ticked before the form will submit, and the server checks it
                         again so a stripped-out attribute cannot get past it. --}}
                    <div class="max-w-lg px-2 space-y-2">
                        <label for="terms" class="flex items-start gap-3 cursor-pointer text-white/70 font-medium italic text-sm text-left">
                            <input type="checkbox"
                                   name="terms"
                                   id="terms"
                                   value="1"
                                   required
                                   @checked(old('terms'))
                                   class="mt-0.5 w-5 h-5 shrink-0 cursor-pointer rounded-md border-2 border-white/30 bg-white/10 text-emerald-500 focus:ring-2 focus:ring-emerald-400/60 focus:ring-offset-0">
                            <span>
                                I have read and agree to the
                                <a href="{{ route('terms') }}" target="_blank" rel="noopener" class="underline decoration-white/30 underline-offset-4 hover:text-white" @click.stop>Terms and Conditions</a>
                                and the
                                <a href="{{ route('privacy') }}" target="_blank" rel="noopener" class="underline decoration-white/30 underline-offset-4 hover:text-white" @click.stop>Privacy Policy</a>.
                            </span>
                        </label>
                        @error('terms')
                            <p class="text-rose-300 font-bold text-xs italic ml-8">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="btn-premium mx-auto w-full md:w-auto px-10 md:px-8 !rounded-2xl md:!rounded-[2rem] !text-lg md:!text-xl">
                        Create Business Account
                        <svg class="w-4 h-4 md:w-4 md:h-4 transition-transform group-hover:translate-x-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>

            @if($googleClientId)
            {{-- Google sign-up panel.

                 Deliberately short. Google hands over a confirmed email, a name
                 and a picture; the one thing it cannot give us is a phone
                 number, and that is the detail an admin needs to call the
                 business back. So the button collects the identity and the
                 modal below collects the phone. Everything else about the shop
                 is asked for on the settings screen the moment the account is
                 approved. --}}
            <div x-show="tab === 'google'" x-cloak class="max-w-xl mx-auto glass-card overflow-hidden shadow-2xl">
                <div class="reg-card-inner p-6 md:p-12 rounded-[3rem] space-y-8">

                    <div class="text-center space-y-3">
                        <h3 class="text-2xl md:text-3xl font-black italic tracking-tight uppercase text-white">One-Tap Signup</h3>
                        <p class="text-sm font-medium text-white/50 italic leading-relaxed">
                            Register with your Google account. We'll ask for your mobile number,
                            then our admin team reviews your business.
                        </p>
                    </div>

                    <div x-show="!busy" class="flex justify-center min-h-[44px]">
                        <div x-ref="googleBtn"></div>
                    </div>

                    <div x-show="busy" x-cloak class="flex items-center justify-center gap-3 h-11">
                        <svg class="w-5 h-5 animate-spin text-white/60" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"/></svg>
                        <span class="text-[10px] font-black uppercase tracking-widest text-white/60">Creating your account…</span>
                    </div>

                    {{-- Errors raised before the modal opens (Google itself
                         refusing). Anything raised while the modal is open is
                         shown inside it instead, next to the fields. --}}
                    <div x-show="error && !showDetails" x-cloak class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20">
                        <p class="text-xs font-bold text-rose-300 leading-relaxed" x-text="error"></p>
                    </div>

                    <div class="pt-6 border-t border-white/10 space-y-3">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-white/30 text-center leading-loose">
                            Starts on the free trial — upgrade any time from Settings.
                        </p>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-white/30 text-center leading-loose">
                            Already registered?
                            <a href="{{ route('login') }}" class="text-white/70 hover:text-white ml-1 underline decoration-2 underline-offset-4 italic">Sign in</a>
                        </p>
                    </div>
                </div>
            </div>

            {{-- "One last thing" — the phone number, asked for before the
                 account is created rather than after, because an admin cannot
                 approve a business they have no way of calling. --}}
            <div x-show="showDetails" x-cloak
                 class="app-modal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                <div class="app-modal__backdrop" style="background: rgba(10, 15, 44, 0.95); backdrop-filter: blur(12px);"></div>

                <form @submit.prevent="submit()"
                      class="app-modal__panel max-w-lg border border-white/10 rounded-[2rem] p-6 sm:p-8 shadow-2xl space-y-6"
                      style="background-color:#0a0f2c;">

                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-1 bg-orange-500 rounded-full"></span>
                            <span class="text-orange-400 font-black text-[9px] uppercase tracking-widest italic">Almost There</span>
                        </div>
                        <h3 class="text-2xl font-black italic tracking-tight uppercase text-white">Your Business Details</h3>
                    </div>

                    {{-- Which Google account this is being created for. --}}
                    <template x-if="account">
                        <div class="flex items-center gap-3 rounded-2xl border border-sky-500/30 bg-sky-500/5 p-3">
                            <img :src="account?.picture" x-show="account?.picture" referrerpolicy="no-referrer" class="w-10 h-10 rounded-full border border-white/10 shrink-0" alt="">
                            <div class="min-w-0">
                                <span class="block text-sm font-black text-white truncate" x-text="account?.name"></span>
                                <span class="block text-[10px] font-black uppercase tracking-widest text-sky-400 truncate" x-text="account?.email"></span>
                            </div>
                        </div>
                    </template>

                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-[0.2em] text-white/60 ml-2">Business Name</label>
                        <input type="text" x-model="details.business_name" maxlength="255" required
                            class="premium-input w-full h-14 px-5 bg-white/5 border border-white/10 rounded-2xl font-bold text-base text-white placeholder:text-white/30"
                            placeholder="Enter Business Name">
                        <p x-show="fieldError('business_name')" x-cloak x-text="fieldError('business_name')"
                           class="text-rose-300 text-[10px] font-black uppercase tracking-widest ml-2"></p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-[0.2em] text-white/60 ml-2">Business Category</label>
                        <select x-model="details.vendor_type" required
                            class="premium-input w-full h-14 px-5 bg-white/5 border border-white/10 rounded-2xl font-bold text-base text-white">
                            @foreach($vendorCategories as $category)
                                @php $themeConfig = \App\Services\ThemeService::getTheme($category->slug); @endphp
                                <option value="{{ $category->slug }}" class="bg-[#0d1333]">
                                    {{ ($themeConfig['emoji'] ?? '✨') . ' ' . ($themeConfig['label'] ?? $category->name) }}
                                </option>
                            @endforeach
                        </select>
                        <p x-show="fieldError('vendor_type')" x-cloak x-text="fieldError('vendor_type')"
                           class="text-rose-300 text-[10px] font-black uppercase tracking-widest ml-2"></p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-[0.2em] text-white/60 ml-2">Mobile Number</label>
                        <input type="tel" x-ref="mobileInput" x-model="details.mobile" required
                               maxlength="10" inputmode="numeric"
                               @input="details.mobile = details.mobile.replace(/[^0-9]/g, '')"
                            class="premium-input w-full h-14 px-5 bg-white/5 border border-white/10 rounded-2xl font-bold text-base text-white placeholder:text-white/30"
                            placeholder="Enter 10-digit mobile number">
                        <p x-show="fieldError('mobile')" x-cloak x-text="fieldError('mobile')"
                           class="text-rose-300 text-[10px] font-black uppercase tracking-widest ml-2"></p>
                        <p class="text-[11px] font-medium text-orange-400 italic leading-relaxed ml-2">
                            Our admin team will contact you on this number to verify your business before approval — please
                            make sure it is reachable.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-[0.2em] text-white/60 ml-2">Referral Code</label>
                        <input type="text" x-model="details.referral_code" maxlength="60"
                            class="premium-input w-full h-14 px-5 bg-white/5 border border-white/10 rounded-2xl font-bold text-base text-white placeholder:text-white/30"
                            placeholder="Enter Referral Code (Optional)">
                        <p x-show="fieldError('referral_code')" x-cloak x-text="fieldError('referral_code')"
                           class="text-rose-300 text-[10px] font-black uppercase tracking-widest ml-2"></p>
                    </div>

                    {{-- The same consent gate the form registration carries; the
                         server checks it again. --}}
                    <div class="space-y-2">
                        <label class="flex items-start gap-3 cursor-pointer text-white/70 font-medium italic text-sm text-left">
                            <input type="checkbox" x-model="details.terms" required
                                   class="mt-0.5 w-5 h-5 shrink-0 cursor-pointer rounded-md border-2 border-white/30 bg-white/10 text-emerald-500 focus:ring-2 focus:ring-emerald-400/60">
                            <span>
                                I have read and agree to the
                                <a href="{{ route('terms') }}" target="_blank" rel="noopener" class="underline decoration-white/30 underline-offset-4 hover:text-white">Terms and Conditions</a>
                                and the
                                <a href="{{ route('privacy') }}" target="_blank" rel="noopener" class="underline decoration-white/30 underline-offset-4 hover:text-white">Privacy Policy</a>.
                            </span>
                        </label>
                        <p x-show="fieldError('terms')" x-cloak x-text="fieldError('terms')"
                           class="text-rose-300 text-[10px] font-black uppercase tracking-widest ml-8"></p>
                    </div>

                    <div x-show="error" x-cloak class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20">
                        <p class="text-xs font-bold text-rose-300 leading-relaxed" x-text="error"></p>
                    </div>

                    <div class="space-y-3 pt-2">
                        <button type="submit" :disabled="busy"
                            class="w-full h-14 rounded-xl bg-gradient-to-r from-orange-500 to-amber-400 text-slate-900 font-black uppercase tracking-widest text-xs flex items-center justify-center gap-3 transition-all hover:opacity-90 disabled:opacity-60 disabled:cursor-not-allowed">
                            <svg x-show="busy" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"/></svg>
                            <span x-text="busy ? 'Creating Account…' : 'Create Business Account'"></span>
                        </button>
                        <button type="button" @click="cancelDetails()" :disabled="busy"
                            class="w-full h-12 rounded-xl bg-white/5 border border-white/10 text-white/40 hover:text-white/70 font-black uppercase tracking-widest text-[10px] flex items-center justify-center transition-all disabled:opacity-40">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
            @endif

            </div>
        </div>
    </div>

    @if(config('services.google.client_id'))
        @include('partials.vendor-google-auth')
    @endif
</x-app-layout>