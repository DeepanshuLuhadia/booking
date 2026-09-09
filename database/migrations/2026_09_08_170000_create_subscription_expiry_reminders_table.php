<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dedup log for expiry-reminder emails, keyed by (vendor, the expiry date
     * the reminder was about, how many days out). Keying on the expiry date
     * itself — rather than a "reminder sent" flag on the vendor row — means a
     * renewal or admin comp that moves subscription_expires_at automatically
     * opens up a fresh reminder cycle with no extra bookkeeping anywhere else.
     */
    public function up(): void
    {
        Schema::create('subscription_expiry_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->date('expires_on');
            $table->unsignedTinyInteger('days_before');
            $table->timestamp('sent_at');

            $table->unique(['vendor_id', 'expires_on', 'days_before'], 'subscription_expiry_reminders_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_expiry_reminders');
    }
};
