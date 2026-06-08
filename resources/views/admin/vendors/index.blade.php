<x-admin-layout>
    <div class="space-y-10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-col gap-2">
                <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white dark:text-white">Vendors Management</h2>
                <p class="text-xs md:text-sm font-medium text-slate-400 uppercase tracking-widest">Manage and approve platform business accounts</p>
            </div>
        </div>

        <div class="glass-card overflow-hidden">
            <div class="table-responsive-wrapper">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/10 bg-white/5/50">
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Business Name</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 hidden sm:table-cell">Owner</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 hidden md:table-cell">Plan</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vendors as $vendor)
                            <tr class="border-b border-white/10 hover:bg-white/5/30 transition-all">
                                <td class="p-4">
                                    <div class="font-black text-white text-sm">{{ $vendor->business_name }}</div>
                                    <div class="text-[10px] font-bold text-slate-400 mt-0.5 tracking-wider uppercase">{{ $vendor->slug }}</div>
                                </td>
                                <td class="p-4 text-slate-400 font-semibold text-xs hidden sm:table-cell">{{ $vendor->owner_name }}</td>
                                <td class="p-4 hidden md:table-cell">
                                    <span class="inline-flex bg-white/10 text-slate-200 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest">
                                        {{ $vendor->subscriptionPlan->name }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $vendor->status == 'active' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                                        {{ $vendor->status }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex justify-end items-center gap-2">
                                        <a href="{{ route('admin.vendors.show', $vendor) }}" class="btn-primary py-2 px-3 text-[10px] font-black uppercase tracking-widest rounded-lg">
                                            View
                                        </a>
                                        <form action="{{ route('admin.vendors.update', $vendor) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="{{ $vendor->status == 'active' ? 'inactive' : 'active' }}">
                                            <button type="submit" class="btn-outline py-2 px-3 text-[10px] font-black uppercase tracking-widest rounded-lg">
                                                {{ $vendor->status == 'active' ? 'Suspend' : 'Activate' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.vendors.destroy', $vendor) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 border border-red-500/20 text-red-500 hover:text-white rounded-lg hover:bg-red-500 transition-colors duration-150 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" viewBox="0 0 20 20" fill="currentColor">
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
    </div>
</x-admin-layout>
