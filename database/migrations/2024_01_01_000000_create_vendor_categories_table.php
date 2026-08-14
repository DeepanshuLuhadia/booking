<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The service categories every vendor is filed under (salon, clinic, …).
 *
 * This table existed in the live database but had no migration, so a fresh
 * install came up without it — taking category discovery, the landing-page
 * listing and the vendors foreign key with it. Guarded so it is a no-op where
 * the table is already present.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vendor_categories')) {
            return;
        }

        Schema::create('vendor_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_categories');
    }
};
