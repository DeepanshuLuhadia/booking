<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Brings the migration history back in line with the live database.
 *
 * Several columns and two tables were added directly to the running database
 * without a migration. The gap only shows up on a fresh install, where later
 * migrations reference columns that were never created — `make_tokens_per_
 * employee` indexes `bookings.token_number`, and `vendors.vendor_category_id`
 * backs the whole category discovery flow.
 *
 * Every statement is guarded, so this is a no-op on databases that already
 * carry the objects and a repair on the ones that do not.
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- Vendor category link ------------------------------------------
        if (! Schema::hasColumn('vendors', 'vendor_category_id')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->foreignId('vendor_category_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('vendor_categories')
                    ->nullOnDelete();
            });
        }

        // --- Vendor operating-hours and trial columns ------------------------
        Schema::table('vendors', function (Blueprint $table) {
            if (! Schema::hasColumn('vendors', 'shop_open_time')) {
                $table->time('shop_open_time')->nullable()->after('is_open');
            }
            if (! Schema::hasColumn('vendors', 'shop_close_time')) {
                $table->time('shop_close_time')->nullable()->after('shop_open_time');
            }
            // Opens and closes the shop automatically at the times above.
            if (! Schema::hasColumn('vendors', 'auto_toggle_status')) {
                $table->boolean('auto_toggle_status')->default(true)->after('shop_close_time');
            }
            // "Running late" — pushes every waiting slot back by N minutes.
            if (! Schema::hasColumn('vendors', 'slot_delay_minutes')) {
                $table->unsignedInteger('slot_delay_minutes')->default(0)->after('auto_toggle_status');
            }
            if (! Schema::hasColumn('vendors', 'delay_active')) {
                $table->boolean('delay_active')->default(false)->after('slot_delay_minutes');
            }
            if (! Schema::hasColumn('vendors', 'is_trial')) {
                $table->boolean('is_trial')->default(false)->after('subscription_plan_id');
            }
        });

        // --- Per-specialist consultation length -------------------------------
        if (! Schema::hasColumn('employees', 'avg_consultation_time')) {
            Schema::table('employees', function (Blueprint $table) {
                // Overrides the vendor default when a specialist is faster or slower.
                $table->integer('avg_consultation_time')->default(15);
            });
        }

        // --- Vendor-level shifts -------------------------------------------
        if (! Schema::hasTable('shifts')) {
            Schema::create('shifts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->time('start_time');
                $table->time('end_time');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // --- Per-specialist shifts -----------------------------------------
        if (! Schema::hasTable('employee_shifts')) {
            Schema::create('employee_shifts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->time('start_time');
                $table->time('end_time');
                $table->timestamps();
            });
        }

        // --- Booking columns -----------------------------------------------
        Schema::table('bookings', function (Blueprint $table) {
            // The queue token. Null for pure time-slot bookings, which is why
            // the unique index added later tolerates NULLs.
            if (! Schema::hasColumn('bookings', 'token_number')) {
                $table->integer('token_number')->nullable()->after('booking_type');
            }

            if (! Schema::hasColumn('bookings', 'shift_id')) {
                $table->foreignId('shift_id')->nullable()->after('token_number')
                    ->constrained('shifts')->nullOnDelete();
            }

            // Push token captured at booking time, so a guest with no account
            // can still be told their turn is coming.
            if (! Schema::hasColumn('bookings', 'fcm_token')) {
                $table->text('fcm_token')->nullable()->after('customer_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(array_values(array_filter(
                ['shop_open_time', 'shop_close_time', 'auto_toggle_status', 'slot_delay_minutes', 'delay_active', 'is_trial'],
                fn ($column) => Schema::hasColumn('vendors', $column)
            )));
        });

        if (Schema::hasColumn('employees', 'avg_consultation_time')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('avg_consultation_time');
            });
        }

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'shift_id')) {
                $table->dropConstrainedForeignId('shift_id');
            }
            if (Schema::hasColumn('bookings', 'token_number')) {
                $table->dropColumn('token_number');
            }
            if (Schema::hasColumn('bookings', 'fcm_token')) {
                $table->dropColumn('fcm_token');
            }
        });

        Schema::dropIfExists('employee_shifts');
        Schema::dropIfExists('shifts');

        if (Schema::hasColumn('vendors', 'vendor_category_id')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->dropConstrainedForeignId('vendor_category_id');
            });
        }
    }
};
