@props([
    'vendorTheme' => null,
    'pageTitle'   => null,
    'panelType'   => null,
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

    <title>{{ $pageTitle ?? config('app.name', 'BookAppointment') }} — Premium Multi-Vendor Platform</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Firebase SDK (Version 8) -->
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js"></script>


    <!-- Styles + Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        {!! \App\Services\ThemeService::getCssVars($theme) !!}
        .theme-nav { background-color: var(--theme-nav-bg) !important; color: var(--theme-nav-text) !important; }
        .nav-link { color: var(--theme-nav-text) !important; }
        /* Desktop nav menu: hidden by default, flex on lg+ */
        .nav-desktop-menu {
            display: none;
        }
        /* Mobile hamburger: visible by default, hidden on lg+ */
        .nav-mobile-toggle {
            display: flex;
        }
        @media (min-width: 1024px) {
            .nav-desktop-menu {
                display: flex !important;
            }
            .nav-mobile-toggle {
                display: none !important;
            }
        }
    </style>
</head>
<body class="antialiased {{ $bodyClass }} min-h-screen relative overflow-x-hidden bg-theme-main">    @if(!request()->cookie('location_granted'))
    <!-- Step 1: Mandatory Location Consent Modal (custom only — no browser geolocation prompt) -->
    <div x-data="{
             showLocationModal: true,
             loading: false,
             init() {
                 if (localStorage.getItem('location_granted')) {
                     this.showLocationModal = false;
                     document.cookie = 'location_granted=true; path=/; max-age=31536000; SameSite=Lax';
                 }
             },
             requestLocation() {
                 this.loading = true;
                 document.cookie = 'location_granted=true; path=/; max-age=31536000; SameSite=Lax';
                 localStorage.setItem('location_granted', 'true');
                 window.location.reload();
             }
         }"
         x-show="showLocationModal"
         x-cloak
         style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; z-index: 2147483647 !important; display: flex !important; align-items: center !important; justify-content: center !important; background: rgba(10, 15, 44, 0.98) !important; backdrop-filter: blur(12px) !important; -webkit-backdrop-filter: blur(12px) !important;"
         class="px-4"
    >
        <div style="background-color: #0a0f2c !important; position: relative !important; z-index: 2147483647 !important;" class="max-w-md w-full border border-white/10 rounded-3xl p-8 flex flex-col items-center text-center shadow-2xl overflow-hidden">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-purple-500/10 rounded-full blur-3xl"></div>

            <div class="w-20 h-20 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mb-6 relative z-10">
                <svg class="w-10 h-10 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            
            <h2 class="text-2xl font-black text-white tracking-tight mb-3 relative z-10">Location Access Required</h2>
            <p class="text-sm text-white/60 mb-8 relative z-10 leading-relaxed">
                To provide you with the best experience and locate services near you, we strictly require access to your location. You cannot proceed without sharing it.
            </p>

            <button 
                @click="requestLocation()" 
                :disabled="loading"
                class="w-full h-14 rounded-xl theme-gradient-bg text-white font-black uppercase tracking-widest text-xs flex items-center justify-center gap-2 transition-all hover:brightness-110 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed relative z-10"
            >
                <span x-show="!loading">Grant Location Access</span>
                <span x-show="loading" class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Confirming...
                </span>
            </button>
        </div>
    </div>
    @endif

    <!-- Step 2: Notification Consent Modal (custom — browser prompt only fires AFTER user clicks Enable) -->
    <div x-data="{
             showNotifModal: false,
             loading: false,
             init() {
                 // Only show if: location already granted + notifications not yet permitted + not dismissed
                 const locationOk = !!localStorage.getItem('location_granted');
                 const notifAlready = (typeof Notification !== 'undefined' && Notification.permission === 'granted');
                 const dismissed = !!localStorage.getItem('notif_consent_dismissed');
                 if (locationOk && !notifAlready && !dismissed) {
                     // Small delay so the page content loads first
                     setTimeout(() => { this.showNotifModal = true; }, 800);
                 }
             },
             enableNotifications() {
                 this.loading = true;
                 // This is user-initiated (click), so the browser prompt fires naturally
                 if (typeof window.__requestNotificationPermission === 'function') {
                     window.__requestNotificationPermission();
                 }
                 localStorage.setItem('notif_consent_dismissed', 'true');
                 this.showNotifModal = false;
             },
             dismissNotifications() {
                 localStorage.setItem('notif_consent_dismissed', 'true');
                 this.showNotifModal = false;
             }
         }"
         x-show="showNotifModal"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; z-index: 2147483647 !important; display: flex !important; align-items: center !important; justify-content: center !important; background: rgba(10, 15, 44, 0.95) !important; backdrop-filter: blur(12px) !important; -webkit-backdrop-filter: blur(12px) !important;"
         class="px-4"
    >
        <div style="background-color: #0a0f2c !important; position: relative !important; z-index: 2147483647 !important;" class="max-w-md w-full border border-white/10 rounded-3xl p-8 flex flex-col items-center text-center shadow-2xl overflow-hidden">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl"></div>

            <div class="w-20 h-20 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mb-6 relative z-10">
                <svg class="w-10 h-10 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            
            <h2 class="text-2xl font-black text-white tracking-tight mb-3 relative z-10">Enable Notifications</h2>
            <p class="text-sm text-white/60 mb-8 relative z-10 leading-relaxed">
                Stay updated with real-time booking confirmations, appointment reminders, and important messages from your service providers.
            </p>

            <button 
                @click="enableNotifications()" 
                :disabled="loading"
                class="w-full h-14 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 text-white font-black uppercase tracking-widest text-xs flex items-center justify-center gap-2 transition-all hover:brightness-110 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed relative z-10 mb-3"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                Enable Notifications
            </button>

            <button 
                @click="dismissNotifications()" 
                class="w-full h-12 rounded-xl bg-white/5 border border-white/10 text-white/40 font-bold uppercase tracking-widest text-[10px] flex items-center justify-center gap-2 transition-all hover:bg-white/10 hover:text-white/60 active:scale-95 relative z-10"
            >
                Maybe Later
            </button>
        </div>
    </div>

    <div x-data="{ scrolled: false, mobileMenu: false }" data-panel-type="{{ $panelType }}" class="relative z-10 flex flex-col min-h-screen">
        <!-- Navigation (Section 4) -->
        <nav @scroll.window="scrolled = (window.pageYOffset > 50)"
             :class="{ 'bg-[#0a0f2c]/80 backdrop-blur-2xl border-b border-white/5 py-3': scrolled, 'bg-transparent py-5 md:py-6': !scrolled }"
             class="fixed top-0 inset-x-0 z-[100] transition-all duration-500 px-4 md:px-8 flex items-center justify-between overflow-visible border-0 border-none">
            
            <div class="flex items-center gap-4 md:gap-10 {{ $panelType ? 'lg:hidden' : '' }}">
                <a href="/" class="group flex items-center gap-2 md:gap-3">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl md:rounded-2xl theme-gradient-bg flex items-center justify-center text-white text-xl md:text-2xl font-black theme-glow-sm transition-transform group-hover:rotate-12 group-hover:scale-110">
                        {{ $theme['icon'] ?? 'B' }}
                    </div>
                    <span class="text-xl md:text-2xl font-black tracking-tighter text-white whitespace-nowrap">
                         BOOK<span class="theme-gradient-text">APPOINTMENT</span>
                    </span>
                </a>
            </div>

            <!-- Desktop Menu -->
            <div class="nav-desktop-menu items-center gap-4 ml-auto">
                <div class="flex items-center gap-10">
                    <a href="{{ route('home') }}" class="text-xs font-black uppercase tracking-widest text-white/70 hover:text-[var(--theme-primary)] transition-colors">Explore</a>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="/admin/dashboard" class="text-xs font-black uppercase tracking-widest text-white/70 hover:text-[var(--theme-primary)] transition-colors">Admin Portal</a>
                        @elseif(auth()->user()->isVendor())
                            <a href="/vendor/dashboard" class="text-xs font-black uppercase tracking-widest theme-gradient-text hover:brightness-110 transition-colors">Business Hub</a>
                        @endif
                    @endauth
                </div>

                <div class="h-4 w-px bg-white"></div>

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

            <!-- Mobile Menu Toggle -->
            <button @click="mobileMenu = !mobileMenu" class="nav-mobile-toggle w-10 h-10 rounded-xl bg-white/5/5 border border-white/10 items-center justify-center text-white transition-all hover:bg-white/5/10 active:scale-95">
                <svg x-show="!mobileMenu" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="mobileMenu" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </nav>

        @if(!$panelType)
        <!-- Mobile Navigation Overlay (Public Site) -->
        <div x-show="mobileMenu" 
             x-cloak
             x-transition.opacity.duration.300ms
             class="flex lg:hidden fixed inset-0 z-[300]">
             
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-[#0a0f2c]/80 backdrop-blur-md"></div>

            {{-- Modal Content --}}
            <div class="relative w-full h-full flex items-center justify-center p-4" @click="mobileMenu = false">
                <div @click.stop
                     :class="mobileMenu ? 'transform translate-y-0 scale-100' : 'transform translate-y-8 scale-95'"
                     class="transition-all duration-300 w-full max-w-sm max-h-[85vh] bg-[#0a0f2c] rounded-[2.5rem] flex flex-col shadow-[0_50px_100px_-20px_rgba(0,0,0,0.5)] overflow-hidden border border-white/10">
                    
                    <!-- Header with Close Button -->
                    <div class="flex items-center justify-between p-6 border-b border-white/10">
                        <div class="text-xl font-black text-white tracking-tighter">
                            MENU
                        </div>
                        <button @click="mobileMenu = false" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white hover:bg-slate-300 transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Scrollable Content -->
                    <div class="flex-grow overflow-y-auto p-6 no-scrollbar">
                        <div class="flex flex-col gap-4">
                            @if(isset($mobileMenu))
                                <div class="vendor-mobile-links">
                                    {{ $mobileMenu }}
                                </div>
                            @endif

                            <div class="flex flex-col gap-3">
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-2 mb-1">Platform</h4>
                                <a href="{{ route('home') }}" class="flex items-center gap-4 px-6 py-4 rounded-2xl bg-white/5 text-white font-black italic uppercase tracking-widest text-[11px] shadow-sm">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    Explore
                                </a>
                                
                                @auth
                                    @if(auth()->user()->isAdmin())
                                        <a href="/admin/dashboard" class="flex items-center gap-4 px-6 py-4 rounded-2xl bg-white/5 text-white font-black italic uppercase tracking-widest text-[11px] shadow-sm">
                                            <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                            Admin Portal
                                        </a>
                                    @elseif(auth()->user()->isVendor())
                                        <a href="/vendor/dashboard" class="flex items-center gap-4 px-6 py-4 rounded-2xl bg-white/5 text-white font-black italic uppercase tracking-widest text-[11px] shadow-sm">
                                            <svg class="w-5 h-5 theme-gradient-text" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            Business Hub
                                        </a>
                                    @endif
                                    
                                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                                        @csrf
                                        <button type="submit" class="w-full h-14 rounded-2xl bg-rose-950/20 text-rose-400 text-[10px] font-black uppercase tracking-[0.2em] italic flex items-center justify-center gap-3 border border-rose-500/20">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            Logout Account
                                        </button>
                                    </form>
                                @else
                                    <div class="grid grid-cols-1 gap-3 mt-4">
                                        <a href="/login" class="flex items-center justify-between h-16 px-8 rounded-2xl bg-white/5 border border-white/10 text-[10px] font-black uppercase tracking-widest text-white shadow-sm transition-all active:scale-[0.98]">
                                            Sign In Portal
                                            <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                        <a href="/register/vendor" class="flex items-center justify-between h-16 px-8 rounded-2xl theme-gradient-bg text-[10px] font-black uppercase tracking-widest text-white shadow-xl shadow-orange-500/20 transition-all active:scale-[0.98]">
                                            Become a Provider
                                            <svg class="w-4 h-4 text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </div>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- Mobile Navigation Slide-in Sidebar (Admin/Vendor Dashboards) -->
        <div x-show="mobileMenu"
             x-cloak
             x-transition.opacity.duration.300ms
             class="block lg:hidden fixed inset-0 z-[9999]">
             
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-[#0a0f2c]/60 backdrop-blur-sm" @click="mobileMenu = false"></div>

            <!-- Slide-in Menu -->
            <div @click.stop
                 :class="mobileMenu ? 'transform translate-x-0' : 'transform -translate-x-full'"
                 class="transition-all duration-300 dashboard-mobile-sidebar bg-[#0a0f2c] border-r border-white/5 flex flex-col">
                 
                 <!-- Header with Close Button -->
                 <div class="flex items-center justify-between p-6 border-b border-white/5">
                     <div class="text-xs font-black text-white tracking-widest uppercase">
                         Navigation
                     </div>
                     <button @click="mobileMenu = false" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-white hover:bg-white/10 transition-colors">
                         <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                     </button>
                 </div>

                 <!-- Scrollable Content -->
                 <div class="flex-grow overflow-y-auto p-6 no-scrollbar" @click="if ($event.target.closest('a')) mobileMenu = false">
                     @if(isset($mobileMenu))
                         {{ $mobileMenu }}
                     @endif

                     <div class="h-px bg-white/5 my-4"></div>
                     <a href="{{ route('home') }}" class="flex items-center gap-4 px-6 py-4 rounded-xl bg-white/5 text-white font-black italic uppercase tracking-widest text-[11px] shadow-sm mb-4">
                         Explore
                     </a>

                     @auth
                         <form method="POST" action="{{ route('logout') }}">
                             @csrf
                             <button type="submit" class="w-full h-12 rounded-xl bg-rose-950/20 text-rose-400 text-[10px] font-black uppercase tracking-[0.2em] italic flex items-center justify-center gap-3">
                                 Logout
                             </button>
                         </form>
                     @endauth
                 </div>
            </div>
        </div>
        @endif

        <!-- Page Content -->
        <main class="flex-grow">
            {{ $slot }}
        </main>

        @if(!$panelType)
        <!-- Footer (Section 12) - Hidden on Dashboards -->
        <footer class="bg-[#0a0f2c] pt-24 pb-12 border-t border-white/5">
            <div class="container mx-auto px-4 md:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-10 mb-16 px-4">
                    <div class="flex flex-col items-center md:items-start gap-4">
                        <div class="text-3xl font-black text-white tracking-tighter">BOOK<span class="theme-gradient-text">AI</span></div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.3em] max-w-sm text-center md:text-left text-white/30 leading-loose">
                            The Next-Generation Multi-Vendor Booking Experience for Global Professionals.
                        </p>
                    </div>
                    
                    <div class="flex flex-wrap justify-center gap-8 md:gap-10 text-[9px] font-black uppercase tracking-[0.3em] text-white/40">
                        <a href="#" class="hover:text-[var(--theme-primary)] transition-colors">Privacy</a>
                        <a href="#" class="hover:text-[var(--theme-primary)] transition-colors">Terms</a>
                        <a href="#" class="hover:text-[var(--theme-primary)] transition-colors">Help</a>
                        <a href="#" class="hover:text-[var(--theme-primary)] transition-colors">Contact</a>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row items-center justify-between pt-12 border-t border-white/5 gap-6">
                    <div class="text-[10px] font-black uppercase tracking-[0.4em] text-white/20">
                        &copy; {{ date('Y') }} BOOKAI PLATFORM. ALL RIGHTS RESERVED.
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="w-8 h-8 rounded-lg bg-white/5/5 flex items-center justify-center text-white/40 hover:text-white transition-colors cursor-pointer border border-white/10">𝕏</div>
                        <div class="w-8 h-8 rounded-lg bg-white/5/5 flex items-center justify-center text-white/40 hover:text-white transition-colors cursor-pointer border border-white/10">📸</div>
                        <div class="w-8 h-8 rounded-lg bg-white/5/5 flex items-center justify-center text-white/40 hover:text-white transition-colors cursor-pointer border border-white/10">💼</div>
                    </div>
                </div>
            </div>
        </footer>
        @endif
    </div>

    <!-- Notification Sound -->
    <audio id="notification-sound" src="{{ asset('audio/notification.wav') }}" preload="auto"></audio>

    <!-- Toast Notifications -->
    <div x-data="{
        show: false, message: '', type: 'success', timer: null,
        triggerToast(msg, type = 'success') {
            this.message = msg; this.type = type; this.show = true;
            
            let sound = document.getElementById('notification-sound');
            if (sound) {
                sound.currentTime = 0;
                let playPromise = sound.play();
                if (playPromise !== undefined) {
                    playPromise.catch(error => console.log('Audio playback prevented by browser:', error));
                }
            }

            if(this.timer) clearTimeout(this.timer);
            this.timer = setTimeout(() => this.show = false, 4000);
        },
        init() {
            @if(session('success'))
                setTimeout(() => this.triggerToast('{{ addslashes(session('success')) }}', 'success'), 100);
            @endif
            @if(session('error'))
                setTimeout(() => this.triggerToast('{{ addslashes(session('error')) }}', 'error'), 100);
            @endif
            @if($errors->any())
                @php
                    $allErrors = implode(' | ', $errors->all());
                @endphp
                setTimeout(() => this.triggerToast('{{ addslashes($allErrors) }}', 'error'), 100);
            @endif
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
            <div class="w-10 h-10 rounded-2xl bg-white/5/20 flex items-center justify-center shrink-0">
                <template x-if="type === 'success'"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></template>
                <template x-if="type === 'error'"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg></template>
            </div>
            <p class="font-black text-sm tracking-tight" x-text="message"></p>
        </div>
    </div>

    @livewireScripts

    <script>
        // Define permission handler FIRST to guarantee it is available even if FCM fails
        window.__requestNotificationPermission = function() {
            if (!('Notification' in window)) {
                console.log('This browser does not support desktop notification');
                return;
            }
            
            Notification.requestPermission().then((permission) => {
                if (permission === 'granted') {
                    console.log('Notification permission granted.');
                    if (typeof window.__registerFcmTokenSilently === 'function') {
                        window.__registerFcmTokenSilently();
                    }
                } else {
                    console.log('Unable to get permission to notify.');
                }
            });
        };

        try {
            // Firebase initialization
            const firebaseConfig = {
                apiKey: "{{ env('FIREBASE_API_KEY', 'YOUR_API_KEY') }}",
                projectId: "ebooking-b2c07",
                messagingSenderId: "{{ env('FIREBASE_MESSAGING_SENDER_ID', '100739474622') }}",
                appId: "{{ env('FIREBASE_APP_ID', 'YOUR_APP_ID') }}"
            };

            if (!firebase.apps.length) {
                firebase.initializeApp(firebaseConfig);
            }

            let messaging = null;
            if (firebase.messaging.isSupported()) {
                messaging = firebase.messaging();
            } else {
                console.warn('Firebase Messaging is not supported by this browser.');
            }

            // Register FCM token WITHOUT prompting — only if permission was already granted
            window.__registerFcmTokenSilently = function() {
                if (!messaging) return;
                
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.register('/firebase-messaging-sw.js?v=5')
                        .then((registration) => {
                            messaging.useServiceWorker(registration);
                            return messaging.getToken({ vapidKey: "{{ env('FIREBASE_VAPID_KEY', 'YOUR_VAPID_KEY') }}" });
                        })
                        .then((currentToken) => {
                            if (currentToken) {
                                console.log('FCM Token:', currentToken);
                                fetch("{{ route('fcm.token.save') }}", {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({ token: currentToken })
                                }).then(res => res.json()).then(data => {
                                    console.log('Token saved to server.', data);
                                }).catch((err) => {
                                    console.log('Unable to save token to server.', err);
                                });
                            }
                        })
                        .catch((err) => {
                            console.log('An error occurred while retrieving token. ', err);
                        });
                }
            };

            if (messaging) {
                // On foreground message
                messaging.onMessage((payload) => {
                    console.log('Message received. ', payload);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: payload.notification.title + ': ' + payload.notification.body, type: 'info' } }));
                });
            }

            // Listen to messages from the Service Worker (e.g. background notification audio requests)
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.addEventListener('message', function(event) {
                    if (event.data && event.data.type === 'PLAY_NOTIFICATION_SOUND') {
                        console.log('Received notification sound request from Service Worker');
                        let sound = document.getElementById('notification-sound');
                        if (sound) {
                            sound.currentTime = 0;
                            let playPromise = sound.play();
                            if (playPromise !== undefined) {
                                playPromise.catch(error => console.log('Audio playback prevented by browser:', error));
                            }
                        }
                    }
                });
            }

            // If permission was already granted previously, silently register the token — NO prompt
            if ('Notification' in window && Notification.permission === 'granted') {
                setTimeout(() => {
                    if (typeof window.__registerFcmTokenSilently === 'function') {
                        window.__registerFcmTokenSilently();
                    }
                }, 2000);
            }
            
        } catch (error) {
            console.error('Firebase initialization or messaging setup failed:', error);
        }
    </script>
</body>
</html>
