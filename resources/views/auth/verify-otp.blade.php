<x-app-layout page-title="Security Verification | Appointment Platform">
    <div class="relative min-h-[90vh] flex items-center justify-center py-20 bg-white">
        <!-- Subtle Institutional Pattern -->
        <div class="absolute inset-0 z-0 bg-dot-pattern opacity-50"></div>

        <div class="relative z-10 w-full max-w-md px-6 animate-reveal">
            <div class="bg-white p-4 shadow-2xl shadow-slate-200/50 border border-slate-100 rounded-[3.5rem] overflow-hidden text-center">
                <div class="p-8 space-y-8">
                    <div class="w-16 h-16 bg-slate-50 border border-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto mb-2">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>

                    <div>
                        <h2 class="text-3xl font-black italic tracking-tight uppercase mb-2">Verify <span class="text-blue-600">Identity.</span></h2>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300 italic">6-DIGIT SECURITY UPLINK SENT</p>
                    </div>

                    @if(session('success'))
                        <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl text-[10px] font-black uppercase tracking-widest italic">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="/verify-otp" class="space-y-8">
                        @csrf
                        
                        <div class="space-y-2">
                            <input type="text" name="otp" required maxlength="6" 
                                   class="w-full h-20 bg-slate-50 border-none rounded-2xl text-center text-4xl font-black tracking-[0.5em] focus:ring-4 focus:ring-blue-50 text-slate-900 placeholder:text-slate-100" 
                                   placeholder="000000" autofocus>
                            @error('otp')
                                <p class="text-rose-500 text-[10px] font-black uppercase tracking-widest mt-2 italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full h-20 bg-slate-900 text-white rounded-2xl font-black italic uppercase tracking-widest hover:bg-black transition-all flex items-center justify-center gap-4 group">
                            AUTHORIZE CONTINUATION
                            <svg class="w-6 h-6 transition-transform group-hover:translate-x-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>

                        <div class="pt-4">
                            <a href="/resend-otp" class="text-[9px] font-black uppercase tracking-widest text-slate-300 hover:text-blue-600 transition-colors italic">Bilateral Sync Failure? Resend OTP</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
