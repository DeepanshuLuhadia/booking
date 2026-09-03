<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Move vendor_categories onto the single vocabulary the site now uses:
 * health, beauty, sports, education and consultant.
 *
 * The rows are RENAMED rather than replaced. An install that already carries
 * these categories under their older names (barber, doctor, activity, …) has
 * vendors pointing at those ids, and inserting fresh rows beside them would
 * leave every one of those vendors attached to a category the theme matrix no
 * longer knows — no colours, no label, and a duplicate in the sign-up
 * dropdown. Renaming keeps the id, so the foreign key never moves.
 *
 * Where both spellings somehow exist, the older row's vendors are moved onto
 * the canonical row before it is removed.
 *
 * Only renames — creating the five where they are absent is VendorCategorySeeder's
 * job, so a fresh install and an existing one both end up in the same place.
 * Safe to run on an install that is already correct: it then does nothing.
 */
return new class extends Migration
{
    /** every older spelling => the name it becomes */
    private const RENAMES = [
        'doctor' => 'health',
        'clinic' => 'health',
        'barber' => 'beauty',
        'salon' => 'beauty',
        'activity' => 'sports',
        'gym' => 'sports',
        'training' => 'education',
        'consultancy' => 'consultant',
    ];

    /** the five, and the name each is displayed under */
    private const CATEGORIES = [
        'health' => 'Health',
        'beauty' => 'Beauty',
        'sports' => 'Sports',
        'education' => 'Education',
        'consultant' => 'Consultant',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('vendor_categories')) {
            return;
        }

        foreach (self::RENAMES as $legacy => $canonical) {
            $legacyRow = DB::table('vendor_categories')->where('slug', $legacy)->first();

            if (!$legacyRow) {
                continue;
            }

            $canonicalRow = DB::table('vendor_categories')->where('slug', $canonical)->first();

            if ($canonicalRow) {
                // Both spellings present: move the vendors across, then drop
                // the duplicate. Deleting first would break the foreign key.
                $this->repointVendors($legacyRow->id, $canonicalRow->id);
                DB::table('vendor_categories')->where('id', $legacyRow->id)->delete();
                continue;
            }

            DB::table('vendor_categories')->where('id', $legacyRow->id)->update([
                'slug' => $canonical,
                'name' => self::CATEGORIES[$canonical],
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverses the renames far enough to restore the older spellings. The
     * merges above cannot be undone — two rows collapsed into one — so this
     * puts the names back without trying to split them again.
     */
    public function down(): void
    {
        if (!Schema::hasTable('vendor_categories')) {
            return;
        }

        $back = ['health' => 'doctor', 'beauty' => 'barber', 'sports' => 'activity', 'education' => 'training'];

        foreach ($back as $canonical => $legacy) {
            DB::table('vendor_categories')->where('slug', $canonical)->update([
                'slug' => $legacy,
                'name' => ucfirst($legacy),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Move every vendor filed under the duplicate row onto the canonical one,
     * so nothing is left pointing at a category that is about to be deleted.
     */
    private function repointVendors(int $from, int $to): void
    {
        if (!Schema::hasColumn('vendors', 'vendor_category_id')) {
            return;
        }

        DB::table('vendors')
            ->where('vendor_category_id', $from)
            ->update(['vendor_category_id' => $to]);
    }
};
