<x-app-layout page-title="Secure Portal Access | Appointment Platform">
    <div class="relative min-h-[90vh] flex items-center justify-center py-24" style="background: linear-gradient(180deg,#0a0f2c 0%,#0d1333 100%);">
        <!-- Glowing Orbs (From Index) -->
        <div style="position:absolute; top:0; left:25%; width:500px; height:500px; background:rgba(255,109,0,.08); border-radius:50%; filter:blur(120px); pointer-events:none;"></div>
        <div style="position:absolute; bottom:0; right:25%; width:600px; height:600px; background:rgba(255,109,0,.04); border-radius:50%; filter:blur(150px); pointer-events:none;"></div>
        <!-- Subtle Institutional Pattern -->
        <div class="absolute inset-0 z-0 bg-dot-pattern opacity-30"></div>

        <div class="relative z-10 w-full max-w-xl px-4 md:px-6 animate-reveal">
            <div class="text-center mb-8 md:mb-12">
                <div
                    class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 border border-white/20 rounded-full text-white/70 text-[9px] font-black uppercase tracking-widest mb-6 md:mb-8">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Portal Security Protocol
                </div>
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[4rem] font-black text-white mb-6 tracking-tighter leading-[1.1] md:leading-[0.9] italic">
                    Authorize <span style="color:transparent; background:linear-gradient(135deg,#ff8c42,#ffab40); -webkit-background-clip:text; background-clip:text;">Access.</span>
                </h1>
                <p class="text-base md:text-lg font-medium text-white/80 max-w-sm mx-auto italic leading-relaxed px-4">Login to manage
                    your professional appointments and registry credentials.</p>
            </div>

            @php
                // Google sign-in is optional: with no client id configured the
                // tab is not offered at all and the card is the email form it
                // has always been.
                $googleClientId = config('services.google.client_id');
            @endphp

            {{-- The Alpine component only exists when Google does. Rendering
                 x-data / x-show unconditionally would leave the email form
                 bound to an undefined `tab` on an install with no client id —
                 and x-show on an undefined value hides it. --}}
            <div class="glass-card overflow-hidden shadow-2xl"
                 @if($googleClientId)
                 x-data="vendorGoogleAuth({
                    mode: 'login',
                    clientId: @js($googleClientId),
                    endpoint: @js(route('auth.google.vendor.login')),
                 })"
                 @endif>

                {{-- Confirmation carried over from a completed password reset.
                     Above the tabs so it is read whichever one is showing. --}}
                @if(session('status'))
                    <div class="px-6 pt-6 md:px-8">
                    <div class="p-5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex gap-4">
                        <svg class="w-6 h-6 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm font-bold text-emerald-300 leading-relaxed">{{ session('status') }}</p>
                    </div>
                    </div>
                @endif

                @if($googleClientId)
                <div class="px-6 md:px-8 pt-6 md:pt-8">
                    <div class="flex gap-2 p-2 bg-white/5 border border-white/10 rounded-2xl">
                        <button type="button" @click="tab = 'email'"
                            class="flex-1 h-11 rounded-xl text-[9px] md:text-[10px] font-black uppercase tracking-widest transition-all"
                            :class="tab === 'email' ? 'bg-white/15 text-white shadow-lg' : 'text-white/40 hover:text-white/70'">
                            Email &amp; Password
                        </button>
                        <button type="button" @click="tab = 'google'"
                            class="flex-1 h-11 rounded-xl text-[9px] md:text-[10px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2"
                            :class="tab === 'google' ? 'bg-white/15 text-white shadow-lg' : 'text-white/40 hover:text-white/70'">
                            <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.1 8 3l5.7-5.7C34.5 6.1 29.5 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.6-.4-3.9z"/><path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.1 8 3l5.7-5.7C34.5 6.1 29.5 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.4 0 10.3-2.1 14-5.4l-6.2-5.2C29.9 34.9 27.1 36 24 36c-5.2 0-9.6-3.3-11.3-7.9l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.3-2.3 4.2-4.2 5.5l6.2 5.2C36.9 40.2 44 35 44 24c0-1.3-.1-2.6-.4-3.9z"/></svg>
                            Google
                        </button>
                    </div>
                </div>
                @endif

                <form @if($googleClientId) x-show="tab === 'email'" @endif
                      method="POST" action="/login" class="p-6 md:p-8 space-y-6 md:space-y-8 rounded-[3rem]">
                    @csrf

                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-4 md:ml-6">Email
                            Address</label>
                        <input type="email" name="email" required
                            class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base placeholder:text-white/40 text-white focus:bg-white/10"
                            placeholder="Primary Email Identity" value="{{ old('email') }}">
                        @error('email')
                        <div class="text-rose-500 text-[10px] font-black uppercase tracking-widest mt-2 ml-4 md:ml-6">{{
                            $message }}</div>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label
                            class="text-xs font-black uppercase tracking-[0.2em] text-white/60 ml-4 md:ml-6">Password</label>
                        <input type="password" name="password" required
                            class="premium-input w-full h-14 px-6 bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-theme-primary/10 font-bold text-base placeholder:text-white/40 text-white focus:bg-white/10"
                            placeholder="••••••••">
                        @error('password')
                        <div class="text-rose-500 text-[10px] font-black uppercase tracking-widest mt-2 ml-4 md:ml-6">{{
                            $message }}</div>
                        @enderror
                    </div>

                    {{-- No "Remember me" box: every login is remembered until the
                         user presses Logout, so a checkbox here would be a lie. --}}
                    <div class="flex items-center justify-end pt-2 px-2 md:px-4">
                        <a href="{{ route('password.request') }}"
                            class="text-[9px] md:text-[10px] font-black uppercase tracking-widest text-white/40 hover:text-white transition-colors">Forgot Password</a>
                    </div>

                    <button type="submit" class="btn-premium w-full !rounded-2xl !text-sm justify-center">
                        LOGIN
                        <svg class="w-4 h-4 md:w-4 md:h-4 transition-transform group-hover:translate-x-2" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>

                </form>

                @if($googleClientId)
                {{-- Google panel.

                     Businesses only: the endpoint refuses any address that is
                     not a registered vendor, so a customer or a guest cannot
                     open a panel through this button. The refusal comes back as
                     the message below rather than as a silent nothing. --}}
                <div x-show="tab === 'google'" x-cloak class="p-6 md:p-8 space-y-6">

                    <div class="text-center space-y-2">
                        <h3 class="text-lg font-black italic tracking-tight text-white">Sign in with Google</h3>
                        <p class="text-xs font-medium text-white/50 italic leading-relaxed">
                            For registered businesses. Use the Google account your business is registered with.
                        </p>
                    </div>

                    <div x-show="!busy" class="flex justify-center min-h-[44px]">
                        <div x-ref="googleBtn"></div>
                    </div>

                    <div x-show="busy" x-cloak class="flex items-center justify-center gap-3 h-11">
                        <svg class="w-5 h-5 animate-spin text-white/60" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"/></svg>
                        <span class="text-[10px] font-black uppercase tracking-widest text-white/60">Signing you in…</span>
                    </div>

                    <template x-if="account">
                        <div class="flex items-center gap-3 rounded-2xl border border-sky-500/30 bg-sky-500/5 p-3">
                            <img :src="account?.picture" x-show="account?.picture" referrerpolicy="no-referrer" class="w-10 h-10 rounded-full border border-white/10 shrink-0" alt="">
                            <div class="min-w-0">
                                <span class="block text-sm font-black text-white truncate" x-text="account?.name"></span>
                                <span class="block text-[10px] font-black uppercase tracking-widest text-sky-400 truncate" x-text="account?.email"></span>
                            </div>
                        </div>
                    </template>

                    <div x-show="error" x-cloak class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20">
                        <p class="text-xs font-bold text-rose-300 leading-relaxed" x-text="error"></p>
                    </div>

                    <p class="text-center text-[10px] font-black uppercase tracking-[0.2em] text-white/30 leading-loose">
                        Not registered yet?
                        <a href="/register/vendor" class="text-white/70 hover:text-white ml-1 underline decoration-2 underline-offset-4 italic">Sign up with Google</a>
                    </p>
                </div>
                @endif

                <div class="px-6 md:px-8 pb-10 pt-8 text-center border-t border-white/10">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-white/40 leading-loose">
                        New Provider?
                        <a href="/register/vendor"
                            class="text-white transition-all ml-2 underline decoration-2 underline-offset-8 italic" style="text-decoration-color:rgba(255,140,66,0.3);">Register</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if(config('services.google.client_id'))
        @include('partials.vendor-google-auth')
    @endif
</x-app-layout>