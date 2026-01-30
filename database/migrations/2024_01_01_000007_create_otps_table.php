<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('identifier'); // email or mobile
            $table->string('otp', 6);
            $table->enum('type', ['registration', 'login', 'verification']);
            $table->timestamp('expires_at');
            $table->boolean('verified')->default(false);
            $table->timestamps();

            $table->index(['identifier', 'otp', 'verified']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
