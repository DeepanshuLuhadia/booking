<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Move the token queue from vendor-level to employee-level.
     *
     * Each employee runs their own token queue and may set an optional daily
     * cap. now_serving_token advances as the employee serves/skips/completes.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedInteger('now_serving_token')->default(0)->after('is_paused');
            $table->unsignedSmallInteger('max_daily_tokens')->nullable()->after('now_serving_token');
        });

        // DB-level guard against duplicate token numbers for the same employee/day.
        // NULL token_number (time-slot bookings) is exempt — MySQL allows multiple NULLs.
        Schema::table('bookings', function (Blueprint $table) {
            $table->unique(['employee_id', 'booking_date', 'token_number'], 'unique_emp_token_per_day');
        });

        // Token state no longer lives on the vendor.
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['now_serving_token', 'max_daily_tokens']);
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->unsignedInteger('now_serving_token')->default(0)->after('allow_booking_until_closing');
            $table->unsignedInteger('max_daily_tokens')->nullable()->after('now_serving_token');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique('unique_emp_token_per_day');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['now_serving_token', 'max_daily_tokens']);
        });
    }
};
