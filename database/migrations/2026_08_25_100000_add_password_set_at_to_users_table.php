<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Records whether the account has a password its owner actually chose.
 *
 * A Google sign-up still fills the NOT NULL `password` column — with an
 * unguessable random string — so the column alone cannot tell the two apart.
 * Without that distinction the "change password" card in the vendor settings
 * would demand a current password the Google vendor has never seen, locking
 * them out of ever setting one (the two-way login).
 *
 * Null therefore means "signs in with Google only, no password yet"; a
 * timestamp means "chose this password, ask for it before changing it".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'password_set_at')) {
                $table->timestamp('password_set_at')->nullable()->after('password');
            }
        });

        // Everyone who registered before this existed typed a password on a
        // form, so their account has one. The exception is the accounts created
        // by "Continue with Google" (google_id set, random password) — those
        // stay null, which is exactly what they are.
        DB::table('users')
            ->whereNull('password_set_at')
            ->whereNull('google_id')
            ->update(['password_set_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'password_set_at')) {
                $table->dropColumn('password_set_at');
            }
        });
    }
};
