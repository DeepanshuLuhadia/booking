<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionExpiringMail;
use App\Models\SubscriptionExpiryReminder;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionExpiryReminders extends Command
{
    protected $signature = 'app:send-subscription-expiry-reminders';

    protected $description = 'Emails vendors 5 days and 1 day before their subscription expires.';

    /** How many days out a reminder should fire. */
    private const THRESHOLDS = [5, 1];

    public function handle()
    {
        $today = Carbon::today();
        $windowEnd = $today->copy()->addDays(max(self::THRESHOLDS));

        $vendors = Vendor::with('user')
            ->where('status', 'active')
            ->whereNotNull('subscription_expires_at')
            ->whereBetween('subscription_expires_at', [$today, $windowEnd->copy()->endOfDay()])
            ->get();

        $sent = 0;

        foreach ($vendors as $vendor) {
            if (!$vendor->user || !$vendor->user->email) {
                continue;
            }

            $expiresOn = $vendor->subscription_expires_at->copy()->startOfDay();
            $daysLeft = $today->diffInDays($expiresOn);

            if (!in_array($daysLeft, self::THRESHOLDS, true)) {
                continue;
            }

            $alreadySent = SubscriptionExpiryReminder::where('vendor_id', $vendor->id)
                ->where('expires_on', $expiresOn->toDateString())
                ->where('days_before', $daysLeft)
                ->exists();

            if ($alreadySent) {
                continue;
            }

            try {
                Mail::to($vendor->user->email)->send(new SubscriptionExpiringMail($vendor, $daysLeft));

                SubscriptionExpiryReminder::create([
                    'vendor_id' => $vendor->id,
                    'expires_on' => $expiresOn->toDateString(),
                    'days_before' => $daysLeft,
                    'sent_at' => now(),
                ]);

                $sent++;
            } catch (\Throwable $e) {
                Log::error("Failed to send subscription expiry reminder to vendor #{$vendor->id}: " . $e->getMessage());
            }
        }

        $this->info("Sent {$sent} subscription expiry reminder(s).");

        return self::SUCCESS;
    }
}
