<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            /*
            | Does this shop collect the customer's details before a booking?
            |
            | Set at the *vendor* level, so it governs every one of the shop's
            | employees at once — a customer scanning any employee's QR code gets
            | the same treatment as one scanning the shop's.
            |
            | Defaults to true, which is the behaviour every existing shop already
            | has: the name/phone form appears before the booking is made. Turned
            | off, the "book" button books immediately and the appointment is
            | filed as a walk-in guest.
            */
            if (!Schema::hasColumn('vendors', 'require_customer_details')) {
                $table->boolean('require_customer_details')->default(true)->after('show_contact_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (Schema::hasColumn('vendors', 'require_customer_details')) {
                $table->dropColumn('require_customer_details');
            }
        });
    }
};
