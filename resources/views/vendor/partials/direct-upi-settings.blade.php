{{--
    Direct-to-vendor UPI advance settings.

    Lives inside the main profile form (one page, one save button) rather than
    on a settings page of its own — `upi_id` was already here, and splitting the
    shop's payment details across two screens is how one of them goes stale.

    The QR preview is fetched from the server on every edit rather than drawn in
    the browser, because it has to encode the exact same NPCI string the
    customer will scan, `mam` and all. A second implementation in JavaScript
    would eventually disagree with the PHP one about the amount lock.
--}}
<div class="glass-card p-6 sm:p-10 space-y-10"
     x-data="upiAdvanceSettings({
        enabled: {{ old('is_direct_payment_enabled', $vendor->is_direct_payment_enabled) ? 'true' : 'false' }},
        vpa: @js(old('upi_id', $vendor->upi_id)),
        payee: @js(old('upi_name', $vendor->upi_name ?: $vendor->business_name)),
        amount: @js(old('advance_amount', $vendor->advance_amount > 0 ? number_format((float) $vendor->advance_amount, 2, '.', '') : '')),
        previewUrl: @js(route('vendor.profile.upi-qr')),
     })">

    <div class="border-b border-slate-50 pb-6 flex items-start justify-between gap-6">
        <div>
            <h3 class="text-xl font-black italic uppercase text-white tracking-tight">
                Direct UPI <span class="text-blue-600">Advance.</span>
            </h3>
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mt-2 italic">
                Customer &rarr; Your Bank Account. Zero Platform Custody.
            </p>
        </div>
        <span class="shrink-0 px-3 py-1.5 rounded-lg text-[8px] font-black uppercase tracking-widest italic border"
              :class="enabled
                ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                : 'bg-white/5 text-slate-400 border-white/10'"
              x-text="enabled ? 'Live' : 'Off'"></span>
    </div>

    {{-- Said plainly and up front. A shop that believes the platform is holding
         this money will not check its own statement, and checking the statement
         is the only verification step that exists. --}}
    <div class="rounded-2xl border border-blue-500/20 bg-blue-500/5 p-5 flex gap-4">
        <svg class="w-5 h-5 shrink-0 text-blue-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-[11px] leading-relaxed text-slate-300 font-medium">
            Money moves <span class="text-white font-black">straight from the customer into your bank account</span>.
            {{ config('app.name') }} never receives it, holds it, or takes a cut &mdash; which also means we cannot
            confirm a payment for you. You approve each one against your own statement.
        </p>
    </div>

    {{-- The toggle. Paired with a hidden 0 so unticking actually posts
         something; an unchecked checkbox posts nothing at all. --}}
    <input type="hidden" name="is_direct_payment_enabled" value="0">
    <label class="flex items-center justify-between gap-6 cursor-pointer group">
        <div class="min-w-0">
            <span class="block text-sm font-black italic uppercase tracking-tight text-white">
                Enable Direct UPI Advance Payment for Bookings
            </span>
            <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest italic mt-1.5">
                Customers pay before their slot is confirmed
            </span>
        </div>
        <span class="relative shrink-0">
            <input type="checkbox" name="is_direct_payment_enabled" value="1" x-model="enabled" class="sr-only peer">
            <span class="block w-14 h-8 rounded-full transition-all duration-300 border"
                  :class="enabled ? 'bg-blue-600 border-blue-500' : 'bg-white/5 border-white/10'"></span>
            <span class="absolute top-1 left-1 w-6 h-6 rounded-full bg-white shadow-lg transition-transform duration-300"
                  :class="enabled ? 'translate-x-6' : 'translate-x-0'"></span>
        </span>
    </label>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-10 pt-2"
         :class="enabled ? '' : 'opacity-40 pointer-events-none select-none'">

        {{-- Fields --}}
        <div class="lg:col-span-3 space-y-8">
            <div class="space-y-4">
                <label for="upi_id"
                       class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">
                    Vendor UPI ID / VPA
                    <span x-show="enabled" class="text-rose-400 not-italic">*</span>
                </label>
                {{-- `required` only while the toggle is on, so the browser
                     refuses to submit rather than making the vendor discover
                     the same rule after a full page round trip. The server rule
                     in ProfileController is still the control — this attribute
                     can be stripped, that one cannot. --}}
                <input type="text" id="upi_id" name="upi_id" x-model="vpa" @input.debounce.400ms="refreshPreview()"
                       :required="enabled"
                       autocomplete="off" spellcheck="false" placeholder="clinic@upi"
                       class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium lowercase">
                <p class="text-[9px] font-black uppercase tracking-widest italic ml-4"
                   :class="vpaLooksValid || !vpa ? 'text-slate-500' : 'text-amber-400'"
                   x-text="vpa && !vpaLooksValid
                        ? 'Expected format: name@bank'
                        : 'The exact ID money should land in. Double-check it.'"></p>
                @error('upi_id')
                    <p class="text-[9px] font-black uppercase tracking-widest italic text-rose-400 ml-4">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-4">
                <label for="upi_name"
                       class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">
                    Payee / Business Name
                </label>
                <input type="text" id="upi_name" name="upi_name" x-model="payee" @input.debounce.400ms="refreshPreview()"
                       maxlength="100" placeholder="City Dental Clinic"
                       class="glass-input w-full min-h-[2.75rem] px-4 py-2.5 rounded-xl font-medium">
                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest italic ml-4">
                    Shown in the customer's UPI app before they confirm
                </p>
                @error('upi_name')
                    <p class="text-[9px] font-black uppercase tracking-widest italic text-rose-400 ml-4">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-4">
                <label for="advance_amount"
                       class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">
                    Advance Fee Amount (₹)
                    <span class="text-slate-500 not-italic">(optional)</span>
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-black">₹</span>
                    {{-- Deliberately NOT required. An empty box is a real choice
                         — "charge the whole booking price up front" — not an
                         unfinished form, so the only floor is zero. --}}
                    <input type="number" id="advance_amount" name="advance_amount" x-model="amount"
                           @input.debounce.400ms="refreshPreview()"
                           min="0" max="999999.99" step="0.01" inputmode="decimal"
                           placeholder="Leave empty to charge the full amount"
                           class="glass-input w-full min-h-[2.75rem] pl-9 pr-4 py-2.5 rounded-xl font-medium">
                </div>

                {{-- The single most confusable thing on this card: an empty box
                     does not mean "no payment", it means "the whole price". Say
                     which of the two modes is currently in force, in words, and
                     change it live as they type. --}}
                <div class="rounded-xl px-4 py-3 ml-4 border transition-colors"
                     :class="chargesFixedAdvance
                        ? 'border-blue-500/20 bg-blue-500/5'
                        : 'border-amber-500/20 bg-amber-500/5'">
                    <p class="text-[9px] font-black uppercase tracking-widest italic"
                       :class="chargesFixedAdvance ? 'text-blue-300' : 'text-amber-300'"
                       x-text="chargesFixedAdvance ? 'Deposit mode' : 'Full payment mode'"></p>
                    <p class="text-[10px] text-slate-400 font-medium mt-1 leading-relaxed"
                       x-text="chargesFixedAdvance
                            ? 'Customers transfer ₹' + amountLabelFor(amount) + ' to book. They settle the rest with you in person.'
                            : 'Customers transfer the full price of their booking to book — your service fee, plus the premium supplement if they pick a premium slot. The exact figure differs per booking.'"></p>
                </div>

                @error('advance_amount')
                    <p class="text-[9px] font-black uppercase tracking-widest italic text-rose-400 ml-4">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Live QR preview --}}
        <div class="lg:col-span-2 space-y-4">
            <span class="block text-[9px] font-black text-slate-300 uppercase italic tracking-widest ml-4">
                Customer-Facing QR
            </span>

            <div class="rounded-2xl border border-white/10 bg-white/5 p-6 flex flex-col items-center text-center min-h-[18rem] justify-center gap-4">
                <template x-if="loading">
                    <svg class="animate-spin w-7 h-7 text-slate-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>

                <template x-if="!loading && svg">
                    <div class="space-y-4 w-full">
                        {{-- Server-rendered SVG. White plate behind it: phone
                             cameras read a dark-on-light code far more reliably
                             than the inverse, and this panel is dark. --}}
                        <div class="bg-white rounded-2xl p-3 inline-block shadow-2xl" x-html="svg"></div>
                        <div>
                            <p class="text-2xl font-black italic text-white tracking-tight" x-text="'₹' + amountLabel"></p>
                            <p class="text-[9px] font-black text-emerald-400 uppercase tracking-widest italic mt-1">
                                Amount Locked
                            </p>
                        </div>
                        <p class="text-[9px] font-mono text-slate-500 break-all leading-relaxed px-2" x-text="vpa"></p>
                    </div>
                </template>

                <template x-if="!loading && !svg">
                    <div class="space-y-3">
                        <svg class="w-12 h-12 mx-auto text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest italic leading-relaxed px-2"
                           x-text="message"></p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

