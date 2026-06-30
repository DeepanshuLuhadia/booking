<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_reviews', function (Blueprint $table) {
            // Stores an array of public storage paths for proof images. Required
            // on the front-end when a low rating (< 2 stars) is submitted.
            $table->json('images')->nullable()->after('comment');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_reviews', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }
};
