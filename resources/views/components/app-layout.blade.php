<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Premium Booking</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="antialiased bg-gradient-mesh min-h-screen text-slate-900">
    
    <div class="relative z-10 flex flex-col min-h-screen">
        <!-- Navigation -->
        <nav class="sticky top-0 z-50 glass-card mx-4 mt-4 px-6 py-4 flex items-center justify-between border-slate-200/50 bg-white/70 backdrop-blur-xl">
            <div class="flex items-center gap-8">
                <a href="/" class="text-3xl font-black bg-gradient-to-r from-blue-600 to-sky-400 bg-clip-text text-transparent">
                    BOOKAI
                </a>
            </div>
            <div class="flex items-center gap-6">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="/admin/dashboard" class="nav-link">Admin Panel</a>
                    @elseif(auth()->user()->isVendor())
                        <a href="/vendor/dashboard" class="nav-link font-bold text-blue-600">Vendor Panel</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-link">Logout</button>
                    </form>
                @else
                    <a href="/login" class="nav-link">Vendor Login</a>
                    <a href="/register/vendor" class="btn-primary">List Your Business</a>
                @endauth
            </div>
        </nav>

        <!-- Page Content -->
        <main class="flex-grow container mx-auto px-4 py-8">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="mt-auto py-8 border-t border-white/5 opacity-50">
            <div class="container mx-auto px-4 text-center text-sm">
                <p>&copy; 2026 BOOKAI. AI Powered Appointment Booking.</p>
            </div>
        </footer>
    </div>

    <!-- Global Success/Error Toastr -->
    <div x-data="{ 
        show: false, 
        message: '', 
        type: 'success',
        timer: null,
        triggerToast(msg, type = 'success') {
            this.message = msg;
            this.type = type;
            this.show = true;
            if(this.timer) clearTimeout(this.timer);
            this.timer = setTimeout(() => this.show = false, 4000);
        }
    }" 
    @toast.window="triggerToast($event.detail.message, $event.detail.type)"
    class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[9999]">
        <div x-show="show" 
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 translate-y-10 scale-90"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-10 scale-90"
             class="px-8 py-5 rounded-[2rem] shadow-[0_25px_50px_-12px_rgba(0,0,0,0.5)] flex items-center gap-4 min-w-[320px] backdrop-blur-3xl border-2"
             :class="{
                'bg-emerald-500/90 text-white border-emerald-400/50': type === 'success',
                'bg-rose-500/90 text-white border-rose-400/50': type === 'error',
                'bg-blue-500/90 text-white border-blue-400/50': type === 'info'
             }"
             x-cloak>
            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                <template x-if="type === 'success'">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                </template>
                <template x-if="type === 'error'">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                </template>
                <template x-if="type === 'info'">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </template>
            </div>
            <p class="font-black italic uppercase tracking-widest text-sm" x-text="message"></p>
        </div>
    </div>

    @livewireScripts
</body>
</html>
