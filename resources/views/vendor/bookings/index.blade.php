<x-vendor-layout>
    <div class="flex flex-col md:flex-row items-center justify-between gap-8 mb-12">
        <div>
            <h1 class="text-4xl font-black italic tracking-tight uppercase ">Appointment <span
                    class="text-blue-600">Registry.</span></h1>
            <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] mt-2 italic">HISTORICAL
                TRANSACTION ARCHIVE</p>
        </div>
        <div class="w-full md:w-auto">
            <form action="{{ route('vendor.bookings.index') }}" method="GET">
                <div class="relative group">
                    <select name="status" onchange="this.form.submit()"
                        class="w-full md:w-64 h-14 bg-white border-2 border-slate-100 rounded-xl px-6 font-black italic text-slate-900 focus:ring-4 focus:ring-blue-50 focus:border-blue-600 transition-all appearance-none cursor-pointer text-[10px] uppercase tracking-widest">
                        <option value="">Matrix: All States</option>
                        <option value="confirmed" {{ request('status')=='confirmed' ? 'selected' : '' }}>State:
                            Confirmed</option>
                        <option value="completed" {{ request('status')=='completed' ? 'selected' : '' }}>State:
                            Completed</option>
                        <option value="cancelled" {{ request('status')=='cancelled' ? 'selected' : '' }}>State:
                            Cancelled</option>
                    </select>
                    <div
                        class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300 group-hover:text-blue-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white shadow-2xl shadow-slate-200/50 border border-slate-100 rounded-[3rem] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 text-left">
                    <tr>
                        <th class="px-10 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic">
                            Name</th>
                        <th class="px-10 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic">
                            Employee</th>
                        <th class="px-10 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic">
                            Time </th>
                        <th class="px-10 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic">
                            Status</th>
                        <th class="px-10 py-6 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($bookings as $booking)
                    <tr class="hover:bg-slate-50/50 transition-all">
                        <td class="px-10 py-8">
                            <div class="font-black text-slate-900 text-lg tracking-tight uppercase italic">{{
                                $booking->customer_name }}</div>
                            <div class="text-[9px] text-slate-300 font-black mt-1 uppercase tracking-widest italic">{{
                                $booking->customer_phone }}</div>
                        </td>
                        <td class="px-10 py-8">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center text-xs font-black italic">
                                    {{ substr($booking->employee->name, 0, 1) }}
                                </div>
                                <span class="font-black text-slate-700 text-sm uppercase italic">{{
                                    $booking->employee->name }}</span>
                            </div>
                        </td>
                        <td class="px-10 py-8">
                            <div class="text-sm font-black text-slate-900 italic tracking-tight">{{
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
                            default => 'bg-slate-50 text-slate-600 border-slate-100'
                            };
                            @endphp
                            <span
                                class="px-4 py-1.5 {{ $color }} rounded-lg text-[9px] font-black uppercase border tracking-widest italic">{{
                                $booking->status }}</span>
                        </td>
                        <td class="px-10 py-8 text-right">
                            <button
                                class="p-3 bg-slate-50 text-slate-300 rounded-xl hover:text-blue-600 transition-all">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                </svg>
                            </button>
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

            .pagination-container nav div:first-child {
                display: none;
            }

            @media (min-width: 640px) {
                .pagination-container nav div:first-child {
                    display: flex;
                    flex: 1 1 0%;
                    align-items: center;
                    justify-content: space-between;
                }
            }

            .pagination-container a,
            .pagination-container span[aria-current="page"]>span,
            .pagination-container span[aria-disabled="true"]>span {
                padding: 0.5rem 1rem;
                border: 1px solid #e2e8f0;
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
                background-color: #ffffff;
                color: #475569;
                text-decoration: none;
            }

            .pagination-container a:hover {
                background-color: #0f172a;
                color: #ffffff;
                border-color: #0f172a;
            }

            .pagination-container span[aria-current="page"]>span {
                background-color: #0f172a;
                color: #ffffff;
                border-color: #0f172a;
            }

            .pagination-container span[aria-disabled="true"]>span {
                background-color: #f8fafc;
                color: #cbd5e1;
                cursor: not-allowed;
            }

            .pagination-container svg {
                width: 1.25rem;
                height: 1.25rem;
            }
        </style>
        <div class="px-10 py-8 border-t border-slate-50 bg-slate-50/20 pagination-container">
            {{ $bookings->links() }}
        </div>
        @endif
    </div>
</x-vendor-layout>