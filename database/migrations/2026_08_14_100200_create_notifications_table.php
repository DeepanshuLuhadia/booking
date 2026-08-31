<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Laravel's standard notifications table.
 *
 * The project has used the `database` notification channel nowhere until now —
 * every alert went out over FCM, which is fire-and-forget: if the shop's device
 * was off, or permission was never granted, the alert simply never happened.
 *
 * A payment waiting to be verified cannot work that way. The money is already
 * out of the customer's account and the slot is being held on the strength of
 * it, so the shop has to find the request whenever it next opens the dashboard
 * rather than only in the moment it was sent. Hence a stored copy.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notifications')) {
            return;
        }

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
