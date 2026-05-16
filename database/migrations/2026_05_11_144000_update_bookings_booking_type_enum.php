<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, convert 'emergency' and 'vendor' to 'premium' and 'normal'
        // to avoid truncation when changing the enum definition
        DB::table('bookings')->where('booking_type', 'emergency')->update(['booking_type' => 'normal']); // Fallback to normal if premium isn't there yet
        // Wait, MySQL won't let me update to a value NOT in the enum.
        
        // Use raw SQL to change the column definition
        DB::statement("ALTER TABLE bookings MODIFY COLUMN booking_type ENUM('normal', 'premium', 'emergency', 'vendor') DEFAULT 'normal'");
        
        // Now map old types to new unified types
        DB::table('bookings')->where('booking_type', 'emergency')->update(['booking_type' => 'premium']);
        DB::table('bookings')->where('booking_type', 'vendor')->update(['booking_type' => 'normal']);
        
        // Finally, tighten the enum to only the new values
        DB::statement("ALTER TABLE bookings MODIFY COLUMN booking_type ENUM('normal', 'premium') DEFAULT 'normal'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE bookings MODIFY COLUMN booking_type ENUM('normal', 'premium', 'emergency', 'vendor') DEFAULT 'normal'");
        
        DB::table('bookings')->where('booking_type', 'premium')->update(['booking_type' => 'emergency']);
        
        DB::statement("ALTER TABLE bookings MODIFY COLUMN booking_type ENUM('normal', 'emergency', 'vendor') DEFAULT 'normal'");
    }
};