{{-- Inline rather than stacked: this project has no @stack('scripts'), and
     Alpine arrives with @livewireScripts at the very end of the layout, so a
     script here is guaranteed to have defined the component before Alpine
     starts looking for it. --}}
@once
    <script>
            /**
             * The direct-payment settings card.
             *
             * Every preview is a server round trip. That is deliberate: the QR
             * has to encode the identical NPCI deep link the customer's payment
             * screen will build, including the `mam` parameter that fixes the
             * amount, and the only way to guarantee that is to have one
             * implementation of it (UpiPaymentService) rather than two.
             */
            function upiAdvanceSettings(initial) {
                return {
                    enabled: initial.enabled,
                    vpa: initial.vpa || '',
                    payee: initial.payee || '',
                    amount: initial.amount || '',
                    previewUrl: initial.previewUrl,

                    svg: null,
                    amountLabel: '0.00',
                    message: 'Enter a valid UPI ID (name@bank) to see the QR code.',
                    loading: false,

                    // Bumped per request so a slow response for an older set of
                    // values cannot overwrite the preview of a newer one.
                    requestId: 0,

                    init() {
                        this.refreshPreview();
                        // Turning the feature on with details already filled in
                        // should show the code immediately, not on next keystroke.
                        this.$watch('enabled', () => this.refreshPreview());
                    },

                    get vpaLooksValid() {
                        return /^[a-zA-Z0-9.\-_]{2,64}@[a-zA-Z][a-zA-Z0-9.\-]{1,63}$/.test((this.vpa || '').trim());
                    },

                    /**
                     * Whether a fixed deposit is being taken, as opposed to the
                     * full booking price. Mirrors UpiPaymentService::
                     * chargesFixedAdvance() — an empty or zero box means "the
                     * whole price", never "no payment".
                     */
                    get chargesFixedAdvance() {
                        return parseFloat(this.amount) > 0;
                    },

                    amountLabelFor(value) {
                        const n = parseFloat(value);
                        return Number.isFinite(n) ? n.toFixed(2) : '0.00';
                    },

                    async refreshPreview() {
                        const ticket = ++this.requestId;

                        if (!this.vpaLooksValid) {
                            this.svg = null;
                            this.loading = false;
                            this.message = 'Enter a valid UPI ID (name@bank) to see the QR code.';
                            return;
                        }

                        /*
                         * No fixed advance means there is no single QR to show:
                         * the amount is that booking's own total, so each
                         * customer gets a different code built when they book.
                         * Rendering a sample here with an arbitrary figure would
                         * be a QR the shop could not actually hand to anyone.
                         */
                        if (!this.chargesFixedAdvance) {
                            this.svg = null;
                            this.loading = false;
                            this.message = 'No fixed advance — each customer gets their own QR for their booking total when they book.';
                            return;
                        }

                        this.loading = true;

                        try {
                            const params = new URLSearchParams({
                                upi_id: this.vpa.trim(),
                                upi_name: this.payee || '',
                                advance_amount: this.amount,
                            });

                            const res = await fetch(`${this.previewUrl}?${params}`, {
                                headers: { 'Accept': 'application/json' },
                            });
                            const data = await res.json();

                            if (ticket !== this.requestId) return;

                            if (data.ok) {
                                this.svg = data.svg;
                                this.amountLabel = data.amount;
                            } else {
                                this.svg = null;
                                this.message = data.message || 'Could not build the QR code.';
                            }
                        } catch (e) {
                            if (ticket !== this.requestId) return;
                            this.svg = null;
                            // The saved settings are unaffected by a failed
                            // preview, so say what actually broke.
                            this.message = 'Preview unavailable — check your connection. Your saved settings are untouched.';
                        } finally {
                            if (ticket === this.requestId) this.loading = false;
                        }
                    },
                };
            }
    </script>
@endonce
