<x-app-layout>
    <div class="flex items-center justify-center min-h-[80vh] py-10">
        <div class="glass-card w-full max-w-md p-10 relative overflow-hidden">
            <!-- Decorative blur -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-primary-500/20 blur-3xl rounded-full"></div>
            
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold mb-2">Business Registration</h2>
                <p class="text-gray-400">List your salon/shop and start accepting bookings</p>
            </div>

            <div class="space-y-6">
                <p class="text-white text-center font-medium">Are you a service provider? Create your business account below.</p>
                
                <a href="/register/vendor" class="btn-primary w-full py-4 text-center block text-lg font-bold">
                    Register as Vendor
                </a>

                <div class="relative flex items-center justify-center my-6">
                    <div class="border-t border-white/10 w-full"></div>
                    <span class="bg-[#0f172a] px-4 text-xs text-gray-500 uppercase tracking-widest absolute">Booking as a Guest?</span>
                </div>

                <p class="text-center text-sm text-gray-400">
                    Customers do not need an account. Simply visit any vendor page and book directly!
                </p>

                <a href="/" class="btn-outline w-full py-3 flex items-center justify-center gap-2">
                    Browse All Vendors
                </a>

                <p class="text-center text-sm text-gray-400 mt-8">
                    Already have a business account? 
                    <a href="/login" class="text-primary-400 font-bold hover:underline">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
