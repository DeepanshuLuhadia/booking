<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Vendor;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * The one place a UPI deep link is built.
 *
 * The platform processes no money. Every payment in this flow goes straight
 * from the customer's UPI app into the vendor's own bank account, and what we
 * produce is only the instruction that addresses it: an NPCI `upi://pay` URL
 * carrying the shop's VPA and the exact amount.
 *
 * Because we never see the transfer, the *amount* is the whole of our leverage,
 * and it is defended in three places rather than one:
 *
 *   1. `am` — the amount, pre-filled in the customer's UPI app.
 *   2. `mam` — the minimum acceptable amount, set equal to `am`. This is the
 *      flag that stops the payment being edited downward: with `mam` present a
 *      compliant PSP app renders the amount fixed instead of editable.
 *   3. `Booking::$requested_amount` — our own copy, written server-side from
 *      the vendor's `advance_amount` and never from anything the customer
 *      submits. This is the figure the shop verifies its bank statement
 *      against, and it is the only one of the three we control end to end.
 *
 * (3) is load-bearing precisely because (1) and (2) are not enforceable by us:
 * they are honoured by the customer's UPI app, not by our server, and a
 * customer who pays the wrong amount anyway is caught at verification rather
 * than prevented at the link. The link makes the right amount the default and
 * the easy path; the vendor's approval step is what makes it binding.
 */
class UpiPaymentService
{
    /**
     * Characters a payee name may carry into the deep link. PSP apps reject or
     * mangle names with punctuation outside this set, and the whole point of
     * `pn` is that the customer recognises who they are paying — a mangled name
     * is worse than a plain one.
     */
    private const NAME_ALLOWED = '/[^A-Za-z0-9 .&\-]/';

    /**
     * VPA shape: handle@psp. Deliberately permissive on the handle (banks allow
     * dots, hyphens and underscores) and strict on the overall structure, which
     * is the part a typo actually breaks.
     */
    public const VPA_PATTERN = '/^[a-zA-Z0-9.\-_]{2,64}@[a-zA-Z][a-zA-Z0-9.\-]{1,63}$/';

    /**
     * Whether this shop is set up to take payment up front at all.
     *
     * Two conditions, not three: the toggle is the shop's intent and the VPA is
     * where the money would go. The advance amount is deliberately NOT part of
     * this — a shop that enables direct payment without naming an advance is
     * asking for the *full* booking price, which is a perfectly valid setup.
     * See amountDueFor().
     *
     * The VPA is checked for shape rather than mere presence because `upi_id`
     * predates this feature as a free-text settlement note. A shop carrying
     * "ask at counter" in that column must not be treated as payable.
     */
    public function isEnabledFor(Vendor $vendor): bool
    {
        return (bool) $vendor->is_direct_payment_enabled
            && $this->isValidVpa($vendor->upi_id);
    }

    /**
     * What the customer must transfer before this booking is confirmed.
     *
     * Two modes, chosen by whether the shop named an advance:
     *
     *   advance_amount > 0  → the advance. A deposit; the balance is settled
     *                         in person.
     *   advance_amount 0/null → $fullAmount, the whole booking price. The shop
     *                         wants paying up front rather than a deposit.
     *
     * $fullAmount is passed in rather than derived here because only the
     * booking knows it — it depends on the employee's fee override and on
     * whether a premium slot was picked, neither of which is a property of the
     * vendor. BookingController computes it as service fee + premium fee, which
     * is the same "Due Now" figure the customer is shown before booking.
     *
     * Returns '0.00' when the shop is not set up, and also when a free booking
     * has no advance on it — in both cases there is nothing to collect and the
     * booking confirms straight away.
     */
    public function amountDueFor(Vendor $vendor, float $fullAmount): string
    {
        if (! $this->isEnabledFor($vendor)) {
            return '0.00';
        }

        $advance = (float) $vendor->advance_amount;

        return $this->formatAmount($advance > 0 ? $advance : max($fullAmount, 0));
    }

    /**
     * Whether the shop takes a fixed advance (as opposed to the full amount).
     * Drives wording — "advance" and "full amount" are different promises to
     * the customer about what is left to pay at the counter.
     */
    public function chargesFixedAdvance(Vendor $vendor): bool
    {
        return $this->isEnabledFor($vendor) && (float) $vendor->advance_amount > 0;
    }

