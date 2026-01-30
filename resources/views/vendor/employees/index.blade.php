<x-vendor-layout>
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-4xl font-black">Manage Staff</h1>
            <p class="text-gray-400">Team members and their availability.</p>
        </div>
        <a href="{{ route('vendor.employees.create') }}" class="btn-primary flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Professional
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @forelse($employees as $employee)
            <div class="glass-card group p-6 hover:scale-[1.02] transition-all">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 overflow-hidden flex items-center justify-center font-bold text-2xl">
                        @if($employee->photo)
                            <img src="{{ asset('storage/' . $employee->photo) }}" class="w-full h-full object-cover">
                        @else
                            {{ substr($employee->name, 0, 1) }}
                        @endif
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">{{ $employee->name }}</h3>
                        <span class="px-2 py-0.5 {{ $employee->is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }} rounded text-[10px] font-bold uppercase tracking-widest">
                            {{ $employee->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                <div class="space-y-3 mb-8">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Working Hours</span>
                        <span class="text-gray-200">{{ \Carbon\Carbon::parse($employee->working_start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($employee->working_end_time)->format('h:i A') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Slot Duration</span>
                        <span class="text-gray-200 font-bold text-primary-400">{{ $employee->slot_duration }} mins</span>
                    </div>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('vendor.employees.edit', $employee) }}" class="flex-grow btn-outline text-center py-2 text-sm">Edit Profile</a>
                    <form action="{{ route('vendor.employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('Remove worker?')">
                        @csrf @method('DELETE')
                        <button class="p-2 border border-red-500/20 text-red-400 rounded-xl hover:bg-red-500/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full glass-card p-20 text-center border-dashed border-white/10">
                <p class="text-gray-500 mb-6 font-medium">No team members joined your shop yet.</p>
                <a href="{{ route('vendor.employees.create') }}" class="btn-primary">Add Your First Professional</a>
            </div>
        @endforelse
    </div>
</x-vendor-layout>
