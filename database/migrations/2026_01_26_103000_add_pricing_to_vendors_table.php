<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('service_fee', 10, 2)->default(0)->after('token_amount');
            $table->decimal('emergency_fee', 10, 2)->default(0)->after('service_fee');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['service_fee', 'emergency_fee']);
        });
    }
};
