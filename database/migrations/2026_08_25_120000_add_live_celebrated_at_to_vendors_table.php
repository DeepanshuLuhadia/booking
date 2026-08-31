<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remembers that this shop has already been congratulated on going live.
 *
 * The moment a vendor clears the last listing blocker (business details saved,
 * first bookable specialist added) the panel shows a one-time "your business
 * is live" celebration. One-time has to survive log-outs and new devices, so
 * it is a column rather than a cookie or a session flag.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (! Schema::hasColumn('vendors', 'live_celebrated_at')) {
                $table->timestamp('live_celebrated_at')->nullable()->after('is_profile_complete');
            }
        });

        /*
        | Backfill: a shop that is already live has had its moment. Without
        | this, every established vendor would be congratulated on their next
        | unrelated profile save, which reads as the platform not knowing who
        | they are. Done through the model so "live" means exactly what the
        | celebration checks: getListingBlockers() empty.
        */
        \App\Models\Vendor::query()
            ->whereNull('live_celebrated_at')
            ->where('status', 'active')
            ->with('employees')
            ->get()
            ->each(function ($vendor) {
                if (empty($vendor->getListingBlockers())) {
                    $vendor->forceFill(['live_celebrated_at' => now()])->saveQuietly();
                }
            });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (Schema::hasColumn('vendors', 'live_celebrated_at')) {
                $table->dropColumn('live_celebrated_at');
            }
        });
    }
};
