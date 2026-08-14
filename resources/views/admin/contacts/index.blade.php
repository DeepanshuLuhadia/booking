<x-admin-layout>
    <div class="space-y-10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-col gap-2">
                <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white">Contact Enquiries</h2>
                <p class="text-xs md:text-sm font-medium text-slate-400 uppercase tracking-widest">Everyone who reached out through the website contact form</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="glass-card px-5 py-3 text-center">
                    <div class="text-xl font-black text-white">{{ $totalCount }}</div>
                    <div class="text-[9px] font-black uppercase tracking-widest text-slate-500">Total</div>
                </div>
                <div class="glass-card px-5 py-3 text-center">
                    <div class="text-xl font-black text-sky-400">{{ $newCount }}</div>
                    <div class="text-[9px] font-black uppercase tracking-widest text-slate-500">Unread</div>
                </div>
                <div class="glass-card px-5 py-3 text-center">
                    <div class="text-xl font-black text-emerald-400">{{ $repliedCount }}</div>
                    <div class="text-[9px] font-black uppercase tracking-widest text-slate-500">Replied</div>
                </div>
            </div>
        </div>

        <!-- Filters + search -->
        <div class="flex flex-col lg:flex-row lg:items-center gap-4 justify-between">
            <div class="flex flex-wrap gap-2">
                @foreach(['all' => 'All', 'new' => 'Unread', 'read' => 'Opened', 'replied' => 'Replied', 'closed' => 'Closed'] as $key => $label)
                    <a href="{{ route('admin.contacts.index', array_filter(['filter' => $key, 'q' => $search])) }}"
                       class="px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all {{ $filter === $key ? 'bg-white text-black' : 'bg-white/10 text-white/60 hover:bg-white/20' }}">
                        {{ $label }}
                        @if($key === 'new' && $newCount > 0)
                            <span class="ml-1 bg-sky-500 text-white text-[9px] rounded-full px-1.5 py-0.5">{{ $newCount }}</span>
                        @endif
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('admin.contacts.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="text" name="q" value="{{ $search }}" placeholder="Search name, email or subject"
                       class="h-11 w-full lg:w-72 px-4 bg-white/5 border border-white/10 rounded-xl text-sm font-semibold text-white placeholder:text-white/30 focus:bg-white/10 focus:outline-none">
                <button type="submit" class="h-11 px-5 rounded-xl bg-white/10 border border-white/10 text-[10px] font-black uppercase tracking-widest text-white hover:bg-white/20 transition-all">Search</button>
                @if($search !== '')
                    <a href="{{ route('admin.contacts.index', ['filter' => $filter]) }}" class="h-11 px-4 flex items-center rounded-xl text-[10px] font-black uppercase tracking-widest text-white/40 hover:text-white">Clear</a>
                @endif
            </form>
        </div>

        <div class="glass-card overflow-hidden">
            <div class="table-responsive-wrapper">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/10 bg-white/5/50">
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">From</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Subject</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 hidden md:table-cell">Message</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $message)
                            <tr class="border-b border-white/10 hover:bg-white/5/30 transition-all {{ $message->status === 'new' ? 'bg-sky-500/5' : '' }}">
                                <td class="p-4 align-top">
                                    <div class="font-black text-white text-sm">{{ $message->name }}</div>
                                    <a href="mailto:{{ $message->email }}" class="text-[11px] font-semibold text-sky-400 hover:underline break-all">{{ $message->email }}</a>
                                    @if($message->phone)
                                        <div class="text-[10px] font-bold text-slate-500 mt-0.5">{{ $message->phone }}</div>
                                    @endif
                                    <div class="text-[10px] font-bold text-slate-600 mt-1">{{ $message->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="p-4 align-top">
                                    <div class="font-bold text-slate-200 text-xs max-w-[220px]">{{ $message->subject }}</div>
                                </td>
                                <td class="p-4 align-top hidden md:table-cell max-w-sm">
                                    <p class="text-slate-400 text-xs leading-relaxed">{{ \Illuminate\Support\Str::limit($message->message, 140) }}</p>
                                </td>
                                <td class="p-4 align-top">
                                    <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border {{ $message->statusClasses() }}">
                                        {{ $message->status }}
                                    </span>
                                </td>
                                <td class="p-4 align-top text-right">
                                    <div class="flex justify-end items-center gap-2">
                                        <a href="{{ route('admin.contacts.show', $message) }}"
                                           class="btn-primary py-2 px-3 text-[10px] font-black uppercase tracking-widest rounded-lg">
                                            Open
                                        </a>
                                        <form action="{{ route('admin.contacts.destroy', $message) }}" method="POST" onsubmit="return confirm('Delete this enquiry permanently?')" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 border border-red-500/20 text-red-500 hover:text-white rounded-lg hover:bg-red-500 transition-colors duration-150 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-12 text-center text-slate-500 font-black uppercase tracking-widest text-xs italic">
                                    No enquiries {{ $search !== '' ? 'match that search' : 'yet' }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-admin-pagination :paginator="$messages" label="enquiries" />
        </div>
    </div>
</x-admin-layout>
