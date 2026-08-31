<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One phone number, one business.
 *
 * `users.mobile` has always been unique, and every path that writes a vendor
 * copies the number from there — so in practice no two shops could share one.
 * "In practice" was the whole problem: the rule lived in three separate
 * validate() calls, and a fourth writer added later would have silently broken
 * it. The database now holds the rule itself, so no code path can.
 *
 * The registration and settings validators stay where they are: they turn this
 * into a sentence the vendor can act on, instead of a constraint violation.
 */
return new class extends Migration
{
    public function up(): void
    {
        if ($this->indexExists()) {
            return;
        }

        /*
        | Refuse to run rather than fail halfway.
        |
        | MySQL's own error for a duplicate names one value and no rows, which
        | is nowhere near enough to fix the data with. If this ever fires, the
        | message says exactly which numbers and which shops to look at.
        */
        $duplicates = DB::table('vendors')
            ->select('contact_number', DB::raw('COUNT(*) as total'))
            ->whereNotNull('contact_number')
            ->where('contact_number', '!=', '')
            ->groupBy('contact_number')
            ->having('total', '>', 1)
            ->pluck('total', 'contact_number');

        if ($duplicates->isNotEmpty()) {
            $detail = $duplicates->map(function ($total, $number) {
                $ids = DB::table('vendors')->where('contact_number', $number)->pluck('id')->implode(', ');

                return "{$number} (vendor ids: {$ids})";
            })->implode('; ');

            throw new RuntimeException(
                'Cannot make vendors.contact_number unique — these numbers are on more than one shop: '
                . $detail . '. Correct them, then run this migration again.'
            );
        }

        Schema::table('vendors', function (Blueprint $table) {
            $table->unique('contact_number', 'vendors_contact_number_unique');
        });
    }

    public function down(): void
    {
        if (! $this->indexExists()) {
            return;
        }

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropUnique('vendors_contact_number_unique');
        });
    }

    /**
     * Guarded so the migration is safe to re-run against a database that
     * already carries the index.
     */
    private function indexExists(): bool
    {
        return collect(Schema::getIndexes('vendors'))
            ->contains(fn ($index) => ($index['name'] ?? null) === 'vendors_contact_number_unique');
    }
};
