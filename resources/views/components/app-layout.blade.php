@props([
    'vendorTheme' => null,
    'pageTitle'   => null,
])
@php
    $theme     = $vendorTheme ?? \App\Services\ThemeService::getTheme('consultant');
    $bodyClass = 'theme-' . $theme['key'];
    $isDark    = $theme['is_dark'] ?? false;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle ?? config('app.name', 'BookAI') }} — Premium Multi-Vendor Platform</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles + Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        {!! \App\Services\ThemeService::getCssVars($theme) !!}
        .theme-nav { background-color: var(--theme-nav-bg) !important; color: var(--theme-nav-text) !important; }
        .nav-link { color: var(--theme-nav-text) !important; }
    </style>
</head>
<body class="antialiased {{ $bodyClass }} min-h-screen relative overflow-x-hidden bg-theme-main">

    <div class="relative z-10 flex flex-col min-h-screen">
        <!-- Navigation (Section 4) -->
        <nav x-data="{ scrolled: false }" 
             @scroll.window="scrolled = (window.pageYOffset > 50)"
             :class="{ 'bg-slate-950/80 backdrop-blur-2xl border-b border-white/5 py-3': scrolled, 'bg-transparent py-6': !scrolled }"
             class="fixed top-0 inset-x-0 z-[100] transition-all duration-500 px-8 flex items-center justify-between">
            <div class="flex items-center gap-10">
                <a href="/" class="group flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl theme-gradient-bg flex items-center justify-center text-white text-2xl font-black theme-glow-sm transition-transform group-hover:rotate-12 group-hover:scale-110">
                        {{ $theme['icon'] ?? 'B' }}
                    </div>
                    <span class="text-2xl font-black tracking-tighter text-white">
                        BOOK<span class="theme-gradient-text">AI</span>
                    </span>
                </a>
            </div>

            <div class="flex items-center gap-10">
                <div class="hidden md:flex items-center gap-10">
                    <a href="{{ route('home') }}" class="text-xs font-black uppercase tracking-widest text-white/70 hover:text-[var(--theme-primary)] transition-colors">Explore</a>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="/admin/dashboard" class="text-xs font-black uppercase tracking-widest text-white/70 hover:text-[var(--theme-primary)] transition-colors">Admin Portal</a>
                        @elseif(auth()->user()->isVendor())
                            <a href="/vendor/dashboard" class="text-xs font-black uppercase tracking-widest theme-gradient-text hover:brightness-110 transition-colors">Business Hub</a>
                        @endif
                    @endauth
                </div>

                <div class="h-6 w-px bg-white/10 hidden sm:block"></div>

                <div class="flex items-center gap-6">
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-[10px] font-black uppercase tracking-widest text-white/30 hover:text-white transition-colors">Logout</button>
                        </form>
                    @else
                        <a href="/login" class="text-xs font-black uppercase tracking-widest text-white/70 hover:text-white">Sign In</a>
                        <a href="/register/vendor" 
                           class="theme-btn px-8 py-3 rounded-xl text-[10px] shadow-none">
                            Join Now
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="flex-grow">
            {{ $slot }}
        </main>

        <!-- Footer (Section 12) -->
        <footer class="bg-slate-950 pt-24 pb-12 border-t border-white/5">
            <div class="container mx-auto px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-12 mb-16">
                    <div class="flex flex-col items-center md:items-start gap-6">
                        <div class="text-3xl font-black text-white tracking-tighter">BOOK<span class="theme-gradient-text">AI</span></div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.3em] max-w-sm text-center md:text-left text-white/30 leading-loose">
                            The Next-Generation Multi-Vendor Booking Experience for Global Professionals.
                        </p>
                    </div>
                    
                    <div class="flex flex-wrap justify-center gap-10 text-[10px] font-black uppercase tracking-[0.3em] text-white/50">
                        <a href="#" class="hover:text-[var(--theme-primary)] transition-colors">Privacy Policy</a>
                        <a href="#" class="hover:text-[var(--theme-primary)] transition-colors">Terms of Service</a>
                        <a href="#" class="hover:text-[var(--theme-primary)] transition-colors">Help Center</a>
                        <a href="#" class="hover:text-[var(--theme-primary)] transition-colors">Contact Us</a>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row items-center justify-between pt-12 border-t border-white/5 gap-6">
                    <div class="text-[10px] font-black uppercase tracking-[0.4em] text-white/20">
                        &copy; {{ date('Y') }} BOOKAI PLATFORM. ALL RIGHTS RESERVED.
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-white/40 hover:text-white transition-colors cursor-pointer border border-white/10">𝕏</div>
                        <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-white/40 hover:text-white transition-colors cursor-pointer border border-white/10">📸</div>
                        <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-white/40 hover:text-white transition-colors cursor-pointer border border-white/10">💼</div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Toast Notifications -->
    <div x-data="{
        show: false, message: '', type: 'success', timer: null,
        triggerToast(msg, type = 'success') {
            this.message = msg; this.type = type; this.show = true;
            if(this.timer) clearTimeout(this.timer);
            this.timer = setTimeout(() => this.show = false, 4000);
        }
    }"
    @toast.window="triggerToast($event.detail.message, $event.detail.type)"
    class="fixed bottom-12 left-1/2 -translate-x-1/2 z-[9999] pointer-events-none">
        <div x-show="show"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 translate-y-20 scale-90"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-400"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-20 scale-90"
             class="px-8 py-5 rounded-[2rem] shadow-[0_30px_60px_-15px_rgba(0,0,0,0.1)] flex items-center gap-4 min-w-[320px] backdrop-blur-3xl border pointer-events-auto"
             :class="{
                'bg-emerald-600/90 text-white border-emerald-400/30': type === 'success',
                'bg-rose-600/90 text-white border-rose-400/30': type === 'error',
                'bg-blue-600/90 text-white border-blue-400/30': type === 'info'
             }" x-cloak>
            <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center shrink-0">
                <template x-if="type === 'success'"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></template>
                <template x-if="type === 'error'"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg></template>
            </div>
            <p class="font-black text-sm tracking-tight" x-text="message"></p>
        </div>
    </div>

    @livewireScripts
</body>
</html>
