<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Direct-to-vendor UPI advance payments — the shop's half of the setup.
 *
 * The platform never touches this money. `upi_id` is the shop's own VPA and the
 * customer pays it from their own UPI app, so every column here is settlement
 * detail the shop owns rather than anything we hold on their behalf.
 *
 * `upi_id` already exists (2026_01_26_084400) and is left alone — it was added
 * as a settlement note and is now load-bearing, so it is only ever read here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            // Off by default: a shop that has not entered a VPA must never send
            // a customer to a payment screen with nothing to pay into.
            if (! Schema::hasColumn('vendors', 'is_direct_payment_enabled')) {
                $table->boolean('is_direct_payment_enabled')->default(false)->after('upi_id');
            }

            // The name the customer sees in their UPI app before confirming.
            // Without it apps show the raw VPA, which reads like a stranger.
            if (! Schema::hasColumn('vendors', 'upi_name')) {
                $table->string('upi_name')->nullable()->after('is_direct_payment_enabled');
            }

            // The advance the shop asks for per appointment. Copied onto each
            // booking at creation time (bookings.requested_amount) so a later
            // change to the fee cannot retroactively invalidate a payment a
            // customer has already made against the old figure.
            if (! Schema::hasColumn('vendors', 'advance_amount')) {
                $table->decimal('advance_amount', 8, 2)->default(0.00)->after('upi_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $drop = array_values(array_filter(
                ['is_direct_payment_enabled', 'upi_name', 'advance_amount'],
                fn ($column) => Schema::hasColumn('vendors', $column)
            ));

            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
