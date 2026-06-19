<x-vendor-layout>
    <div class="flex flex-col md:flex-row items-center justify-between gap-8 mb-12">
        <div>
            <h1 class="text-4xl font-black italic tracking-tight uppercase ">Appointment <span
                    class="text-blue-600">Registry.</span></h1>
            <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] mt-2 italic">HISTORICAL
                TRANSACTION ARCHIVE</p>
        </div>
        <div class="w-full md:w-auto">
            <form id="status-form" action="{{ route('vendor.bookings.index') }}" method="GET">
                <div class="relative" x-data="{
                    open: false,
                    selected: '{{ request('status', '') }}',
                    options: {
                        '': { label: 'Matrix: All States', icon: '🌐' },
                        'confirmed': { label: 'State: Confirmed', icon: '✅' },
                        'completed': { label: 'State: Completed', icon: '🏆' },
                        'cancelled': { label: 'State: Cancelled', icon: '❌' }
                    },
                    get selectedLabel() {
                        return this.options[this.selected]?.label || 'Select State';
                    },
                    get selectedIcon() {
                        return this.options[this.selected]?.icon || '';
                    }
                }" @click.away="open = false">
                    <input type="hidden" name="status" x-model="selected">
                    
                    <div @click="open = !open" 
                         class="w-full md:w-64 h-14 px-6 rounded-xl flex items-center justify-between cursor-pointer transition-all border-2"
                         :class="open ? 'border-blue-600 bg-white/5 ring-4 ring-blue-500/20' : 'border-white/10 bg-white/5'">
                        <div class="flex items-center gap-3">
                            <span x-text="selectedIcon" class="opacity-90 text-base not-italic"></span>
                            <span x-text="selectedLabel" class="text-white text-[10px] font-black italic uppercase tracking-widest"></span>
                        </div>
                        <svg :class="open ? 'rotate-180 text-blue-600' : 'text-slate-300'" class="w-4 h-4 transition-transform duration-300 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                
                    <div x-cloak x-show="open" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="absolute z-[100] w-full mt-2 bg-slate-900 border border-white/10 rounded-2xl p-2 shadow-2xl left-0">
                        
                        <template x-for="(data, key) in options" :key="key">
                            <div @click="selected = key; open = false; $nextTick(() => document.getElementById('status-form').submit())"
                                 class="px-4 py-3 flex items-center gap-3 rounded-lg cursor-pointer transition-all duration-200"
                                 :class="selected === key 
                                     ? 'bg-blue-500/10 border-l-4 border-blue-500 text-blue-500' 
                                     : 'text-white/80 hover:bg-white/5 hover:text-white hover:translate-x-1'">
                                <span x-text="data.icon" class="text-base not-italic"></span>
                                <span x-text="data.label" class="font-black italic text-[10px] uppercase tracking-widest"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="glass-card overflow-hidden">
        <div class="table-responsive-wrapper">
            <table class="w-full">
                <thead class="bg-white/5 text-left">
                    <tr>
                        <th class="px-10 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic">
                            Name</th>
                        <th class="hidden sm:table-cell px-10 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic">
                            Employee</th>
                        <th class="hidden md:table-cell px-10 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic">
                            Time </th>
                        <th class="px-10 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic">
                            Status</th>
                        <th class="px-10 py-6 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($bookings as $booking)
                    <tr class="hover:bg-white/5/50 transition-all">
                        <td class="px-10 py-8">
                            <div class="font-black text-white text-lg tracking-tight uppercase italic">{{
                                $booking->customer_name }}</div>
                            <div class="text-[9px] text-slate-300 font-black mt-1 uppercase tracking-widest italic">{{
                                $booking->customer_phone }}</div>
                        </td>
                        <td class="hidden sm:table-cell px-10 py-8">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center text-xs font-black italic">
                                    {{ substr($booking->employee->name, 0, 1) }}
                                </div>
                                <span class="font-black text-slate-200 text-sm uppercase italic">{{
                                    $booking->employee->name }}</span>
                            </div>
                        </td>
                        <td class="hidden md:table-cell px-10 py-8">
                            <div class="text-sm font-black text-white italic tracking-tight">{{
                                \Carbon\Carbon::parse($booking->booking_date)->format('l, M d, Y') }}</div>
                            <div class="text-[9px] text-blue-600 font-black mt-1 uppercase tracking-widest italic">{{
                                $booking->slot_start_time }} - {{ $booking->slot_end_time }}</div>
                        </td>
                        <td class="px-10 py-8">
                            @php
                            $color = match($booking->status) {
                            'confirmed' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                            'cancelled' => 'bg-rose-50 text-rose-600 border-rose-100',
                            'completed' => 'bg-blue-50 text-blue-600 border-blue-100',
                            default => 'bg-white/5 text-slate-300 border-white/10'
                            };
                            @endphp
                            <span
                                class="px-4 py-1.5 {{ $color }} rounded-lg text-[9px] font-black uppercase border tracking-widest italic">{{
                                $booking->status }}</span>
                        </td>
                        <td class="px-10 py-8 text-right">
                            <div class="flex items-center justify-end gap-3">
                                @if($booking->status === 'confirmed')
                                    <form action="{{ route('vendor.bookings.complete', $booking) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" title="Mark as Complete" class="p-2.5 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all border border-blue-100">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('vendor.bookings.destroy', $booking) }}" method="POST" class="inline" onsubmit="return confirm('ARE YOU SURE YOU WANT TO DELETE THIS ENTRY?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete" class="p-2.5 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-600 hover:text-white transition-all border border-rose-100">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-10 py-32 text-center">
                            <div class="flex flex-col items-center opacity-10">
                                <svg class="h-20 w-20 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-2xl font-black uppercase tracking-[0.3em] italic">Archive Depleted</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bookings->hasPages())
        <style>
            .pagination-container nav [role="navigation"] {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            /* Always show prev/next navigation */
            .pagination-container nav div:first-child {
                display: flex;
                flex: 1 1 0%;
                align-items: center;
                justify-content: space-between;
            }

            /* Hide the detailed page numbers on mobile, show only prev/next */
            .pagination-container nav div:last-child {
                display: none;
            }

            @media (min-width: 640px) {
                .pagination-container nav div:last-child {
                    display: flex;
                    flex: 1 1 0%;
                    align-items: center;
                    justify-content: center;
                }
            }

            .pagination-container a,
            .pagination-container span[aria-current="page"]>span,
            .pagination-container span[aria-disabled="true"]>span {
                padding: 0.5rem 1rem;
                border: 1px solid rgba(255,255,255,0.1);
                border-radius: 0.75rem;
                font-size: 10px;
                font-weight: 900;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                transition-property: all;
                transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
                transition-duration: 150ms;
            }

            .pagination-container a {
                background-color: rgba(255,255,255,0.05);
                color: rgba(255,255,255,0.7);
                text-decoration: none;
            }

            .pagination-container a:hover {
                background-color: rgba(255,255,255,0.15);
                color: #ffffff;
                border-color: rgba(255,255,255,0.3);
            }

            .pagination-container span[aria-current="page"]>span {
                background-color: #2563EB;
                color: #ffffff;
                border-color: #3b82f6;
            }

            .pagination-container span[aria-disabled="true"]>span {
                background-color: rgba(255,255,255,0.02);
                color: rgba(255,255,255,0.2);
                cursor: not-allowed;
            }

            .pagination-container svg {
                width: 1.25rem;
                height: 1.25rem;
            }
        </style>
        <div class="px-10 py-8 border-t border-slate-50 bg-white/5/20 pagination-container">
            {{ $bookings->links() }}
        </div>
        @endif
    </div>
</x-vendor-layout>