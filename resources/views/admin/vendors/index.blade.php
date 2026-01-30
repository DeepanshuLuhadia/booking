<x-admin-layout>
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <h2 class="text-3xl font-bold">Vendors Management</h2>
            <p class="text-gray-400">Manage and approve platform vendors</p>
        </div>

        <div class="glass-card overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-white/5 bg-white/5">
                        <th class="p-4 font-bold">Business Name</th>
                        <th class="p-4 font-bold">Owner</th>
                        <th class="p-4 font-bold">Plan</th>
                        <th class="p-4 font-bold">Status</th>
                        <th class="p-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vendors as $vendor)
                        <tr class="border-b border-white/5 hover:bg-white/5 transition-all">
                            <td class="p-4">
                                <div class="font-bold">{{ $vendor->business_name }}</div>
                                <div class="text-xs text-gray-500">{{ $vendor->slug }}</div>
                            </td>
                            <td class="p-4">{{ $vendor->owner_name }}</td>
                            <td class="p-4">
                                <span class="bg-primary-500/10 text-primary-400 px-3 py-1 rounded-full text-xs font-bold">
                                    {{ $vendor->subscriptionPlan->name }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $vendor->status == 'active' ? 'bg-green-500/10 text-green-400' : 'bg-yellow-500/10 text-yellow-400' }}">
                                    {{ ucfirst($vendor->status) }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.vendors.show', $vendor) }}" class="btn-primary py-1 px-3 text-xs">
                                        View Details
                                    </a>
                                    <form action="{{ route('admin.vendors.update', $vendor) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="{{ $vendor->status == 'active' ? 'inactive' : 'active' }}">
                                        <button type="submit" class="btn-outline py-1 text-xs">
                                            {{ $vendor->status == 'active' ? 'Suspend' : 'Activate' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.vendors.destroy', $vendor) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 border border-red-500/20 text-red-400 rounded-xl hover:bg-red-500/10">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
