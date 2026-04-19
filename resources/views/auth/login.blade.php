<x-app-layout page-title="Secure Portal Access | Appointment Platform">
    <div class="relative min-h-[90vh] flex items-center justify-center py-20 bg-theme-main">
        <!-- Subtle Institutional Pattern -->
        <div class="absolute inset-0 z-0 bg-dot-pattern opacity-30"></div>

        <div class="relative z-10 w-full max-w-xl px-6 animate-reveal">
            <div class="text-center mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 border border-white/20 rounded-full text-white/70 text-[9px] font-black uppercase tracking-widest mb-8">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Institutional Security Protocol
                </div>
                <h1 class="text-5xl md:text-[4rem] font-black text-white mb-6 tracking-tighter leading-[0.9] italic">
                    Authorize <span class="text-theme-primary">Access.</span>
                </h1>
                <p class="text-lg font-medium text-white/80 max-w-sm mx-auto italic leading-relaxed">Login to manage your professional appointments and registry credentials.</p>
            </div>

            <div class="glass-card p-4 overflow-hidden shadow-2xl">
                <form method="POST" action="/login" class="p-8 space-y-8 rounded-[3rem]">
                    @csrf
                    
                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 ml-6">User Identification</label>
                        <input type="email" name="email" required 
                               class="premium-input w-full h-18 px-10 bg-slate-50 border-slate-200 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-lg placeholder:text-slate-400 text-slate-900" 
                               placeholder="Primary Email Identity" value="{{ old('email') }}">
                        @error('email')
                            <div class="text-rose-500 text-[10px] font-black uppercase tracking-widest mt-2 ml-6">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 ml-6">Security Credential</label>
                        <input type="password" name="password" required 
                               class="premium-input w-full h-18 px-10 bg-slate-50 border-slate-200 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-lg placeholder:text-slate-400 text-slate-900" 
                               placeholder="••••••••">
                        @error('password')
                            <div class="text-rose-500 text-[10px] font-black uppercase tracking-widest mt-2 ml-6">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between pt-2 px-4">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="remember" class="w-5 h-5 rounded-lg border-2 border-slate-200 bg-slate-50 text-theme-primary focus:ring-theme-primary/10">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Persistent Sync</span>
                        </label>
                        <a href="#" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-theme-primary transition-colors">Recover Keys</a>
                    </div>

                    <button type="submit" class="btn-premium w-full h-20 !rounded-2xl !text-sm">
                        INITIALIZE ACCESS
                        <svg class="w-6 h-6 transition-transform group-hover:translate-x-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                    
                    <div class="pt-8 text-center border-t border-slate-100">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 leading-loose">
                            Uncataloged Provider? 
                            <a href="/register" class="text-slate-900 hover:text-theme-primary transition-all ml-2 underline decoration-theme-primary/30 decoration-4 underline-offset-8 italic">New Registry Registration</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>
</x-app-layout>
