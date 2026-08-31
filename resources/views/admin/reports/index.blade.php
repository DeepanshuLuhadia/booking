<x-admin-layout>
    {{--
        The platform booking report. Same builder as the vendor panel's, one
        level up: the shop multi-select stands in for the vendor's staff filter,
        and the preview carries a Business column because rows from thirty shops
        are otherwise indistinguishable.
    --}}
    <div class="space-y-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex flex-col gap-2">
                <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white">Booking Reports</h2>
                <p class="text-xs md:text-sm font-medium text-slate-400 uppercase tracking-widest">
                    Export appointment data across every shop
                </p>
            </div>
            <div class="px-5 py-3 rounded-2xl bg-white/5 border border-white/10 shrink-0">
                <p class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em]">Reporting Year</p>
                <p class="text-[11px] font-black text-white italic mt-1">1 April → 31 March</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.reports.index') }}" x-data="{ period: '{{ $period }}' }">

            {{-- ── Filters ─────────────────────────────────────────── --}}
            <div class="glass-card p-6 sm:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    <div class="space-y-3">
                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Report Period</label>
                        <select name="period" x-model="period" @change="if (period !== 'custom') $el.form.submit()"
                                class="w-full h-14 px-5 rounded-xl bg-white/5 border-2 border-white/10 text-white text-[11px] font-black uppercase tracking-widest focus:border-blue-600 focus:outline-none">
                            @foreach($periods as $key => $label)
                                <option value="{{ $key }}" class="bg-slate-900" @selected($period === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Booking Type</label>
                        <select name="status" @change="$el.form.submit()"
                                class="w-full h-14 px-5 rounded-xl bg-white/5 border-2 border-white/10 text-white text-[11px] font-black uppercase tracking-widest focus:border-blue-600 focus:outline-none">
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" class="bg-slate-900" @selected($status === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Shops — multi-select. Nothing ticked means the whole
                         platform, so an untouched filter cannot empty the report. --}}
                    @if($allVendors->isNotEmpty())
                        <div class="space-y-3"
                             x-data="vendorPicker(@js($vendorIds), @js($allVendors->pluck('business_name', 'id')))">
                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Shops</label>

                            <div class="relative" @click.away="open = false">
                                <div @click="open = !open"
                                     class="w-full h-14 px-5 rounded-xl bg-white/5 border-2 flex items-center justify-between gap-3 cursor-pointer transition-all"
                                     :class="open ? 'border-blue-600 ring-4 ring-blue-500/20' : 'border-white/10'">
                                    <span class="text-[11px] font-black uppercase tracking-widest truncate"
                                          :class="selected.length ? 'text-white' : 'text-slate-300'"
                                          x-text="label"></span>
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-300"
                                         :class="open ? 'rotate-180 text-blue-600' : 'text-slate-300'"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>

                                <div x-show="open" x-cloak
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 -translate-y-2"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="absolute z-[120] w-full mt-2 bg-slate-900 border border-white/10 rounded-2xl p-2 shadow-2xl left-0">

                                    <div class="px-3 pb-2">
                                        {{-- A platform with hundreds of shops makes a plain
                                             list unusable, so the list filters as you type. --}}
                                        <input type="text" x-model="search" placeholder="Search shops…"
                                               @click.stop
                                               class="w-full h-10 px-3 rounded-lg bg-white/5 border border-white/10 text-white text-[11px] font-bold focus:border-blue-600 focus:outline-none">
                                    </div>

                                    <div class="flex items-center justify-between px-3 py-2 mb-1 border-b border-white/5">
                                        <span class="text-[8px] font-black text-slate-500 uppercase tracking-[0.2em]"
                                              x-text="selected.length ? selected.length + ' of {{ $allVendors->count() }}' : 'All {{ $allVendors->count() }}'"></span>
                                        <button type="button" @click="selected = []"
                                                class="text-[9px] font-black text-blue-500 hover:text-blue-400 uppercase tracking-widest">
                                            Clear
                                        </button>
                                    </div>

                                    <div class="max-h-56 overflow-y-auto custom-scrollbar">
                                        @foreach($allVendors as $shop)
                                            <label class="px-3 py-2.5 flex items-center gap-3 rounded-lg cursor-pointer text-white/80 hover:bg-white/5 hover:text-white transition-all"
                                                   x-show="matches({{ $shop->id }}, @js($shop->business_name))">
                                                <input type="checkbox" name="vendors[]" value="{{ $shop->id }}"
                                                       x-model.number="selected"
                                                       class="w-4 h-4 shrink-0 rounded border-white/20 bg-white/10 accent-blue-600">
                                                <span class="font-black text-[10px] uppercase tracking-widest truncate">{{ $shop->business_name }}</span>
                                            </label>
                                        @endforeach
                                    </div>

                                    <button type="submit"
                                            class="btn-primary w-full mt-2 py-3 rounded-xl text-[9px] font-black uppercase tracking-widest">
                                        Apply Shop Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-3 md:col-span-2 lg:col-span-3" x-show="period === 'custom'" x-cloak>
                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Custom Range</label>
                        <div class="flex items-center gap-3 max-w-xl">
                            <input type="date" name="from" value="{{ $from }}"
                                   class="w-full h-14 px-4 rounded-xl bg-white/5 border-2 border-white/10 text-white text-[11px] font-black focus:border-blue-600 focus:outline-none">
                            <span class="text-slate-500 text-[10px] font-black shrink-0">TO</span>
                            <input type="date" name="to" value="{{ $to }}"
                                   class="w-full h-14 px-4 rounded-xl bg-white/5 border-2 border-white/10 text-white text-[11px] font-black focus:border-blue-600 focus:outline-none">
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5 mt-8 pt-6 border-t border-white/10">
                    <div class="min-w-0">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Selected Period</p>
                        <p class="text-sm font-black text-white mt-1 truncate">{{ $range['label'] }}</p>
                        <p class="text-[10px] font-bold text-slate-400 mt-1 truncate">
                            @if($vendorIds)
                                Shops: {{ $allVendors->whereIn('id', $vendorIds)->pluck('business_name')->join(', ') }}
                            @else
                                All shops ({{ $allVendors->count() }})
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" class="btn-outline px-6 py-3.5 rounded-xl text-[10px] font-black uppercase tracking-widest">
                            Apply
                        </button>
                        <button type="submit" name="format" value="csv"
                                formaction="{{ route('admin.reports.export') }}"
                                class="px-6 py-3.5 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/25 hover:text-white text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download CSV
                        </button>
                        <button type="submit" name="format" value="xlsx"
                                formaction="{{ route('admin.reports.export') }}"
                                class="btn-primary px-6 py-3.5 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download Excel
                        </button>
                    </div>
                </div>
            </div>

            {{-- ── Headline numbers ─────────────────────────────────── --}}
            @php
                $tiles = [
                    ['label' => 'In This Report', 'value' => $summary['total'],     'tone' => 'text-blue-400'],
                    ['label' => 'Shops Covered',  'value' => $summary['vendors'],   'tone' => 'text-white'],
                    ['label' => 'Completed',      'value' => $summary['completed'], 'tone' => 'text-emerald-400'],
                    ['label' => 'Confirmed',      'value' => $summary['confirmed'], 'tone' => 'text-sky-400'],
                    ['label' => 'Cancelled',      'value' => $summary['cancelled'], 'tone' => 'text-rose-400'],
                    ['label' => 'Skipped',        'value' => $summary['skipped'],   'tone' => 'text-amber-400'],
                ];
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mt-6">
                @foreach($tiles as $tile)
                    <div class="glass-card p-5">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">{{ $tile['label'] }}</p>
                        <h3 class="text-3xl font-black {{ $tile['tone'] }} leading-none">{{ number_format($tile['value']) }}</h3>
                    </div>
                @endforeach
            </div>

            <div class="glass-card p-5 mt-4 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Collected Online — This Report</p>
                    <h3 class="text-3xl font-black text-white leading-none">₹{{ number_format($summary['revenue'], 2) }}</h3>
                </div>
                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest text-right max-w-xs">
                    Token &amp; emergency fees paid through the app. Cash taken at the counter is not tracked here.
                </p>
            </div>
        </form>

        {{-- ── Preview ──────────────────────────────────────────────── --}}
        <div class="glass-card overflow-hidden">
            <div class="p-6 border-b border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-xl font-black text-slate-100 uppercase tracking-wide">Preview</h3>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">
                        @if($preview->count())
                            Showing {{ number_format($preview->firstItem()) }}–{{ number_format($preview->lastItem()) }}
                            of {{ number_format($preview->total()) }} rows — the download contains every one
                        @elseif($preview->total())
                            Page {{ $preview->currentPage() }} is past the end — {{ number_format($preview->total()) }} rows in this selection
                        @else
                            No rows in this selection
                        @endif
                    </p>
                </div>
                @if($preview->hasPages())
                    <span class="shrink-0 px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                        Page {{ $preview->currentPage() }} / {{ $preview->lastPage() }}
                    </span>
                @endif
            </div>

            <div class="table-responsive-wrapper">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/10 bg-white/5">
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Business</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Date</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Customer</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 hidden md:table-cell">Specialist</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 hidden sm:table-cell">Slot</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($preview as $booking)
                            <tr class="border-b border-white/10 hover:bg-white/5 transition-all">
                                <td class="p-4">
                                    <span class="font-black text-white text-sm">{{ $booking->vendor?->business_name ?? '—' }}</span>
                                </td>
                                <td class="p-4">
                                    <div class="text-xs font-black text-white">{{ $booking->appointment_date_label }}</div>
                                    @if($booking->token_number)
                                        <div class="text-[10px] text-blue-400 font-black mt-0.5">Token #{{ $booking->token_number }}</div>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-slate-200 text-xs uppercase">{{ $booking->customer_name }}</div>
                                    <div class="text-[10px] text-slate-400 font-bold mt-0.5">{{ $booking->customer_phone }}</div>
                                </td>
                                <td class="p-4 hidden md:table-cell">
                                    <span class="font-bold text-slate-300 text-xs uppercase">{{ $booking->employee?->name ?? '—' }}</span>
                                </td>
                                <td class="p-4 hidden sm:table-cell">
                                    <span class="text-[11px] text-slate-300 font-bold">
                                        {{ $booking->appointment_at?->format('h:i A') ?? $booking->slot_start_time }}
                                        –
                                        {{ $booking->appointment_end_at?->format('h:i A') ?? $booking->slot_end_time }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    @php
                                        $color = match($booking->status) {
                                            'confirmed' => 'bg-emerald-500/15 text-emerald-300',
                                            'completed' => 'bg-blue-500/15 text-blue-300',
                                            'cancelled' => 'bg-rose-500/15 text-rose-300',
                                            'skipped'   => 'bg-amber-500/15 text-amber-300',
                                            default     => 'bg-white/5 text-slate-400',
                                        };
                                    @endphp
                                    <span class="px-3 py-1.5 {{ $color }} rounded-lg text-[9px] font-black uppercase tracking-widest">{{ $booking->status }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <span class="text-xs font-black text-white">₹{{ number_format((float) $booking->online_paid_amount, 2) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-20 text-center">
                                    <div class="flex flex-col items-center opacity-20">
                                        <svg class="h-14 w-14 mb-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="text-xs font-black uppercase tracking-widest text-slate-400">No bookings in this period</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-admin-pagination :paginator="$preview" label="bookings" />
        </div>
    </div>

    <script>
        /**
         * The shop multi-select. Same contract as the vendor panel's staff
         * picker — empty means everything — plus a type-ahead, because a
         * platform list grows without bound where a shop's staff list does not.
         */
        function vendorPicker(selected, names) {
            return {
                open: false,
                search: '',
                selected: selected ?? [],
                names: names ?? {},

                get label() {
                    if (this.selected.length === 0) return 'All Shops';
                    if (this.selected.length === 1) return this.names[this.selected[0]] ?? '1 Selected';
                    return this.selected.length + ' Shops Selected';
                },

                /* A ticked shop stays visible whatever the search says, so a
                   filter typed after choosing can never hide the choice — it
                   would still submit, which is worse than not showing it. */
                matches(id, name) {
                    if (this.selected.includes(id)) return true;
                    if (!this.search) return true;
                    return String(name).toLowerCase().includes(this.search.toLowerCase());
                },
            };
        }
    </script>
</x-admin-layout>
