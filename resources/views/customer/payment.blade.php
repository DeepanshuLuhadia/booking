{{--
    The "pay the shop again" screen.

    NOT a step in booking. A booking at a direct-payment shop is confirmed the
    moment it is made and the customer is handed to their UPI app straight from
    the confirmation screen — there is no proof to upload and nothing for this
    platform to verify. This page exists only for the customer who dismissed the
    payment chooser without paying, or who is coming back from "My Bookings" a
    day later and wants the QR again.

    So the copy here never implies the appointment is at risk. It is not.

    The amount comes from $amount, which the controller took from the booking
    row. The same figure is baked into the link's `am` and `mam`, so the button,
    the QR code and the shop's expectation cannot disagree.
--}}
<x-app-layout page-title="Pay {{ $vendor->business_name }}" footer-mode="minimal">
    <div class="min-h-screen pt-28 md:pt-36 pb-24 px-4 md:px-8">
        <div class="max-w-xl mx-auto space-y-6">

            {{-- ═══════════════ HEADER ═══════════════ --}}
            <div class="text-center space-y-3">
                <p class="text-[9px] font-black text-white/40 uppercase tracking-[0.3em] italic">
                    Booking #{{ $booking->id }} &middot; {{ $vendor->business_name }}
                </p>
                <h1 class="text-3xl md:text-5xl font-black italic uppercase tracking-tighter text-white leading-none">
                    @if($booking->isAdvanceVerified())
                        Payment Received
                    @else
                        Pay The Business
                    @endif
                </h1>
                {{-- Said first, because it is the thing the customer most needs
                     to know and the thing the old flow got wrong. --}}
                <p class="text-sm text-white/50 font-medium">
                    Your appointment is already confirmed — this page is only here so you can pay.
                </p>
            </div>

            {{-- ═══════════════ WHAT THE APPOINTMENT IS ═══════════════ --}}
            <div class="bg-white/5 border border-white/10 rounded-3xl p-6 space-y-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-white/40 mb-1">
                            @if($booking->token_number) Your Token @else Your Slot @endif
                        </p>
                        <p class="text-2xl font-black italic text-white">
                            @if($booking->token_number)
                                #{{ $booking->token_number }}
                            @else
                                {{ $booking->appointment_at?->format('h:i A') ?? '—' }}
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-[9px] font-black uppercase tracking-widest text-white/40 mb-1">Amount</p>
                        <p class="text-2xl font-black italic text-emerald-300">₹{{ $amount }}</p>
                    </div>
                </div>
                <p class="text-xs text-white/50 font-medium border-t border-white/10 pt-4">
                    {{ $booking->appointment_date_label }}
                    @if($booking->employee)
                        &middot; with <span class="text-white/80 font-bold">{{ $booking->employee->name }}</span>
                    @endif
                </p>
            </div>

            @if($booking->isAdvanceVerified())
                {{-- The shop has already ticked this off in its own UPI app.
                     Nothing left to do, and no button that could invite paying
                     a second time. --}}
                <div class="bg-emerald-500/5 border border-emerald-500/20 rounded-3xl p-6 text-center">
                    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-300 mb-2">Confirmed By The Business</p>
                    <p class="text-sm text-white/60 font-medium">
                        {{ $vendor->business_name }} has confirmed receiving your ₹{{ $amount }}. Nothing else is needed.
                    </p>
                </div>
            @else
                {{-- ═══════════════ PAY ═══════════════

                     The tap-to-pay anchor exists only on phones. On desktop a
                     `upi://` link has no handler — following it produces a
                     browser error page or a "no app found" dialog — so there
                     the QR below is the whole of the flow and the button is
                     never rendered visible at all. --}}
                <div class="bg-white/5 border border-white/10 rounded-3xl p-6 space-y-5"
                     x-data="{ onPhone: /Android|iPhone|iPad|iPod|Windows Phone/i.test(navigator.userAgent) }">
                    {{-- The primary action on a phone: one tap raises whichever
                         UPI apps are installed, with the amount already locked. --}}
                    <a href="{{ $deepLink }}" x-show="onPhone" x-cloak
                       class="theme-btn w-full h-16 rounded-2xl flex items-center justify-center text-base italic font-black uppercase tracking-widest">
                        Pay ₹{{ $amount }} Now
                    </a>

                    @if($qrSvg)
                        {{-- The desktop route, where there is no app to hand off
                             to. Rendered inline: no file on disk, no URL for a
                             stale image to be cached at. --}}
                        <div class="bg-white rounded-2xl p-5">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-3 text-center">
                                <span x-show="onPhone">Or Scan With Any UPI App</span>
                                <span x-show="!onPhone" x-cloak>Scan With Any UPI App To Pay</span>
                            </p>
                            <div class="w-48 mx-auto [&>svg]:w-full [&>svg]:h-auto">{!! $qrSvg !!}</div>
                            <p class="mt-3 text-center text-[11px] font-bold text-slate-600 break-all">{{ $vendor->upi_id }}</p>
                        </div>
                    @else
                        {{-- QR generation failed. The tap-to-pay button above
                             still works, and the VPA in text form is enough to
                             pay by hand. --}}
                        <p class="text-center text-[11px] font-bold text-white/50 break-all">
                            UPI ID: {{ $vendor->upi_id }}
                        </p>
                    @endif
                </div>

                {{-- The one instruction that replaces the whole upload step the
                     old flow had: the receipt is shown to a person, not to us. --}}
                <div class="bg-amber-500/5 border border-amber-500/20 rounded-3xl p-6">
                    <p class="text-[10px] font-black uppercase tracking-widest text-amber-300 mb-2">When Your Turn Comes</p>
                    <p class="text-xs text-white/70 font-medium leading-relaxed">
                        Show your UPI payment screenshot to
                        <span class="text-white font-bold">{{ $booking->employee?->name ?? 'the specialist' }}</span>
                        at the counter. This payment goes straight to {{ $vendor->business_name }} —
                        {{ config('app.name') }} never receives it and cannot confirm it for you.
                    </p>
                </div>
            @endif

            <div class="text-center">
                <a href="{{ route('bookings.mine') }}"
                   class="inline-block px-8 py-4 rounded-xl bg-white/5 border border-white/10 text-[10px] font-black uppercase tracking-widest text-white/70 hover:bg-white/10 transition-all active:scale-95">
                    Back To My Bookings
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
