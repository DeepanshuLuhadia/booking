<x-app-layout page-title="Set New Password | Appointment Platform">
    <div class="relative min-h-[90vh] flex items-center justify-center py-24" style="background: linear-gradient(180deg,#0a0f2c 0%,#0d1333 100%);">
        <div style="position:absolute; top:0; left:25%; width:500px; height:500px; background:rgba(255,109,0,.08); border-radius:50%; filter:blur(120px); pointer-events:none;"></div>
        <div style="position:absolute; bottom:0; right:25%; width:600px; height:600px; background:rgba(255,109,0,.04); border-radius:50%; filter:blur(150px); pointer-events:none;"></div>
        <div class="absolute inset-0 z-0 bg-dot-pattern opacity-30"></div>

        <div class="relative z-10 w-full max-w-xl px-4 md:px-6 animate-reveal">
            <div class="text-center mb-8 md:mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 border border-white/20 rounded-full text-white/70 text-[9px] font-black uppercase tracking-widest mb-6 md:mb-8">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Secure Reset
                </div>
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[4rem] font-black text-white mb-6 tracking-tighter leading-[1.1] md:leading-[0.9] italic">
                    New <span style="color:transparent; background:linear-gradient(135deg,#ff8c42,#ffab40); -webkit-background-clip:text; background-clip:text;">Password.</span>
                </h1>
                <p class="text-base md:text-lg font-medium text-white/80 max-w-sm mx-auto italic leading-relaxed px-4">
                    Choose a password you have not used here before. At least 8 characters.
                </p>
            </div>

            <div class="glass-card overflow-hidden shadow-2xl">
                <form method="POST" action="{{ route('password.update') }}" class="p-6 md:p-8 space-y-6 md:space-y-8 rounded-[3rem]">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-4 md:ml-6">Email Address</label>
                        <input type="email" name="email" required value="{{ old('email', $email) }}"
                            class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base placeholder:text-white/40 text-white focus:bg-white/10"
                            placeholder="Registered email address">
                        @error('email')
                            <div class="text-rose-500 text-[10px] font-black uppercase tracking-widest mt-2 ml-4 md:ml-6">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-4 md:ml-6">New Password</label>
                        <input type="password" name="password" required autofocus autocomplete="new-password" minlength="8"
                            class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base placeholder:text-white/40 text-white focus:bg-white/10"
                            placeholder="••••••••">
                        @error('password')
                            <div class="text-rose-500 text-[10px] font-black uppercase tracking-widest mt-2 ml-4 md:ml-6">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-4 md:ml-6">Confirm Password</label>
                        <input type="password" name="password_confirmation" required autocomplete="new-password" minlength="8"
                            class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base placeholder:text-white/40 text-white focus:bg-white/10"
                            placeholder="••••••••">
                        @error('password_confirmation')
                            <div class="text-rose-500 text-[10px] font-black uppercase tracking-widest mt-2 ml-4 md:ml-6">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn-premium w-full !rounded-2xl !text-sm justify-center">
                        UPDATE PASSWORD
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>

                    <div class="pt-8 text-center border-t border-white/10">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-white/40 leading-loose">
                            Link expired?
                            <a href="{{ route('password.request') }}" class="text-white transition-all ml-2 underline decoration-2 underline-offset-8 italic" style="text-decoration-color:rgba(255,140,66,0.3);">Request A New One</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
