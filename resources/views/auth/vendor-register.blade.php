<x-app-layout>
    <div class="max-w-4xl mx-auto py-12">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-black mb-4">Start Your Business with <span class="bg-gradient-to-r from-primary-400 to-primary-600 bg-clip-text text-transparent italic">BOOKAI</span></h1>
            <p class="text-gray-400">Join thousands of vendors using AI-powered booking systems.</p>
        </div>

        <form method="POST" action="/register/vendor" class="space-y-8">
            @csrf
            
            <div class="glass-card p-10">
                <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center text-sm">1</span>
                    Basic Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Business Name</label>
                        <input type="text" name="business_name" value="{{ old('business_name') }}" required class="w-full glass-input" placeholder="e.g. Modern Hair Saloon">
                        @error('business_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Owner Name</label>
                        <input type="text" name="owner_name" value="{{ old('owner_name') }}" required class="w-full glass-input" placeholder="e.g. John Doe">
                        @error('owner_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full glass-input" placeholder="john@example.com">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Mobile Number</label>
                        <input type="text" name="mobile" value="{{ old('mobile') }}" required class="w-full glass-input" placeholder="+91 9876543210">
                        @error('mobile') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Password</label>
                        <input type="password" name="password" required class="w-full glass-input" placeholder="••••••••">
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation" required class="w-full glass-input" placeholder="••••••••">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Referral Code (Optional)</label>
                        <input type="text" name="referral_code" value="{{ old('referral_code', request('ref')) }}" class="w-full glass-input" placeholder="VND-XXXXXXXX">
                        @error('referral_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="glass-card p-10">
                <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center text-sm">2</span>
                    Select Subscription Plan
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($plans as $plan)
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="subscription_plan_id" value="{{ $plan->id }}" class="peer sr-only" {{ $loop->first ? 'checked' : '' }}>
                            <div class="glass-card p-6 border-white/5 peer-checked:border-primary-500/50 peer-checked:bg-primary-500/5 transition-all group-hover:bg-white/5">
                                <h4 class="font-bold text-lg">{{ $plan->name }}</h4>
                                <p class="text-2xl font-black my-2">₹{{ number_format($plan->price) }}</p>
                                <p class="text-xs text-gray-500">Up to {{ $plan->max_employees }} employees</p>
                                
                                <div class="absolute top-4 right-4 text-primary-500 hidden peer-checked:block">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">By registerting you agree to our Terms of Service.</p>
                <button type="submit" class="btn-primary px-12">
                    Create Account & Continue
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
