<x-vendor-layout>
    <div x-data="{ 
        photoPreview: '{{ $vendor->shop_photo ? asset('storage/' . $vendor->shop_photo) : '' }}',
        handleFileChange(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.photoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
    }">
        <h1 class="text-4xl font-black mb-8">Shop Settings</h1>

        <form action="{{ route('vendor.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-8">
                    <div class="glass-card p-8 space-y-6">
                        <h3 class="text-xl font-bold border-b border-white/5 pb-4">Business Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm text-gray-400 mb-2">Business Name</label>
                                <input type="text" name="business_name" value="{{ $vendor->business_name }}" required class="w-full glass-input">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-2">Owner Name</label>
                                <input type="text" name="owner_name" value="{{ $vendor->owner_name }}" required class="w-full glass-input">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-400 mb-2">Address</label>
                            <textarea name="address" rows="3" required class="w-full glass-input">{{ $vendor->address }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm text-gray-400 mb-2">Latitude</label>
                                <input type="text" name="latitude" value="{{ $vendor->latitude }}" class="w-full glass-input">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-2">Longitude</label>
                                <input type="text" name="longitude" value="{{ $vendor->longitude }}" class="w-full glass-input">
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-8 space-y-6">
                        <h3 class="text-xl font-bold border-b border-white/5 pb-4">Booking Preferences</h3>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-bold">Enable Token Booking</h4>
                                <p class="text-xs text-gray-500">Require customers to pay a small token amount to confirm slot.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="token_booking_enabled" value="1" {{ $vendor->token_booking_enabled ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm text-gray-400 mb-2">Token Amount (₹)</label>
                                <input type="number" name="token_amount" value="{{ $vendor->token_amount }}" min="0" class="w-full glass-input" placeholder="e.g. 100">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-2">UPI ID for Settlements</label>
                                <input type="text" name="upi_id" value="{{ $vendor->upi_id }}" class="w-full glass-input" placeholder="e.g., yourname@paytm">
                                <p class="text-[10px] text-gray-500 mt-1">Required to receive settlement payments.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm text-gray-400 mb-2">Service Fee (₹)</label>
                                <input type="number" name="service_fee" value="{{ $vendor->service_fee }}" min="0" class="w-full glass-input" placeholder="e.g. 50">
                                <p class="text-[10px] text-gray-500 mt-1">Default fee for all bookings.</p>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-2">Premium/Express Fee (₹)</label>
                                <input type="number" name="emergency_fee" value="{{ $vendor->emergency_fee }}" min="0" class="w-full glass-input" placeholder="e.g. 200">
                                <p class="text-[10px] text-gray-500 mt-1">Extra fee for urgent/premium slots.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-8">
                    <!-- Shop Photo with Preview -->
                    <div class="glass-card p-8">
                        <h3 class="text-xl font-bold border-b border-white/5 pb-4 mb-6">Shop Photo</h3>
                        
                        <div class="relative w-full aspect-video rounded-xl overflow-hidden mb-6 bg-white/5 border-2 border-dashed border-white/10 flex items-center justify-center">
                            <template x-if="photoPreview">
                                <img :src="photoPreview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!photoPreview">
                                <span class="text-gray-500">No photo selected</span>
                            </template>
                        </div>

                        <input type="file" name="shop_photo" id="shop_photo_input" class="hidden" accept="image/*" @change="handleFileChange($event)">
                        <button type="button" @click="document.getElementById('shop_photo_input').click()" class="btn-outline w-full py-3">
                            <span x-text="photoPreview ? 'Change Photo' : 'Select Photo'"></span>
                        </button>
                    </div>

                    <div class="glass-card p-8 bg-gradient-to-br from-primary-600/10 to-transparent border-primary-500/20">
                        <h3 class="text-xl font-bold mb-2">Subscription</h3>
                        <p class="text-sm text-gray-400 mb-6">Your current active plan.</p>
                        
                        <div class="p-4 bg-black/30 rounded-xl border border-white/5 mb-6">
                            <div class="text-xs text-gray-500 uppercase font-black">Current Plan</div>
                            <div class="text-2xl font-black text-primary-400">{{ $vendor->subscriptionPlan->name }}</div>
                        </div>

                        <ul class="space-y-3 mb-8 text-sm">
                            @foreach($vendor->subscriptionPlan->features as $feature)
                                <li class="flex items-center gap-3 text-gray-300">
                                    <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>

                        <button type="button" onclick="window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'UPGRADE MODULE OFFLINE - CONTACT SUPPORT', type: 'info' } }))" class="btn-primary w-full">Upgrade Plan</button>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="btn-primary w-full py-4 text-xl">Save All Settings</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-vendor-layout>
