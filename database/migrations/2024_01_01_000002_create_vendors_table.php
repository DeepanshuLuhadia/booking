<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('business_name');
            $table->string('slug')->unique();
            $table->string('owner_name');
            $table->string('contact_number');
            $table->string('shop_photo')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_open')->default(true);
            $table->boolean('token_booking_enabled')->default(false);
            $table->decimal('token_amount', 10, 2)->nullable();
            $table->foreignId('subscription_plan_id')->nullable()->constrained();
            $table->timestamp('subscription_expires_at')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->enum('status', ['inactive', 'active'])->default('inactive');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
