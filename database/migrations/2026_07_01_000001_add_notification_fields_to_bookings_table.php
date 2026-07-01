<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // FCM token of the customer's device, captured at booking time so we can
            // push a "you're next" / "it's your turn" alert when the queue advances.
            if (!Schema::hasColumn('bookings', 'fcm_token')) {
                $table->text('fcm_token')->nullable()->after('customer_phone');
            }
            // Idempotency guards so repeated queue-advance actions don't re-notify.
            if (!Schema::hasColumn('bookings', 'next_notified_at')) {
                $table->timestamp('next_notified_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('bookings', 'turn_notified_at')) {
                $table->timestamp('turn_notified_at')->nullable()->after('next_notified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            foreach (['fcm_token', 'next_notified_at', 'turn_notified_at'] as $col) {
                if (Schema::hasColumn('bookings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
