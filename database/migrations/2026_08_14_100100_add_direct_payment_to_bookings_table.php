<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Direct-to-vendor UPI advance payments — the booking's half.
 *
 * The lifecycle these columns record:
 *
 *   pending  → booking made, advance not yet paid (or a previous proof was
 *              rejected and the customer is being asked again)
 *   paid     → customer has submitted a UTR and a screenshot; nobody has
 *              checked the bank yet
 *   verified → the shop found the money in its account and locked the slot
 *   rejected → the shop could not find it; the customer is asked to re-submit
 *
 * `requested_amount` is the amount lock. It is written server-side from the
 * vendor's `advance_amount` and never from customer input, so the figure the
 * screen displays, the figure encoded into the UPI deep link, and the figure
 * the shop verifies against are all the same one row.
 *
 * A non-zero `requested_amount` is also what marks a booking as belonging to
 * this flow at all — bookings at shops that do not ask for an advance keep
 * 0.00 and stay on `payment_status = 'pending'` forever, which is why every
 * query below filters on the amount and not just the status.
 *
 * `token_number` already exists on this table (see the
 * `align_untracked_schema` migration) and is deliberately untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid', 'verified', 'rejected'])
                    ->default('pending')
                    ->after('online_paid_amount');
            }

            if (! Schema::hasColumn('bookings', 'payment_method')) {
                $table->string('payment_method')->default('direct_upi')->after('payment_status');
            }

            if (! Schema::hasColumn('bookings', 'requested_amount')) {
                $table->decimal('requested_amount', 8, 2)->default(0.00)->after('payment_method');
            }

            // The bank's 12-character reference for the transfer. Unique across
            // the table: one transfer can only ever settle one booking, so a
            // reference already claimed elsewhere is a re-use attempt, not a
            // second payment.
            if (! Schema::hasColumn('bookings', 'utr_number')) {
                $table->string('utr_number', 32)->nullable()->unique()->after('requested_amount');
            }

            // Path on the `public` disk, under payment_proofs/.
            if (! Schema::hasColumn('bookings', 'payment_screenshot')) {
                $table->string('payment_screenshot')->nullable()->after('utr_number');
            }

            // When the customer submitted the proof, and when the shop ruled on
            // it — the two timestamps the verification queue is ordered by.
            if (! Schema::hasColumn('bookings', 'payment_submitted_at')) {
                $table->timestamp('payment_submitted_at')->nullable()->after('payment_screenshot');
            }

            if (! Schema::hasColumn('bookings', 'payment_verified_at')) {
                $table->timestamp('payment_verified_at')->nullable()->after('payment_submitted_at');
            }

            // Why a proof was turned down, shown back to the customer on the
            // re-submission screen so "rejected" is not the whole explanation.
            if (! Schema::hasColumn('bookings', 'payment_rejection_reason')) {
                $table->string('payment_rejection_reason', 255)->nullable()->after('payment_verified_at');
            }
        });

        // The vendor's verification queue: this shop's proofs, oldest first.
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['vendor_id', 'payment_status'], 'bookings_vendor_payment_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_vendor_payment_status_index');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $drop = array_values(array_filter([
                'payment_status',
                'payment_method',
                'requested_amount',
                'utr_number',
                'payment_screenshot',
                'payment_submitted_at',
                'payment_verified_at',
                'payment_rejection_reason',
            ], fn ($column) => Schema::hasColumn('bookings', $column)));

            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
