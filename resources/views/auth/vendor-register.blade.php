<x-app-layout page-title="Vendor Onboarding | Appointment Platform">
    <div class="relative min-h-screen pb-32" style="background: linear-gradient(180deg,#0a0f2c 0%,#0d1333 100%);">
        <!-- Glowing Orbs (From Index) -->
        <div style="position:absolute; top:0; left:25%; width:500px; height:500px; background:rgba(255,109,0,.08); border-radius:50%; filter:blur(120px); pointer-events:none;"></div>
        <div style="position:absolute; bottom:0; right:25%; width:600px; height:600px; background:rgba(255,109,0,.04); border-radius:50%; filter:blur(150px); pointer-events:none;"></div>
        <!-- Subtle Institutional Pattern -->
        <div class="absolute inset-0 z-0 bg-dot-pattern opacity-30"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-6 pt-20">
            <!-- Header Section -->
            <div class="text-center mb-20 animate-text-reveal">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-slate-900 border border-slate-800 rounded-full text-white text-[9px] font-black uppercase tracking-widest mb-8">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Enterprise Onboarding Protocol
                </div>
                <h1
                    class="text-6xl md:text-[5.5rem] font-black text-white mb-6 tracking-tighter leading-[0.9] italic">
                    Scale Your <span style="color:transparent; background:linear-gradient(135deg,#ff8c42,#ffab40); -webkit-background-clip:text; background-clip:text;">Enterprise.</span>
                </h1>
                <p class="text-xl font-medium text-white/50 max-w-xl mx-auto italic leading-relaxed">Join the global
                    infrastructure for professional appointment-based service providers.</p>
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
            </style>

            <form method="POST" action="/register/vendor" class="space-y-16 animate-reveal delay-100">
                @csrf

                <!-- STEP 1: IDENTITY -->
                <div class="glass-card p-4 overflow-hidden shadow-2xl">
                    <div class="p-10 md:p-14 rounded-[3rem]">
                        <div class="flex items-center gap-6 mb-12">
                            <div class="w-14 h-14 bg-white/10 text-white border border-white/20 rounded-2xl flex items-center justify-center text-xl font-black italic shadow-xl">01</div>
                            <div>
                                <h3 class="text-3xl font-black italic tracking-tight uppercase text-white">Registry Identity</h3>
                                <p class="text-xs font-black uppercase tracking-[0.2em] text-white/40">CORE ESTABLISHMENT DATA</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10">
                            <!-- Category -->
                            <div class="space-y-2 group">
                                <label class="text-[9px] font-black uppercase tracking-[0.2em] text-white/60 ml-6">Specialization Sector</label>
                                <div class="relative">
                                    <select name="vendor_type" required
                                        class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base text-white appearance-none cursor-pointer transition-all focus:bg-white/10">
                                        @foreach($vendorCategories as $category)
                                            <option value="{{ $category->slug }}" class="bg-[#0f172a] text-white" {{ old('vendor_type') == $category->slug ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none">
                                        <svg class="w-5 h-5 text-white/30 group-focus-within:text-theme-primary transition-colors"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Business Name -->
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-6">Institutional Name</label>
                                <input type="text" name="business_name" value="{{ old('business_name') }}" required
                                    class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base text-white placeholder:text-white/30 transition-all focus:bg-white/10"
                                    placeholder="Business Nomenclature">
                            </div>

                            <!-- Owner Name -->
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-6">Principal Representative</label>
                                <input type="text" name="owner_name" value="{{ old('owner_name') }}" required
                                    class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base text-white placeholder:text-white/30 transition-all focus:bg-white/10"
                                    placeholder="Legal Full Name">
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-6">Primary Communication Hub</label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                    class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base text-white placeholder:text-white/30 transition-all focus:bg-white/10"
                                    placeholder="Corporate Email Identity">
                            </div>

                            <!-- Mobile -->
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-6">Mobile Uplink Number</label>
                                <input type="tel" name="mobile" value="{{ old('mobile') }}" required maxlength="10"
                                    class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base text-white placeholder:text-white/30 transition-all focus:bg-white/10"
                                    placeholder="10-digit primary uplink">
                            </div>

                            <!-- Referral -->
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-6">Registry Referral (OPTIONAL)</label>
                                <input type="text" name="referral_code" value="{{ old('referral_code', request('ref')) }}"
                                    class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base text-white placeholder:text-white/30 transition-all focus:bg-white/10"
                                    placeholder="XXXX-XXXX">
                            </div>

                            <!-- Password -->
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-6">Secure Access Key</label>
                                <input type="password" name="password" required
                                    class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base text-white placeholder:text-white/30 transition-all focus:bg-white/10"
                                    placeholder="••••••••">
                            </div>

                            <!-- Confirm -->
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-6">Confirm Key</label>
                                <input type="password" name="password_confirmation" required
                                    class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base text-white placeholder:text-white/30 transition-all focus:bg-white/10"
                                    placeholder="••••••••">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: PLAN -->
                <div class="glass-card p-4 overflow-hidden shadow-2xl">
                    <div class="p-10 md:p-14 rounded-[3rem]">
                        <div class="flex items-center gap-6 mb-12">
                            <div class="w-14 h-14 bg-white/10 text-white border border-white/20 rounded-2xl flex items-center justify-center text-xl font-black italic shadow-xl">02</div>
                            <div>
                                <h3 class="text-3xl font-black italic tracking-tight uppercase text-white">Operational Tier</h3>
                                <p class="text-[9px] font-black uppercase tracking-[0.2em] text-white/40">SCALING CAPACITY SELECTION</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            @foreach($plans as $plan)
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="subscription_plan_id" value="{{ $plan->id }}"
                                    class="peer sr-only plan-card-input" {{ $loop->first ? 'checked' : '' }}>
                                <div class="plan-card-content h-full p-8 bg-white/5 border border-white/10 rounded-[2.5rem] transition-all duration-300 text-white group-hover:translate-y-[-4px] group-hover:shadow-xl antialiased focus-within:ring-4 focus-within:ring-theme-primary/10 hover:bg-white/10">
                                    <h4 class="text-xl font-black mb-2 italic">{{ $plan->name }}</h4>
                                    <div class="flex items-baseline gap-1 mb-6">
                                        <span class="text-4xl font-black tracking-tighter italic">₹{{ number_format($plan->price) }}</span>
                                        <span class="pricing-cycle text-[9px] font-black uppercase tracking-widest text-white/40">/ CYCLE</span>
                                    </div>

                                    <div class="space-y-4 mb-4">
                                        <div class="flex items-center gap-3">
                                            <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" /></svg>
                                            <span class="text-[10px] font-black uppercase tracking-widest">{{ $plan->max_employees }} Specialists</span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" /></svg>
                                            <span class="text-[10px] font-black uppercase tracking-widest">Global Scheduling</span>
                                        </div>
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
                <div class="flex flex-col md:flex-row items-center justify-between gap-12 pt-8">
                    <p class="text-white/60 font-medium italic max-w-sm text-center md:text-left text-sm leading-relaxed">
                        By initializing deployment, you authorize acceptance of our <a href="#" class="text-white font-black italic underline underline-offset-4 transition-colors" style="text-decoration-color:rgba(255,140,66,0.3);">Institutional Master Protocols</a>.
                    </p>
                    <button type="submit" class="btn-premium w-full md:w-auto h-24 px-16 !rounded-[2rem] !text-2xl">
                        INITIALIZE DEPLOYMENT
                        <svg class="w-8 h-8 transition-transform group-hover:translate-x-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>