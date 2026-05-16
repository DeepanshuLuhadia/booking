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
            $table->index(['status', 'is_profile_complete', 'is_open'], 'idx_vendor_discovery_status');
            $table->index(['global_opening_time', 'global_closing_time'], 'idx_vendor_discovery_timing');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->index(['vendor_id', 'is_active'], 'idx_employee_discovery_active');
            $table->index(['service_fee_override'], 'idx_employee_discovery_fee');
            $table->index(['working_start_time', 'working_end_time'], 'idx_employee_discovery_timing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropIndex('idx_vendor_discovery_status');
            $table->dropIndex('idx_vendor_discovery_timing');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('idx_employee_discovery_active');
            $table->dropIndex('idx_employee_discovery_fee');
            $table->dropIndex('idx_employee_discovery_timing');
        });
    }
};
