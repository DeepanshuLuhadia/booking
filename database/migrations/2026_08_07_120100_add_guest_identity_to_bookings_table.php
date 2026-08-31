<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Collected alongside the phone number when the shop asks for
            // customer details. Optional — a customer may not want to give one.
            if (!Schema::hasColumn('bookings', 'customer_email')) {
                $table->string('customer_email')->nullable()->after('customer_phone');
            }

            /*
            | Per-device identity, written on EVERY booking.
            |
            | A guest's identity used to be their phone number: it is what the
            | session and cookie remember, what the duplicate check compares and
            | what "my bookings" queries by. A shop that has turned
            | `require_customer_details` off never collects one, so without this
            | column such a booking would belong to nobody — invisible on the
            | device that made it, and uncountable against the booking limits.
            |
            | Held in the session and a 30-day cookie by CustomerBookingService.
            */
            if (!Schema::hasColumn('bookings', 'guest_key')) {
                $table->string('guest_key', 64)->nullable()->after('customer_email');
                $table->index('guest_key');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'guest_key')) {
                $table->dropIndex(['guest_key']);
                $table->dropColumn('guest_key');
            }
            if (Schema::hasColumn('bookings', 'customer_email')) {
                $table->dropColumn('customer_email');
            }
        });
    }
};
