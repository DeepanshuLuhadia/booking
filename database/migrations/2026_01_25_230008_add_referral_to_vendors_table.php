<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('referral_code')->unique()->nullable()->after('qr_code_path');
            $table->foreignId('referred_by_id')->nullable()->constrained('vendors')->nullOnDelete()->after('referral_code');
            $table->decimal('referral_balance', 10, 2)->default(0)->after('referred_by_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            //
        });
    }
};
