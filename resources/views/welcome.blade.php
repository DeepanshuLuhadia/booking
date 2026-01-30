<x-app-layout>
    <div class="relative py-20 overflow-hidden">
        <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-6xl md:text-8xl font-black mb-6 tracking-tight animate-float">
                Book Next-Gen <br/>
                <span class="bg-gradient-to-r from-primary-400 via-blue-500 to-purple-600 bg-clip-text text-transparent">
                    Appointments
                </span>
            </h1>
            <p class="text-xl text-gray-400 max-w-2xl mx-auto mb-12">
                Discover local vendors, scan QR codes, and book same-day slots powered by AI-optimized scheduling for 2026.
            </p>
            
            <div class="flex flex-col md:flex-row items-center justify-center gap-6">
                <a href="/register" class="btn-primary flex items-center gap-2 group">
                    Register as Vendor 
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="#" class="btn-outline">Find Nearby Shops</a>
            </div>
        </div>

        <!-- Featured Section -->
        <div class="mt-32 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="glass-card p-8 group hover:scale-[1.02] transition-all">
                <div class="w-12 h-12 bg-primary-500/20 rounded-lg flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold mb-4">Instant Booking</h3>
                <p class="text-gray-400">Save time with real-time slot generation and 2-hour advance booking logic.</p>
            </div>
            
            <div class="glass-card p-8 group hover:scale-[1.02] transition-all">
                <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold mb-4">QR Integration</h3>
                <p class="text-gray-400">Scan vendor QR codes to land directly on their booking page. No app install needed.</p>
            </div>

            <div class="glass-card p-8 group hover:scale-[1.02] transition-all">
                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold mb-4">Secure Payments</h3>
                <p class="text-gray-400">Integrated Razorpay for subscriptions, token bookings, and emergency fees.</p>
            </div>
        </div>
    </div>
</x-app-layout>
