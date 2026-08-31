<x-admin-layout>
    {{--
        The platform booking ledger. Everything every shop and every specialist
        has taken, newest first, always paginated — the admin's tracking view,
        where /admin/reports is the export view.
    --}}
    <div class="space-y-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex flex-col gap-2">
                <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white">All Bookings</h2>
                <p class="text-xs md:text-sm font-medium text-slate-400 uppercase tracking-widest">
                    Every appointment across all shops and specialists
                </p>
            </div>
            <a href="{{ route('admin.reports.index') }}"
               class="shrink-0 px-6 py-3.5 rounded-xl bg-white/5 border border-white/10 text-slate-300 hover:bg-white/10 hover:text-white text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export Reports
            </a>
        </div>

        {{-- ── Filters ─────────────────────────────────────────────── --}}
        <form method="GET" action="{{ route('admin.bookings.index') }}" class="glass-card p-6 sm:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <div class="space-y-3 lg:col-span-1">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Search</label>
                    <input type="text" name="q" value="{{ $filters['q'] }}"
                           placeholder="Customer, phone, token or booking ID"
                           class="w-full h-14 px-5 rounded-xl bg-white/5 border-2 border-white/10 text-white text-[11px] font-bold placeholder:text-slate-500 focus:border-blue-600 focus:outline-none">
                </div>

                {{-- Changing the shop resets the specialist: staff ids belong to
                     one shop, so carrying the old one over would list nothing. --}}
                <div class="space-y-3">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Shop</label>
                    <select name="vendor" onchange="this.form.employee.value=''; this.form.submit()"
                            class="w-full h-14 px-5 rounded-xl bg-white/5 border-2 border-white/10 text-white text-[11px] font-black uppercase tracking-widest focus:border-blue-600 focus:outline-none">
                        <option value="" class="bg-slate-900">All Shops ({{ $allVendors->count() }})</option>
                        @foreach($allVendors as $shop)
                            <option value="{{ $shop->id }}" class="bg-slate-900" @selected($filters['vendor'] === $shop->id)>{{ $shop->business_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-3">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Specialist</label>
                    <select name="employee" @disabled($employees->isEmpty()) onchange="this.form.submit()"
                            class="w-full h-14 px-5 rounded-xl bg-white/5 border-2 border-white/10 text-white text-[11px] font-black uppercase tracking-widest focus:border-blue-600 focus:outline-none disabled:opacity-40 disabled:cursor-not-allowed">
                        <option value="" class="bg-slate-900">
                            {{ $employees->isEmpty() ? 'Pick a shop first' : 'All Specialists' }}
                        </option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" class="bg-slate-900" @selected($filters['employee'] === $employee->id)>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-3">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</label>
                    <select name="status" onchange="this.form.submit()"
                            class="w-full h-14 px-5 rounded-xl bg-white/5 border-2 border-white/10 text-white text-[11px] font-black uppercase tracking-widest focus:border-blue-600 focus:outline-none">
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" class="bg-slate-900" @selected($filters['status'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-3 md:col-span-2">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Business Date Range</label>
                    <div class="flex items-center gap-3">
                        <input type="date" name="from" value="{{ $filters['from'] }}"
                               class="w-full h-14 px-4 rounded-xl bg-white/5 border-2 border-white/10 text-white text-[11px] font-black focus:border-blue-600 focus:outline-none">
                        <span class="text-slate-500 text-[10px] font-black shrink-0">TO</span>
                        <input type="date" name="to" value="{{ $filters['to'] }}"
                               class="w-full h-14 px-4 rounded-xl bg-white/5 border-2 border-white/10 text-white text-[11px] font-black focus:border-blue-600 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 mt-8 pt-6 border-t border-white/10">
                <button type="submit" class="btn-primary px-6 py-3.5 rounded-xl text-[10px] font-black uppercase tracking-widest">
                    Apply Filters
                </button>
                <a href="{{ route('admin.bookings.index') }}"
                   class="px-6 py-3.5 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 text-[10px] font-black uppercase tracking-widest transition-all">
                    Reset
                </a>
            </div>
        </form>

        {{-- ── Headline numbers ─────────────────────────────────────── --}}
        @php
            // Counted across the whole selection, ignoring the status filter —
            // see the controller. `total` is therefore the selection, not the page.
            $tiles = [
                ['label' => 'In Selection', 'value' => $summary['total'],     'tone' => 'text-white'],
                ['label' => 'Confirmed',    'value' => $summary['confirmed'], 'tone' => 'text-emerald-400'],
                ['label' => 'Completed',    'value' => $summary['completed'], 'tone' => 'text-blue-400'],
                ['label' => 'Cancelled',    'value' => $summary['cancelled'], 'tone' => 'text-rose-400'],
                ['label' => 'Skipped',      'value' => $summary['skipped'],   'tone' => 'text-amber-400'],
                ['label' => 'Pending Pay',  'value' => $summary['pending'],   'tone' => 'text-slate-300'],
            ];
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($tiles as $tile)
                <div class="glass-card p-5">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">{{ $tile['label'] }}</p>
                    <h3 class="text-3xl font-black {{ $tile['tone'] }} leading-none">{{ number_format($tile['value']) }}</h3>
                </div>
            @endforeach
        </div>

        {{-- ── Ledger ───────────────────────────────────────────────── --}}
        <div class="glass-card overflow-hidden">
            <div class="table-responsive-wrapper">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/10 bg-white/5">
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Shop</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Date</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Customer</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 hidden md:table-cell">Specialist</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 hidden sm:table-cell">Slot</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 hidden lg:table-cell">Source</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr class="border-b border-white/10 hover:bg-white/5 transition-all">
                                <td class="p-4">
                                    @if($booking->vendor)
                                        <a href="{{ route('admin.vendors.show', $booking->vendor) }}"
                                           class="font-black text-white text-sm hover:text-blue-400 transition-colors">
                                            {{ $booking->vendor->business_name }}
                                        </a>
                                    @else
                                        <span class="font-black text-slate-500 text-sm">—</span>
                                    @endif
                                    <div class="text-[10px] font-bold text-slate-500 mt-0.5">#{{ $booking->id }}</div>
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
                                    <span class="text-[11px] text-slate-300 font-bold whitespace-nowrap">
                                        {{ $booking->appointment_at?->format('h:i A') ?? $booking->slot_start_time }}
                                        –
                                        {{ $booking->appointment_end_at?->format('h:i A') ?? $booking->slot_end_time }}
                                    </span>
                                </td>
                                <td class="p-4 hidden lg:table-cell">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                        {{ $booking->vendor_booked ? 'Walk-in' : ucfirst($booking->booking_type ?? 'online') }}
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
                                <td colspan="8" class="p-20 text-center">
                                    <div class="flex flex-col items-center opacity-20">
                                        <svg class="h-14 w-14 mb-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="text-xs font-black uppercase tracking-widest text-slate-400">No bookings match these filters</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-admin-pagination :paginator="$bookings" label="bookings" />
        </div>
    </div>
</x-admin-layout>
