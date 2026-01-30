<x-vendor-layout>
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-4xl font-black">Edit Professional</h1>
        <a href="{{ route('vendor.employees.index') }}" class="btn-outline py-2 px-6 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to List
        </a>
    </div>

    <div class="glass-card p-12 max-w-2xl">
        <form action="{{ route('vendor.employees.update', $employee) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm text-gray-400 mb-2" for="name">Professional Name</label>
                    <input type="text" name="name" value="{{ $employee->name }}" required class="w-full glass-input @error('name') border-red-500 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm text-gray-400 mb-2" for="working_start_time">Shift Start (24h)</label>
                        <input type="time" name="working_start_time" value="{{ \Carbon\Carbon::parse($employee->working_start_time)->format('H:i') }}" required class="w-full glass-input">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-2" for="working_end_time">Shift End (24h)</label>
                        <input type="time" name="working_end_time" value="{{ \Carbon\Carbon::parse($employee->working_end_time)->format('H:i') }}" required class="w-full glass-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Service Fee Override (₹)</label>
                        <input type="number" name="service_fee_override" value="{{ $employee->service_fee_override }}" step="0.01" class="w-full glass-input" placeholder="Leave empty to use shop default">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Slot Duration (Minutes)</label>
                        <input type="number" name="slot_duration" value="{{ $employee->slot_duration }}" required class="w-full glass-input">
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-2">Active Status</label>
                    <select name="is_active" class="w-full glass-input">
                        <option value="1" {{ $employee->is_active ? 'selected' : '' }}>Available for Bookings</option>
                        <option value="0" {{ !$employee->is_active ? 'selected' : '' }}>On Leave / Unavailable</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-2">Profile Photo (Optional)</label>
                    <div class="flex items-center gap-6">
                        @if($employee->photo)
                            <img src="{{ asset('storage/' . $employee->photo) }}" class="w-20 h-20 rounded-xl object-cover">
                        @endif
                        <input type="file" name="photo" class="w-full glass-input">
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="btn-primary px-12 py-3 text-lg">Update Professional</button>
            </div>
        </form>
    </div>
</x-vendor-layout>
