<x-app-layout page-title="Reset Access | Appointment Platform">
    <div class="relative min-h-[90vh] flex items-center justify-center py-24" style="background: linear-gradient(180deg,#0a0f2c 0%,#0d1333 100%);">
        <div style="position:absolute; top:0; left:25%; width:500px; height:500px; background:rgba(255,109,0,.08); border-radius:50%; filter:blur(120px); pointer-events:none;"></div>
        <div style="position:absolute; bottom:0; right:25%; width:600px; height:600px; background:rgba(255,109,0,.04); border-radius:50%; filter:blur(150px); pointer-events:none;"></div>
        <div class="absolute inset-0 z-0 bg-dot-pattern opacity-30"></div>

        <div class="relative z-10 w-full max-w-xl px-4 md:px-6 animate-reveal">
            <div class="text-center mb-8 md:mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 border border-white/20 rounded-full text-white/70 text-[9px] font-black uppercase tracking-widest mb-6 md:mb-8">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    Account Recovery
                </div>
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[4rem] font-black text-white mb-6 tracking-tighter leading-[1.1] md:leading-[0.9] italic">
                    Forgot <span style="color:transparent; background:linear-gradient(135deg,#ff8c42,#ffab40); -webkit-background-clip:text; background-clip:text;">Password?</span>
                </h1>
                <p class="text-base md:text-lg font-medium text-white/80 max-w-sm mx-auto italic leading-relaxed px-4">
                    Enter the email on your account and we will send you a secure link to set a new password.
                </p>
            </div>

            <div class="glass-card overflow-hidden shadow-2xl">
                <form method="POST" action="{{ route('password.email') }}" class="p-6 md:p-8 space-y-6 md:space-y-8 rounded-[3rem]">
                    @csrf

                    @if(session('status'))
                        <div class="p-5 rounded-2xl bg-emerald-500/10 border border-emerald-500/25 flex gap-4">
                            <svg class="w-6 h-6 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-sm font-bold text-emerald-300 leading-relaxed">{{ session('status') }}</p>
                        </div>
                    @endif

                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-4 md:ml-6">Email Address</label>
                        <input type="email" name="email" required autofocus value="{{ old('email') }}"
                            class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base placeholder:text-white/40 text-white focus:bg-white/10"
                            placeholder="Registered email address">
                        @error('email')
                            <div class="text-rose-500 text-[10px] font-black uppercase tracking-widest mt-2 ml-4 md:ml-6">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn-premium w-full !rounded-2xl !text-sm justify-center">
                        SEND RESET LINK
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>

                    <div class="pt-8 text-center border-t border-white/10">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-white/40 leading-loose">
                            Remembered it?
                            <a href="{{ route('login') }}" class="text-white transition-all ml-2 underline decoration-2 underline-offset-8 italic" style="text-decoration-color:rgba(255,140,66,0.3);">Back To Login</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
