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

            <form method="POST" action="/register/vendor" class="reg-form space-y-16 animate-reveal delay-100">
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

                            <!-- Password -->
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-6">Password</label>
                                <input type="password" name="password" required
                                    class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base text-white placeholder:text-white/30 transition-all focus:bg-white/10"
                                    placeholder="Create Password">
                            </div>

                            <!-- Confirm -->
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-6">Confirm Password</label>
                                <input type="password" name="password_confirmation" required
                                    class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base text-white placeholder:text-white/30 transition-all focus:bg-white/10"
                                    placeholder="Re-Enter Your Password">
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
        </div>
    </div>
</x-app-layout>