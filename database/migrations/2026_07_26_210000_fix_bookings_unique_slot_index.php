<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop the old strict unique index that prevented cancelled slots from being re-booked
            $table->dropUnique('bookings_employee_id_booking_date_slot_start_time_unique');

            // Add non-unique composite index for fast slot availability lookups
            $table->index(['employee_id', 'booking_date', 'status', 'slot_start_time'], 'idx_slot_availability');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_slot_availability');
            $table->unique(['employee_id', 'booking_date', 'slot_start_time']);
        });
    }
};
