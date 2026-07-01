<x-app-layout page-title="Approval Pending | Appointment Platform">
    <div class="relative min-h-[90vh] flex items-center justify-center py-24" style="background: linear-gradient(180deg,#0a0f2c 0%,#0d1333 100%);">
        <!-- Glowing Orbs (From Index) -->
        <div style="position:absolute; top:0; left:25%; width:500px; height:500px; background:rgba(255,109,0,.08); border-radius:50%; filter:blur(120px); pointer-events:none;"></div>
        <div style="position:absolute; bottom:0; right:25%; width:600px; height:600px; background:rgba(255,109,0,.04); border-radius:50%; filter:blur(150px); pointer-events:none;"></div>
        <!-- Subtle Institutional Pattern -->
        <div class="absolute inset-0 z-0 bg-dot-pattern opacity-30"></div>

        <div class="relative z-10 w-full max-w-xl px-4 md:px-6 animate-reveal">
            <div class="text-center mb-8 md:mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 border border-white/20 rounded-full text-white/70 text-[9px] font-black uppercase tracking-widest mb-6 md:mb-8">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Awaiting Admin Confirmation
                </div>
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[4rem] font-black text-white mb-6 tracking-tighter leading-[1.1] md:leading-[0.9] italic">
                    Approval <span style="color:transparent; background:linear-gradient(135deg,#ff8c42,#ffab40); -webkit-background-clip:text; background-clip:text;">Pending.</span>
                </h1>
                <p class="text-base md:text-lg font-medium text-white/80 max-w-sm mx-auto italic leading-relaxed px-4">
                    Thanks for registering{{ $vendor && $vendor->business_name ? ', ' . $vendor->business_name : '' }}. Your
                    account is under review — once an admin approves it, you'll unlock your vendor panel.
                </p>
            </div>

            <div class="glass-card overflow-hidden shadow-2xl">
                <div class="p-6 md:p-8 space-y-6 md:space-y-8 rounded-[3rem]">

                    <div class="text-center">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-white/50 leading-loose">
                            Need help? Contact Admin
                        </p>
                    </div>

                    <!-- Contact admin: two ways to reach us -->
                    <div class="space-y-4">
                        <a href="mailto:{{ $adminEmail }}?subject=Vendor%20Approval%20Request"
                           class="w-full flex items-center gap-4 px-6 h-16 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/25 rounded-2xl transition-all group">
                            <span class="w-10 h-10 shrink-0 bg-white/10 text-orange-400 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                            <span class="flex-grow text-left">
                                <span class="block text-[9px] font-black uppercase tracking-widest text-white/40 italic">Email Us</span>
                                <span class="block text-sm font-bold text-white truncate">{{ $adminEmail }}</span>
                            </span>
                            <svg class="w-5 h-5 text-white/30 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                        </a>

                        <a href="tel:{{ $adminPhone }}"
                           class="w-full flex items-center gap-4 px-6 h-16 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/25 rounded-2xl transition-all group">
                            <span class="w-10 h-10 shrink-0 bg-white/10 text-orange-400 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </span>
                            <span class="flex-grow text-left">
                                <span class="block text-[9px] font-black uppercase tracking-widest text-white/40 italic">Call Us</span>
                                <span class="block text-sm font-bold text-white truncate">{{ $adminPhone }}</span>
                            </span>
                            <svg class="w-5 h-5 text-white/30 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    <div class="pt-8 text-center border-t border-white/10">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-[10px] font-black uppercase tracking-[0.2em] text-white/40 hover:text-white transition-colors italic">Sign out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
