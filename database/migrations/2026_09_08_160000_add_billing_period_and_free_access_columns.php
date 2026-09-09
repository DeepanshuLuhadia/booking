<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->enum('billing_period', ['monthly', 'yearly'])->default('yearly')->after('is_active');
        });

        Schema::table('vendors', function (Blueprint $table) {
            // Admin-granted comp period: while set (and in the future), autopay
            // is paused and nothing is charged, but access is unaffected —
            // subscription_expires_at is extended to at least this date.
            $table->timestamp('free_access_until')->nullable()->after('razorpay_subscription_status');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('free_access_until');
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('billing_period');
        });
    }
};
