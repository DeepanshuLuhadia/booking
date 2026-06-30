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
        Schema::table('vendors', function (Blueprint $table) {
            $table->unsignedInteger('now_serving_token')->default(0)->after('allow_booking_until_closing');
            $table->unsignedInteger('max_daily_tokens')->nullable()->after('now_serving_token');
            $table->boolean('bookings_paused')->default(false)->after('is_open');
            $table->boolean('is_verified')->default(false)->after('status');
            
            $table->index(['status', 'is_open', 'is_profile_complete'], 'idx_vendor_discovery');
            $table->index('is_verified', 'idx_vendor_verified');
        });

        Schema::table('otps', function (Blueprint $table) {
            $table->unsignedTinyInteger('attempts')->default(0)->after('otp');
        });

        Schema::table('bookings', function (Blueprint $table) {
            // Need to change the enum status manually using raw SQL as Laravel's Schema Builder 
            // struggles with modifying enums safely, or we can use string instead. 
            // We will just change it to a string for simpler forward compatibility, or modify the enum.
        });
        
        // Modifying ENUM column safely via raw SQL:
        DB::statement("ALTER TABLE bookings MODIFY status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'skipped', 'expired') DEFAULT 'pending'");

        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['employee_id', 'booking_date', 'customer_phone'], 'idx_booking_dedup');
            $table->index(['vendor_id', 'booking_date', 'status'], 'idx_active_tokens');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_booking_dedup');
            $table->dropIndex('idx_active_tokens');
        });
        
        DB::statement("ALTER TABLE bookings MODIFY status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending'");

        Schema::table('otps', function (Blueprint $table) {
            $table->dropColumn('attempts');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropIndex('idx_vendor_discovery');
            $table->dropIndex('idx_vendor_verified');
            
            $table->dropColumn([
                'now_serving_token',
                'max_daily_tokens',
                'bookings_paused',
                'is_verified'
            ]);
        });
    }
};
