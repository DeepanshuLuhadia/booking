<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Key/value store behind the admin "Site Settings" screen.
 *
 * The public content pages (About / Terms / Privacy / Contact) read their
 * company identity and editable copy from here, so the platform's name,
 * address and contact details can be changed from the panel without a deploy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            // Which tab of the settings screen the key belongs to.
            $table->string('group')->default('general')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
