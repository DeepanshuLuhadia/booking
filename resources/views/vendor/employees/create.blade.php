<x-vendor-layout>
    <div class="max-w-2xl">
        <h1 class="text-4xl font-black mb-8">Add Professional</h1>
        
        <form action="{{ route('vendor.employees.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            
            <div class="glass-card p-10">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Full Name</label>
                        <input type="text" name="name" required class="w-full glass-input" placeholder="e.g. Rahul Sharma">
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm text-gray-400 mb-2">Start Time</label>
                            <input type="time" name="working_start_time" required class="w-full glass-input" value="09:00">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-2">End Time</label>
                            <input type="time" name="working_end_time" required class="w-full glass-input" value="20:00">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Service Fee Override (₹)</label>
                        <input type="number" name="service_fee_override" step="0.01" class="w-full glass-input" placeholder="Leave empty to use shop default">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Slot Duration (Minutes)</label>
                        <input type="number" name="slot_duration" value="45" required class="w-full glass-input">
                    </div>
                </div>

                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Profile Photo (Optional)</label>
                        <input type="file" name="photo" class="w-full glass-input">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="btn-primary px-12">Save Professional</button>
                <a href="{{ route('vendor.employees.index') }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</x-vendor-layout>
