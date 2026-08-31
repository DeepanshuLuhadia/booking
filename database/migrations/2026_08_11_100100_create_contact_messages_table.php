<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enquiries submitted through the public /contact form.
 *
 * Kept deliberately guest-friendly: the form is open to visitors who have
 * never signed in, so `user_id` is only stamped when we happen to know who
 * they are. The admin panel lists these and can reply straight to `email`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 32)->nullable();
            $table->string('subject');
            $table->text('message');

            // new -> read -> replied, with closed as the manual "done" state.
            $table->enum('status', ['new', 'read', 'replied', 'closed'])->default('new')->index();

            $table->text('admin_reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->foreignId('replied_by')->nullable()->constrained('users')->nullOnDelete();

            // Kept for abuse triage — the form is unauthenticated.
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();

            $table->timestamps();

            $table->index('created_at');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
