<x-app-layout>
    <div class="flex items-center justify-center min-h-[80vh]">
        <div class="glass-card w-full max-w-md p-10 relative overflow-hidden text-center">
            <div class="absolute -top-10 -left-10 w-32 h-32 bg-purple-500/20 blur-3xl rounded-full"></div>
            
            <div class="w-20 h-20 bg-primary-500/20 text-primary-400 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-1.34-1.34c-1.274-1.274-2.316-2.73-3.141-4.305m3.753-1.455c-1.34-1.34-2.382-2.796-3.207-4.371M12 11a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" />
                </svg>
            </div>

            <h2 class="text-3xl font-bold mb-2">Verify OTP</h2>
            <p class="text-gray-400 mb-8">We've sent a 6-digit code to your mobile number.</p>

            @if(session('success'))
                <div class="bg-green-500/10 border border-green-500/20 p-2 mb-4 text-green-400 text-sm italic">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="/verify-otp" class="space-y-6">
                @csrf
                
                <div>
                    <input type="text" name="otp" required maxlength="6" class="w-full glass-input text-center text-3xl font-black tracking-[1em]" placeholder="000000" autofocus>
                    @error('otp')
                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-primary w-full py-4 text-lg">
                    Verify & Continue
                </button>

                <div class="pt-4">
                    <a href="/resend-otp" class="text-sm text-primary-400 hover:underline">Didn't receive code? Resend OTP</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
