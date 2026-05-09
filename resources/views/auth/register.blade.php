<x-app-layout page-title="Initialize Partnership | Appointment Platform">
    <div class="relative min-h-[90vh] flex items-center justify-center py-20" style="background: linear-gradient(180deg,#0a0f2c 0%,#0d1333 100%);">
        <!-- Glowing Orbs (From Index) -->
        <div style="position:absolute; top:0; left:25%; width:500px; height:500px; background:rgba(255,109,0,.08); border-radius:50%; filter:blur(120px); pointer-events:none;"></div>
        <div style="position:absolute; bottom:0; right:25%; width:600px; height:600px; background:rgba(255,109,0,.04); border-radius:50%; filter:blur(150px); pointer-events:none;"></div>
        <!-- Subtle Institutional Pattern -->
        <div class="absolute inset-0 z-0 bg-dot-pattern opacity-30"></div>

        <div class="relative z-10 w-full max-w-2xl px-6 animate-reveal">
            <div class="text-center mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 border border-white/20 rounded-full text-white/70 text-[9px] font-black uppercase tracking-widest mb-8">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Scalability Protocol V2.0
                </div>
                <h1 class="text-5xl md:text-[4.5rem] font-black text-white mb-6 tracking-tighter leading-[0.9] italic">
                    Integrate Your <span style="color:transparent; background:linear-gradient(135deg,#00c853,#64dd17); -webkit-background-clip:text; background-clip:text;">Practice.</span>
                </h1>
                <p class="text-lg font-medium text-white/80 max-w-md mx-auto italic leading-relaxed">Join the global network of specialized appointment-based professional services.</p>
            </div>

            <div class="glass-card p-4 overflow-hidden border-white/10 shadow-2xl">
                <div class="p-8 space-y-10 rounded-[3rem]">
                    <!-- Professional Registry Prompt -->
                    <div class="bg-white/5 border border-white/10 rounded-[2.5rem] p-12 text-white relative overflow-hidden group shadow-lg">
                        <div class="absolute inset-0 bg-emerald-500 opacity-0 group-hover:opacity-5 transition-opacity"></div>
                        <h3 class="text-3xl font-black italic mb-2 tracking-tight">Vendor Onboarding</h3>
                        <p class="text-white/60 font-bold text-[9px] uppercase tracking-[0.2em] mb-10 leading-relaxed italic">Deploy high-performance scheduling for your establishment.</p>
                        
                        <a href="/register/vendor" class="btn-premium w-full h-20 !rounded-2xl !text-sm !bg-white/10 !text-white hover:!bg-emerald-600">
                            INITIALIZE ONBOARDING
                            <svg class="w-6 h-6 transition-transform group-hover:translate-x-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>

                    <!-- Divider Matrix -->
                    <div class="relative flex items-center justify-center">
                        <div class="border-t-2 border-white/10 w-full"></div>
                        <span class="bg-[#0a0f2c] px-6 py-1 rounded-full border border-white/10 text-[9px] font-black text-white/50 uppercase tracking-[0.4em] absolute italic">Personal Client Portal?</span>
                    </div>

                    <!-- Customer Intelligence -->
                    <div class="text-center space-y-8 p-4">
                        <p class="text-white/70 font-medium text-lg leading-relaxed italic">Clients feature a <span class="text-white font-black">Zero-Registration</span> workflow. Simply identify your provider and allocate time instantly.</p>
                        
                        <a href="/" class="inline-flex items-center gap-4 text-white font-black uppercase tracking-widest text-[9px] hover:text-white/80 transition-colors group">
                            GLOBAL PROVIDER REGISTRY
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                    </div>

                    <div class="pt-8 border-t border-white/10 text-center">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-white/40">
                            Already Authenticated? 
                            <a href="/login" class="text-white transition-all ml-2 underline decoration-4 underline-offset-8 italic" style="text-decoration-color:rgba(255,140,66,0.3);">Authorize Credentials</a>
                        </p>
                    </div>
                </div>

                <div class="bg-white/[0.02] p-6 flex flex-col gap-4 border-t border-white/5 italic">
                    <div class="flex items-center justify-center gap-10 opacity-30">
                        <div class="flex items-center gap-2">
                            <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span class="text-[8px] font-black uppercase tracking-widest text-white">High-Fidelity</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-[8px] font-black uppercase tracking-widest text-white">Direct Settlement</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
