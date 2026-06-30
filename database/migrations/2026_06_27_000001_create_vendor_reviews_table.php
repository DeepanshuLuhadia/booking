<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('reviewer_name', 60);
            $table->string('reviewer_phone', 15)->nullable();
            $table->unsignedTinyInteger('rating'); // 1..5
            $table->text('comment')->nullable();

            // Vendor-side moderation: a vendor can flag (report) a review for
            // admin attention. The review stays publicly visible until an admin
            // either deletes it or clears (unreports) the flag.
            $table->boolean('is_reported')->default(false);
            $table->string('report_reason', 500)->nullable();
            $table->timestamp('reported_at')->nullable();

            $table->timestamps();

            $table->index(['vendor_id', 'created_at']);
            $table->index('is_reported');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_reviews');
    }
};
