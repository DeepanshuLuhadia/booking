<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The third way a customer can complete a direct-UPI booking: neither a
 * reference nor a screenshot, but a declaration that they will show the receipt
 * to the shop in person.
 *
 * Recorded rather than inferred, because "no UTR and no screenshot" otherwise
 * looks identical to "submitted nothing" — and the shop needs to tell those two
 * apart. A booking carrying this flag is a customer saying "I have paid, ask me
 * at the counter"; a booking carrying none of the three has not been submitted
 * at all and the shop must never see it.
 *
 * It does NOT bypass verification. The shop still approves or rejects against
 * their own bank statement; this only explains why the proof column is empty.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'payment_proof_deferred')) {
                $table->boolean('payment_proof_deferred')
                    ->default(false)
                    ->after('payment_screenshot');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'payment_proof_deferred')) {
                $table->dropColumn('payment_proof_deferred');
            }
        });
    }
};
