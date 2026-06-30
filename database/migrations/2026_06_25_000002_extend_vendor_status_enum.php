<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Extend vendors.status to support the full approval lifecycle.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE vendors MODIFY status ENUM('pending','active','inactive','suspended','rejected') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Collapse new states back to the original two before narrowing the enum.
        DB::statement("UPDATE vendors SET status = 'inactive' WHERE status IN ('pending','suspended','rejected')");
        DB::statement("ALTER TABLE vendors MODIFY status ENUM('inactive','active') NOT NULL DEFAULT 'inactive'");
    }
};
