<x-app-layout page-title="My Bookings" footer-mode="minimal">

    {{--
        Every live booking this visitor holds, across every vendor.

        A booking that is still pending/confirmed occupies the customer's one
        allowed slot at that vendor — BookingController refuses a second one —
        so each card says so explicitly rather than leaving the customer to
        discover it as an error on the vendor's booking form. A vendor only
        becomes bookable again once its booking is completed (or cancelled /
        skipped / expired by the nightly reset), which is what the history
        section below shows.
    --}}
    <div class="min-h-screen pt-32 md:pt-40 pb-24 px-4 md:px-8"
         x-data="myBookings(@js($liveBookings))"
         x-init="init()">

        <div class="max-w-5xl mx-auto">

            {{-- Header --}}
            <div class="mb-10 md:mb-14">
                <span class="theme-gradient-text font-black text-[10px] uppercase tracking-[0.3em] italic block mb-3">
                    Your Queue
                </span>
                <h1 class="text-4xl md:text-6xl font-black tracking-tighter italic uppercase text-white">
                    My Bookings
                </h1>
                <p class="text-white/50 text-sm md:text-base font-medium mt-4 max-w-2xl">
                    Everything you are holding right now, at every business. You can book with a
                    business again once its appointment here is marked complete.
                </p>
            </div>

            {{-- ══════════════════════════════════════════════════
                 ACTIVE BOOKINGS
                 ══════════════════════════════════════════════════ --}}
            <template x-if="bookings.length > 0">
                <div class="space-y-6">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="open-pulse bg-emerald-500"></span>
                        <h2 class="text-[10px] font-black uppercase tracking-[0.25em] text-white/50 italic">
                            Active — <span x-text="bookings.length"></span> <span x-text="bookings.length === 1 ? 'business' : 'businesses'"></span>
                        </h2>
                    </div>

                    <template x-for="booking in bookings" :key="booking.id">
                        <div class="glass-card bg-white/5 backdrop-blur-3xl border border-white/10 rounded-[2.5rem] p-6 md:p-8 shadow-2xl shadow-black/20 relative overflow-hidden">

                            <div class="absolute inset-0 theme-gradient-bg opacity-[0.06] pointer-events-none"></div>

                            <div class="relative z-10 flex flex-col md:flex-row md:items-center gap-6 md:gap-8">

                                {{-- Business + specialist --}}
                                <div class="flex-1 min-w-0">
                                    <span class="inline-block px-3 py-1 bg-sky-500/20 text-sky-300 rounded-full text-[9px] font-black uppercase tracking-widest italic mb-3">
                                        Active Booking
                                    </span>
                                    <h3 class="text-2xl md:text-3xl font-black tracking-tighter italic uppercase text-white truncate"
                                        x-text="booking.vendor_name"></h3>
                                    <p class="text-white/50 text-xs font-bold uppercase tracking-widest mt-2">
                                        With <span class="text-white/80" x-text="booking.employee_name"></span>
                                    </p>
                                    <p class="text-white/40 text-xs font-medium mt-1" x-text="booking.booking_date"></p>
                                </div>

                                {{-- Token mode: live queue position --}}
                                <template x-if="booking.token_number">
                                    <div class="md:w-72 shrink-0">
                                        <h2 class="text-4xl font-black italic tracking-tighter uppercase text-white mb-4 text-center md:text-right"
                                            x-text="'Token #' + booking.token_number"></h2>

                                        <div class="grid grid-cols-2 gap-4 bg-white/5 p-4 rounded-2xl border border-white/10">
                                            {{-- Label and value both server-derived: between customers
                                                 this reads "Up Next #10" instead of still announcing a
                                                 token that has already been finished with. --}}
                                            <div class="text-center">
                                                <p class="text-[9px] font-black uppercase tracking-widest text-white/40 italic"
                                                   x-text="booking.serving_label ?? 'Now Serving'"></p>
                                                <p class="text-2xl font-black text-white italic"
                                                   x-text="booking.serving_display ?? '—'"></p>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-[9px] font-black uppercase tracking-widest text-white/40 italic">Ahead Of You</p>
                                                <p class="text-2xl font-black text-sky-400 italic" x-text="'#' + booking.people_ahead"></p>
                                            </div>
                                        </div>

                                        <p x-show="booking.approx_wait_min > 0" style="display:none;"
                                           class="mt-3 text-[10px] font-black uppercase tracking-widest text-white/40 italic text-center"
                                           x-text="'Approx. ' + booking.approx_wait_min + ' min wait'"></p>
                                    </div>
                                </template>

                                {{-- Time-slot mode: the appointment time itself --}}
                                <template x-if="!booking.token_number">
                                    <div class="md:w-72 shrink-0 md:text-right">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-white/40 italic mb-1">Your Slot</p>
                                        <h2 class="text-3xl font-black italic tracking-tighter uppercase text-white"
                                            x-text="booking.slot_time || '—'"></h2>
                                        <span class="inline-block mt-3 px-3 py-1 bg-emerald-500/20 text-emerald-300 text-[10px] font-black uppercase rounded-lg"
                                              x-text="booking.status_label"></span>
                                    </div>
                                </template>
                            </div>

                            <div class="relative z-10 mt-6 pt-6 border-t border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <p class="text-white/40 text-xs italic">
                                    Only one active booking per business each day — this one must be completed
                                    before you can book <span class="text-white/70 font-bold" x-text="booking.vendor_name"></span> again.
                                </p>
                                <div class="shrink-0 flex items-center gap-3">
                                    <template x-if="booking.vendor_slug">
                                        <a :href="'/vendors/' + booking.vendor_slug"
                                           class="px-6 py-3 rounded-xl bg-white/5 border border-white/10 text-[10px] font-black uppercase tracking-widest text-white hover:bg-white/10 transition-all active:scale-95 text-center">
                                            View Queue
                                        </a>
                                    </template>

                                    <button type="button" @click="cancelTarget = booking"
                                            class="px-6 py-3 rounded-xl bg-rose-950/30 border border-rose-500/20 text-[10px] font-black uppercase tracking-widest text-rose-300 hover:bg-rose-950/50 transition-all active:scale-95">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- ══════════════════════════════════════════════════
                 EMPTY STATE
                 ══════════════════════════════════════════════════ --}}
            <template x-if="bookings.length === 0">
                <div class="glass-card bg-white/5 backdrop-blur-3xl border border-white/10 rounded-[2.5rem] p-12 md:p-20 text-center shadow-2xl shadow-black/20">
                    <div class="w-16 h-16 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center mx-auto mb-6 text-white/30">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black italic uppercase tracking-tighter text-white mb-3">
                        No Active Bookings
                    </h3>
                    <p class="text-white/40 text-sm font-medium max-w-md mx-auto mb-8">
                        @if($isIdentified)
                            Nothing is holding your place right now. Every business is open to you.
                        @else
                            We recognise you by the phone number you book with. Make a booking and it
                            will show up here on this device.
                        @endif
                    </p>
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center gap-2 theme-gradient-bg px-8 py-4 rounded-xl text-[10px] font-black uppercase tracking-widest text-white shadow-xl transition-all hover:brightness-110 active:scale-95">
                        Explore Businesses
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </template>

            {{-- ══════════════════════════════════════════════════
                 CLOSED OUT — the vendor is bookable again
                 ══════════════════════════════════════════════════ --}}
            @if($closedBookings->isNotEmpty())
                <div class="mt-14">
                    <h2 class="text-[10px] font-black uppercase tracking-[0.25em] text-white/40 italic mb-6">
                        Finished — these businesses are open to you again
                    </h2>

                    <div class="space-y-3">
                        @foreach($closedBookings as $booking)
                            <div class="glass-card bg-white/[0.03] backdrop-blur-2xl border border-white/5 rounded-2xl px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
                                <div class="flex-1 min-w-0">
                                    <p class="text-white/80 font-black italic uppercase tracking-tight truncate">
                                        {{ $booking['vendor_name'] }}
                                    </p>
                                    <p class="text-white/30 text-[11px] font-bold uppercase tracking-widest mt-1">
                                        {{ $booking['employee_name'] }} · {{ $booking['booking_date'] }}
                                        @if($booking['token_number'])
                                            · Token #{{ $booking['token_number'] }}
                                        @elseif($booking['slot_time'])
                                            · {{ $booking['slot_time'] }}
                                        @endif
                                    </p>

                                    {{-- A skip is the one outcome the customer cannot work out
                                         from the chip alone: their turn went past without being
                                         served, and rebooking is on them. --}}
                                    @if($booking['status'] === 'skipped')
                                        <p class="text-amber-300/70 text-[11px] font-medium mt-2 normal-case tracking-normal">
                                            Skipped due to non-availability — your turn has passed. Please book again,
                                            or contact {{ $booking['vendor_name'] }} to reschedule.
                                        </p>
                                    @endif
                                </div>

                                @php
                                    $tone = match($booking['status']) {
                                        'completed' => 'bg-emerald-500/15 text-emerald-300',
                                        'skipped'   => 'bg-amber-500/15 text-amber-300',
                                        default     => 'bg-white/5 text-white/40',
                                    };
                                @endphp
                                <span class="shrink-0 px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest {{ $tone }}">
                                    {{ $booking['status_label'] }}
                                </span>

                                @if($booking['vendor_slug'])
                                    <a href="{{ route('vendor.show', $booking['vendor_slug']) }}"
                                       class="shrink-0 px-5 py-2.5 rounded-xl bg-white/5 border border-white/10 text-[9px] font-black uppercase tracking-widest text-white hover:bg-white/10 transition-all active:scale-95 text-center">
                                        Book Again
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        {{-- ══════════════════════════════════════════════════
             CANCEL CONFIRMATION
             Cancelling is not undoable and gives the slot away
             to whoever asks next, so it is always confirmed.
             ══════════════════════════════════════════════════ --}}
        <div x-show="cancelTarget" x-cloak x-transition class="fixed inset-0 z-[300] flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-2xl" @click="cancelTarget = null"></div>

            <div class="relative bg-[#0a0f2c] text-white rounded-[2.5rem] p-8 md:p-12 w-full max-w-lg text-center border border-white/10 shadow-[0_100px_200px_-50px_rgba(0,0,0,0.8)]">
                <div class="w-16 h-16 rounded-2xl bg-rose-500/15 border border-rose-500/20 text-rose-400 flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.48 0L3.16 16.25A2 2 0 005 19z"/>
                    </svg>
                </div>

                <h3 class="text-2xl md:text-3xl font-black italic uppercase tracking-tighter mb-3">Cancel this booking?</h3>

                <p class="text-white/50 text-sm font-medium mb-2">
                    <template x-if="cancelTarget && cancelTarget.token_number">
                        <span>Token <span class="text-white font-black" x-text="'#' + cancelTarget.token_number"></span> with</span>
                    </template>
                    <template x-if="cancelTarget && !cancelTarget.token_number">
                        <span>Your <span class="text-white font-black" x-text="cancelTarget.slot_time"></span> slot with</span>
                    </template>
                    <span class="text-white font-black" x-text="cancelTarget ? cancelTarget.employee_name : ''"></span>
                    at <span class="text-white font-black" x-text="cancelTarget ? cancelTarget.vendor_name : ''"></span>.
                </p>
                <p class="text-white/30 text-xs italic mb-8">
                    Your place in the queue is given up straight away and cannot be restored — you would
                    have to book again, and take a new token at the back of the queue.
                </p>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button" @click="cancelTarget = null" :disabled="cancelling"
                            class="flex-1 h-14 rounded-xl bg-white/5 border border-white/10 text-[10px] font-black uppercase tracking-widest text-white hover:bg-white/10 transition-all active:scale-95 disabled:opacity-40">
                        Keep It
                    </button>
                    <button type="button" @click="confirmCancel()" :disabled="cancelling"
                            class="flex-1 h-14 rounded-xl bg-rose-600 text-[10px] font-black uppercase tracking-widest text-white hover:brightness-110 transition-all active:scale-95 disabled:opacity-40">
                        <span x-show="!cancelling">Yes, Cancel</span>
                        <span x-show="cancelling" style="display:none;">Cancelling…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function myBookings(initial) {
            return {
                bookings: initial || [],

                // The booking awaiting confirmation in the cancel dialog.
                cancelTarget: null,
                cancelling: false,

                // Specialist queue channels currently subscribed to. See watchQueues().
                watched: new Set(),

                /*
                 * Cancel the customer's own booking. The server re-checks that
                 * this device owns it and returns the remaining live bookings,
                 * so the list is replaced from its answer rather than patched
                 * locally — a cancellation the vendor beat us to still lands the
                 * page in the right state.
                 */
                async confirmCancel() {
                    if (!this.cancelTarget || this.cancelling) return;
                    this.cancelling = true;

                    try {
                        const res = await fetch(`/my-bookings/${this.cancelTarget.id}/cancel`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        const data = await res.json();

                        if (data.success) {
                            this.bookings = data.bookings || [];
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message, type: 'success' } }));

                            // The history list and the nav badge are rendered by
                            // the server, so reload once the toast has been read
                            // to bring the cancelled booking through to "Finished".
                            setTimeout(() => window.location.reload(), 2200);
                        } else {
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.error || 'Could not cancel this booking.', type: 'error' } }));
                            // It may have been completed or expired underneath us.
                            await this.refresh();
                        }
                    } catch (e) {
                        console.error('Cancellation failed', e);
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'SYSTEM ERROR', type: 'error' } }));
                    }

                    this.cancelling = false;
                    this.cancelTarget = null;
                },

                /*
                 * Keep every card's queue position moving. One request covers all
                 * vendors, so a customer holding three tokens still costs a single
                 * poll — and the payload is built by the same service the page
                 * rendered from, so the two cannot disagree.
                 */
                async refresh() {
                    try {
                        const res = await fetch('{{ route('bookings.mine.status') }}');
                        if (!res.ok) return;
                        const data = await res.json();
                        this.bookings = data.bookings || [];
                        this.watchQueues();
                    } catch (e) {
                        console.error('Booking refresh failed', e);
                    }
                },

                /*
                 * Follow the queue of every specialist this customer is waiting
                 * on. Holding three tokens at three shops means three channels —
                 * each one pushes that card's position without a poll.
                 */
                watchQueues() {
                    if (!window.Echo) return;

                    const wanted = new Set(this.bookings.map(b => b.employee_id).filter(Boolean));

                    // Drop the ones we no longer hold a booking with.
                    for (const id of this.watched) {
                        if (!wanted.has(id)) {
                            window.Echo.leave(`queue.${id}`);
                            this.watched.delete(id);
                        }
                    }

                    for (const id of wanted) {
                        if (this.watched.has(id)) continue;
                        this.watched.add(id);

                        window.Echo.channel(`queue.${id}`)
                            .listen('.queue.updated', (e) => {
                                let closed = null;

                                this.bookings = this.bookings.map((booking) => {
                                    if (booking.employee_id !== e.employee_id) return booking;

                                    // The shop completed, cancelled or skipped it —
                                    // it is no longer live, so drop it from the list.
                                    if (e.changed && e.changed.booking_id === booking.id
                                        && e.changed.status !== 'confirmed' && e.changed.status !== 'pending') {
                                        closed = e.changed.status;
                                        return null;
                                    }

                                    // Counted from the tokens still waiting, not
                                    // token minus now_serving — the latter counts
                                    // completed and cancelled tokens below yours
                                    // as people still standing in front of you.
                                    const ahead = (booking.token_number && Array.isArray(e.waiting_tokens))
                                        ? e.waiting_tokens.filter(t => t < booking.token_number).length
                                        : 0;

                                    return {
                                        ...booking,
                                        now_serving: e.now_serving,
                                        serving_label: e.serving_label,
                                        serving_display: e.serving_display,
                                        people_ahead: ahead,
                                    };
                                }).filter(Boolean);

                                if (closed) {
                                    // A skip is worth spelling out — the customer is
                                    // usually still waiting and has to rebook.
                                    const note = closed === 'skipped'
                                        ? 'Your appointment was skipped due to non-availability. Please book again or contact the business.'
                                        : 'One of your bookings was closed by the business.';
                                    window.Realtime.toast(note, 'info');
                                    // Server-rendered history and the nav badge need
                                    // a round trip to catch up.
                                    setTimeout(() => window.location.reload(), 2500);
                                }

                                this.watchQueues();
                            });
                    }
                },

                init() {
                    if (this.bookings.length === 0) return;

                    this.watchQueues();

                    // Fallback only — stands down whenever the socket is up.
                    setInterval(() => {
                        if (window.Realtime?.connected()) return;
                        this.refresh();
                    }, 15000);
                }
            };
        }
    </script>
</x-app-layout>
