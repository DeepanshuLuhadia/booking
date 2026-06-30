<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_reviews', function (Blueprint $table) {
            // Captured only when the reviewer opts to sign in with Google.
            $table->string('reviewer_email')->nullable()->after('reviewer_phone');
            // True => identity confirmed via Google; false => anonymous reviewer.
            $table->boolean('is_verified')->default(false)->after('reviewer_email');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_reviews', function (Blueprint $table) {
            $table->dropColumn(['reviewer_email', 'is_verified']);
        });
    }
};
