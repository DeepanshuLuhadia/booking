<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Razorpay Subscriptions (UPI Autopay / e-mandate) support, so a vendor's
     * plan renews automatically instead of needing a manual repayment every
     * period. razorpay_plan_id is cached on the local plan so it's only
     * created once with Razorpay, lazily, the first time someone checks out.
     */
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->string('razorpay_plan_id')->nullable()->after('is_active');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->string('razorpay_subscription_id')->nullable()->after('subscription_expires_at');
            // Raw Razorpay subscription state (created/authenticated/active/
            // pending/halted/cancelled/completed) — for support/debugging.
            // Access control never reads this; it stays driven by
            // subscription_expires_at, same as before autopay existed.
            $table->string('razorpay_subscription_status')->nullable()->after('razorpay_subscription_id');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['razorpay_subscription_id', 'razorpay_subscription_status']);
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('razorpay_plan_id');
        });
    }
};
