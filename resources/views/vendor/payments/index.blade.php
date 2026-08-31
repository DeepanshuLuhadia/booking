{{--
    The shop's record of payments customers made straight into its account.

    A ledger, not a verification desk. No booking on this page is waiting on
    anything — appointments here are confirmed the moment they are made — and
    the platform verifies none of this money, because none of it passes through
    the platform. What the shop does is open its own UPI app, find the credit,
    and tick the row off so it can tell at a glance which ones it has not found
    yet.

    "Mark as received" is a plain POST form rather than a fetch call: it writes
    to the booking and the page must reload to reflect it, and a failed request
    has to be visible rather than swallowed by JavaScript.
--}}
<x-vendor-layout>
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-8 mb-12">
        <div>
            <h1 class="text-4xl font-black italic tracking-tight uppercase text-white">
                Online <span class="text-blue-600">Payments.</span>
            </h1>
            <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] mt-2 italic">
                Paid Direct To Your UPI &middot; Check Against Your Own App
            </p>
        </div>

        @if($pending->isNotEmpty())
            <div class="px-6 py-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-center shrink-0">
                <p class="text-3xl font-black italic text-amber-400 leading-none">{{ $pending->count() }}</p>
                <p class="text-[8px] font-black text-amber-300/70 uppercase tracking-widest italic mt-1.5">
                    Not Ticked Off
                </p>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-8 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-6 py-4
                    text-[11px] font-black uppercase tracking-widest italic text-emerald-300">
            {{ session('success') }}
        </div>
    @endif
    @if(session('info'))
        <div class="mb-8 rounded-2xl border border-sky-500/20 bg-sky-500/10 px-6 py-4
                    text-[11px] font-black uppercase tracking-widest italic text-sky-300">
            {{ session('info') }}
        </div>
    @endif
    @if(session('error') || $errors->any())
        <div class="mb-8 rounded-2xl border border-rose-500/20 bg-rose-500/10 px-6 py-4
                    text-[11px] font-black uppercase tracking-widest italic text-rose-300">
            {{ session('error') ?? $errors->first() }}
        </div>
    @endif

    @if(! $vendor->acceptsDirectAdvance() && $pending->isEmpty())
        <div class="glass-card p-10 text-center space-y-4">
            <svg class="w-14 h-14 mx-auto text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M17 9V7a5 5 0 00-10 0v2a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2z"/>
            </svg>
            <p class="text-xl font-black italic uppercase tracking-tight text-white">Direct UPI payments are off</p>
            <p class="text-xs text-slate-400 font-medium max-w-md mx-auto leading-relaxed">
                Turn them on in your settings and customers will be handed straight to their UPI app when they
                book, paying into your account without the platform in between.
            </p>
            <a href="{{ route('vendor.profile.edit') }}"
               class="inline-flex items-center gap-2 mt-2 px-6 h-12 rounded-xl bg-blue-600 hover:bg-blue-500
                      text-white text-[10px] font-black uppercase tracking-widest transition-all">
                Open Payment Settings
            </a>
        </div>
    @endif

    {{-- ================= STILL TO FIND ================= --}}
    @if($pending->isNotEmpty())
        {{-- Stated once, at the top. A shop that thinks the platform checked
             this money will tick rows off without looking, and their own UPI
             app is the only check that exists. --}}
        <div class="rounded-2xl border border-blue-500/20 bg-blue-500/5 p-5 mb-8 flex gap-4">
            <svg class="w-5 h-5 shrink-0 text-blue-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-[11px] leading-relaxed text-slate-300 font-medium">
                These payments went <span class="text-white font-black">straight into your account</span> &mdash;
                {{ config('app.name') }} never received them and cannot confirm them for you. Check your UPI app for
                each amount, and ask the customer for their receipt at the counter.
                <span class="text-white font-black">Every one of these bookings is already confirmed</span> &mdash;
                ticking a row off changes nothing about the appointment.
            </p>
        </div>

        <div class="space-y-4 mb-16">
            @foreach($pending as $booking)
                <div class="glass-card p-6 md:p-8 flex flex-col lg:flex-row lg:items-center gap-6">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 flex-wrap mb-2">
                            <span class="px-3 py-1 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20
                                         text-[8px] font-black uppercase tracking-widest italic">
                                Paid Online &middot; Not Ticked Off
                            </span>
                            <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest italic">
                                Booking #{{ $booking->id }}
                            </span>
                        </div>

                        <p class="text-xl font-black italic uppercase tracking-tight text-white truncate">
                            {{ $booking->customer_name }}
                        </p>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic mt-1">
                            @if($booking->customer_phone && $booking->customer_phone !== 'Anonymous')
                                {{ $booking->customer_phone }} &middot;
                            @endif
                            {{ $booking->appointment_date_label }}
                            @if($booking->token_number)
                                &middot; Token #{{ $booking->token_number }}
                            @elseif($booking->appointment_at)
                                &middot; {{ $booking->appointment_at->format('h:i A') }}
                            @endif
                            &middot; {{ $booking->employee?->name }}
                        </p>
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest italic mt-2">
                            Paid {{ $booking->payment_submitted_at?->diffForHumans() ?? '—' }}
                            &middot; into {{ $vendor->upi_id }}
                        </p>

                        {{-- Legacy rows only. Nothing writes a UTR or a
                             screenshot any more, but the ones already on file
                             are still the fastest way to match a credit, so
                             they stay visible where they exist. --}}
                        @if($booking->utr_number || $booking->payment_screenshot_url)
                            <div class="mt-4 flex items-center gap-4 flex-wrap">
                                @if($booking->utr_number)
                                    <span class="font-mono text-xs text-slate-300 tracking-[0.15em] select-all">
                                        UTR {{ $booking->utr_number }}
                                    </span>
                                @endif
                                @if($booking->payment_screenshot_url)
                                    <a href="{{ $booking->payment_screenshot_url }}" target="_blank" rel="noopener"
                                       class="text-[9px] font-black uppercase tracking-widest italic text-blue-400 hover:text-blue-300">
                                        View receipt &nearr;
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-6 shrink-0">
                        <div class="text-right">
                            <p class="text-[8px] font-black text-slate-500 uppercase tracking-widest italic mb-1">
                                Amount
                            </p>
                            <p class="text-2xl font-black italic text-emerald-400 tracking-tight">
                                ₹{{ number_format((float) $booking->requested_amount, 2) }}
                            </p>
                        </div>

                        <form action="{{ route('vendor.payments.approve', $booking) }}" method="POST"
                              onsubmit="return confirm('Confirm ₹{{ number_format((float) $booking->requested_amount, 2) }} arrived in your account for booking #{{ $booking->id }}?')">
                            @csrf
                            <button type="submit"
                                    class="h-14 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white
                                           text-[10px] font-black uppercase tracking-widest transition-all
                                           flex items-center justify-center gap-3 shadow-lg shadow-emerald-600/20 whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                                Mark As Received
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif($vendor->acceptsDirectAdvance())
        <div class="glass-card p-16 text-center mb-16">
            <div class="flex flex-col items-center opacity-20">
                <svg class="h-16 w-16 mb-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xl font-black uppercase tracking-[0.25em] italic text-white">All Caught Up</p>
            </div>
            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest italic mt-6">
                New online payments appear here the moment a customer books
            </p>
        </div>
    @endif

    {{-- ================= ALREADY TICKED OFF ================= --}}
    @if($settled->total() > 0)
        <div class="mb-6">
            <h2 class="text-2xl font-black italic uppercase tracking-tight text-white">Received <span class="text-blue-600">History.</span></h2>
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mt-2 italic">
                Payments You Have Confirmed Receiving
            </p>
        </div>

        <div class="glass-card overflow-hidden">
            <div class="table-responsive-wrapper">
                <table class="w-full">
                    <thead class="bg-white/5 text-left">
                        <tr>
                            <th class="px-8 py-5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic">Customer</th>
                            <th class="hidden md:table-cell px-8 py-5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic">Specialist</th>
                            <th class="px-8 py-5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic">Amount</th>
                            <th class="hidden sm:table-cell px-8 py-5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic">Marked</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($settled as $booking)
                            <tr class="hover:bg-white/5 transition-all">
                                <td class="px-8 py-6">
                                    <div class="font-black text-white text-sm uppercase italic tracking-tight">
                                        {{ $booking->customer_name }}
                                    </div>
                                    <div class="text-[9px] text-slate-400 font-black mt-1 uppercase tracking-widest italic">
                                        #{{ $booking->id }} &middot; {{ $booking->appointment_date_label }}
                                    </div>
                                </td>
                                <td class="hidden md:table-cell px-8 py-6">
                                    <span class="text-xs text-slate-300 font-medium">{{ $booking->employee?->name ?? '—' }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="font-black text-white text-sm italic">
                                        ₹{{ number_format((float) $booking->requested_amount, 2) }}
                                    </span>
                                </td>
                                <td class="hidden sm:table-cell px-8 py-6">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">
                                        {{ $booking->payment_verified_at?->diffForHumans() ?? '—' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($settled->hasPages())
                <div class="px-8 py-6 border-t border-white/10">
                    {{ $settled->links() }}
                </div>
            @endif
        </div>
    @endif
</x-vendor-layout>
