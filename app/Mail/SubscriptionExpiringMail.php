<?php

namespace App\Mail;

use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiringMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Vendor $vendor;
    public int $daysBefore;

    public function __construct(Vendor $vendor, int $daysBefore)
    {
        $this->vendor = $vendor;
        $this->daysBefore = $daysBefore;
    }

    public function envelope(): Envelope
    {
        $when = $this->daysBefore === 1 ? 'tomorrow' : "in {$this->daysBefore} days";

        return new Envelope(
            subject: "Your subscription expires {$when} - renew to keep your booking page live",
        );
    }

    public function content(): Content
    {
        // Live via Razorpay UPI Autopay: the charge (and expiry extension) happens
        // on its own, so the email should say so rather than push a renewal the
        // vendor doesn't need to act on.
        $autopayLive = in_array($this->vendor->razorpay_subscription_status, ['active', 'authenticated'], true);

        return new Content(
            view: 'emails.subscription_expiring',
            with: [
                'vendorName' => $this->vendor->business_name,
                'expiresAt' => $this->vendor->subscription_expires_at,
                'daysBefore' => $this->daysBefore,
                'autopayLive' => $autopayLive,
                'plansUrl' => route('vendor.plans'),
            ],
        );
    }
}