    /**
     * The NPCI deep link for a booking's advance, or null when the booking
     * carries no advance to collect.
     *
     * Built from the booking's OWN `requested_amount`, not the vendor's current
     * `advance_amount`. A shop that raises its fee after a customer has already
     * been quoted must not silently change what that customer is being asked to
     * transfer — the quote is fixed at booking time.
     */
    public function deepLinkFor(Booking $booking): ?string
    {
        $vendor = $booking->vendor;
        $amount = (float) $booking->requested_amount;

        if (! $vendor || $amount <= 0 || blank($vendor->upi_id)) {
            return null;
        }

        return $this->buildDeepLink(
            $vendor->upi_id,
            $this->payeeName($vendor),
            $amount,
            'Booking-' . $booking->id
        );
    }

    /**
     * The link a vendor's own settings screen previews, using whatever is
     * currently typed into the form rather than what is saved.
     */
    public function previewLink(?string $vpa, ?string $payeeName, $amount): ?string
    {
        $amount = (float) $amount;

        if (blank($vpa) || $amount <= 0 || ! $this->isValidVpa($vpa)) {
            return null;
        }

        return $this->buildDeepLink(
            $vpa,
            $this->sanitiseName($payeeName) ?: 'Merchant',
            $amount,
            'Advance-Preview'
        );
    }

    /**
     * Assemble the URL.
     *
     * Every value is url-encoded — a payee name with a space or an ampersand
     * would otherwise truncate the query string, and the parameter it truncates
     * is as likely to be `mam` as anything else, which would silently unlock
     * the amount.
     */
    public function buildDeepLink(string $vpa, string $payeeName, float $amount, string $note): string
    {
        $formatted = $this->formatAmount($amount);

        return 'upi://pay?' . http_build_query([
            'pa'  => trim($vpa),
            'pn'  => $payeeName,
            'am'  => $formatted,
            // Equal to `am`, which is what fixes the field rather than merely
            // suggesting it. See the class docblock.
            'mam' => $formatted,
            'cu'  => 'INR',
            'tn'  => $this->sanitiseNote($note),
        ], '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * The deep link rendered as an inline SVG QR code, for the desktop screen
     * where there is no UPI app to hand off to.
     *
     * SVG rather than PNG on purpose: it needs no imagick, scales to any
     * viewport, and can be dropped straight into the page with no file written
     * to disk and no URL for a stale image to be cached at.
     *
     * Returns null rather than throwing — a QR code that fails to render must
     * not take down the payment screen, which still has the tap-to-pay button
     * and the shop's VPA in text form.
     */
    public function qrSvg(?string $deepLink, int $size = 260): ?string
    {
        if (blank($deepLink)) {
            return null;
        }

        try {
            $svg = (string) QrCode::format('svg')
                ->size($size)
                ->margin(1)
                // High correction: these get scanned off glossy laptop screens
                // at an angle, which is the worst case for a dense payload.
                ->errorCorrection('H')
                ->generate($deepLink);

            /*
            | Drop the XML prolog the generator emits.
            |
            | These SVGs are injected inline — into the page with {!! !!}, and
            | into the settings preview through Alpine's x-html. An
            | `<?xml … ?>` declaration inside an HTML document is parsed as a
            | bogus comment node rather than a declaration: harmless, but it is
            | dead markup in every payment screen, and stripping it is cheaper
            | than explaining it.
            */
            return preg_replace('/^\s*<\?xml.*?\?>\s*/s', '', $svg);
        } catch (\Throwable $e) {
            Log::warning('UPI QR generation failed: ' . $e->getMessage());

            return null;
        }
    }

    public function isValidVpa(?string $vpa): bool
    {
        return filled($vpa) && preg_match(self::VPA_PATTERN, trim($vpa)) === 1;
    }

    /**
     * The name shown in the customer's UPI app: the shop's chosen payee name,
     * falling back to its business name so the field is never blank.
     */
    public function payeeName(Vendor $vendor): string
    {
        return $this->sanitiseName($vendor->upi_name)
            ?: ($this->sanitiseName($vendor->business_name) ?: 'Merchant');
    }

    /**
     * Two decimal places, no thousands separator. A UPI app reading "1,500.00"
     * either rejects the link or, worse, parses it as 1.00.
     */
    public function formatAmount($amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    private function sanitiseName(?string $name): string
    {
        return trim(mb_substr(preg_replace(self::NAME_ALLOWED, '', (string) $name), 0, 50));
    }

    /**
     * Transaction notes are the most fragile field in the spec — several PSPs
     * silently drop the whole link on punctuation. Ours are machine-generated
     * ("Booking-41"), so restricting to alphanumerics and hyphens costs nothing.
     */
    private function sanitiseNote(string $note): string
    {
        return mb_substr(preg_replace('/[^A-Za-z0-9\- ]/', '', $note), 0, 50) ?: 'Booking';
    }
}
