<x-admin-layout>
    <div class="space-y-10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-col gap-2">
                <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white dark:text-white">Vendors Management</h2>
                <p class="text-xs md:text-sm font-medium text-slate-400 uppercase tracking-widest">Manage and approve platform business accounts</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            @foreach(['all','pending','active','suspended','rejected'] as $s)
                <a href="{{ route('admin.vendors.index', ['status' => $s]) }}"
                   class="px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all {{ ($status ?? 'all') === $s ? 'bg-white text-black' : 'bg-white/10 text-white/60 hover:bg-white/20' }}">
                    {{ $s }}
                    @if($s === 'pending' && ($pendingCount ?? 0) > 0)
                        <span class="ml-1 bg-red-500 text-white text-[9px] rounded-full px-1.5 py-0.5">{{ $pendingCount }}</span>
                    @endif
                </a>
            @endforeach
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
                                    @php
                                        $statusColor = match($vendor->status) {
                                            'active'    => 'bg-emerald-50 text-emerald-600',
                                            'pending'   => 'bg-amber-50 text-amber-600',
                                            'suspended' => 'bg-orange-50 text-orange-600',
                                            'rejected'  => 'bg-rose-50 text-rose-600',
                                            default     => 'bg-white/10 text-slate-300',
                                        };
                                    @endphp
                                    <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $statusColor }}">
                                        {{ $vendor->status }}
                                    </span>
                                    @if($vendor->is_verified)
                                        <span class="inline-flex ml-1 px-2 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-blue-50 text-blue-600">✓ Verified</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex justify-end items-center gap-2">
                                        <a href="{{ route('admin.vendors.show', $vendor) }}" class="btn-primary py-2 px-3 text-[10px] font-black uppercase tracking-widest rounded-lg">
                                            View
                                        </a>
                                        @if(in_array($vendor->status, ['pending', 'suspended', 'rejected']))
                                            <form action="{{ route('admin.vendors.approve', $vendor) }}" method="POST" class="m-0">
                                                @csrf
                                                <button type="submit" class="py-2 px-3 text-[10px] font-black uppercase tracking-widest rounded-lg bg-emerald-500 text-white hover:bg-emerald-600 transition-colors">
                                                    Approve
                                                </button>
                                            </form>
                                        @endif
                                        @if($vendor->status === 'active')
                                            <form action="{{ route('admin.vendors.suspend', $vendor) }}" method="POST" class="m-0">
                                                @csrf
                                                <button type="submit" class="btn-outline py-2 px-3 text-[10px] font-black uppercase tracking-widest rounded-lg">
                                                    Suspend
                                                </button>
                                            </form>
                                        @endif
                                        @if($vendor->status === 'pending')
                                            <form action="{{ route('admin.vendors.reject', $vendor) }}" method="POST" class="m-0">
                                                @csrf
                                                <button type="submit" class="py-2 px-3 text-[10px] font-black uppercase tracking-widest rounded-lg border border-rose-500/20 text-rose-500 hover:bg-rose-500 hover:text-white transition-colors">
                                                    Reject
                                                </button>
                                            </form>
                                        @endif
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
