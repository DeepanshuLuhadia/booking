<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Backfills two vendor columns that existed in the live database but were never
 * captured in a migration: `appointment_mode` and `avg_consultation_time`.
 *
 * Their absence broke every fresh install — the next migration in the sequence
 * (add_global_times_to_vendors_table) positions its columns `after
 * avg_consultation_time` and aborted with "Unknown column", taking the whole
 * test suite down with it.
 *
 * Guarded with hasColumn so it is a no-op on the databases that already carry
 * these columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (! Schema::hasColumn('vendors', 'appointment_mode')) {
                $table->enum('appointment_mode', ['time_slot', 'token', 'hybrid'])
                    ->default('time_slot')
                    ->after('token_amount');
            }

            if (! Schema::hasColumn('vendors', 'avg_consultation_time')) {
                // Minutes per customer; drives every wait-time estimate.
                $table->integer('avg_consultation_time')
                    ->default(15)
                    ->after('appointment_mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['appointment_mode', 'avg_consultation_time']);
        });
    }
};
