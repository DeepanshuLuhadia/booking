<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->date('booking_date');
            $table->time('slot_start_time');
            $table->time('slot_end_time');
            $table->enum('booking_type', ['normal', 'emergency', 'vendor'])->default('normal');
            $table->boolean('token_required')->default(false);
            $table->decimal('token_amount', 10, 2)->default(0);
            $table->decimal('emergency_fee', 10, 2)->default(0);
            $table->decimal('online_paid_amount', 10, 2)->default(0);
            $table->enum('status', ['confirmed', 'cancelled', 'completed', 'no_show'])->default('confirmed');
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->boolean('vendor_booked')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Prevent double booking
            $table->unique(['employee_id', 'booking_date', 'slot_start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
