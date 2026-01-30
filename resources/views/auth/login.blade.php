<x-app-layout>
    <div class="flex items-center justify-center min-h-[80vh]">
        <div class="glass-card w-full max-w-md p-10 relative overflow-hidden">
            <!-- Decorative blur -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-primary-500/20 blur-3xl rounded-full"></div>
            
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold mb-2">Welcome Back</h2>
                <p class="text-gray-400">Login to manage your bookings</p>
            </div>

            <form method="POST" action="/login" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Email Address</label>
                    <input type="email" name="email" required class="w-full glass-input" placeholder="admin@gmail.com">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Password</label>
                    <input type="password" name="password" required class="w-full glass-input" placeholder="••••••••">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" class="rounded border-white/10 bg-white/5 text-primary-500 focus:ring-primary-500">
                        <span class="text-gray-400">Remember me</span>
                    </label>
                    <a href="#" class="text-primary-400 hover:underline">Forgot password?</a>
                </div>

                <button type="submit" class="btn-primary w-full py-4 text-lg">
                    Sign In
                </button>

                <p class="text-center text-sm text-gray-400">
                    Don't have an account? 
                    <a href="/register" class="text-primary-400 font-bold hover:underline">Sign up</a>
                </p>
            </form>
        </div>
    </div>
</x-app-layout>
