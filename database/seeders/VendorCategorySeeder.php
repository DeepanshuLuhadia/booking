<?php

namespace Database\Seeders;

use App\Models\VendorCategory;
use App\Services\ThemeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * The five categories the site is built around.
 *
 * The list comes from ThemeService rather than being repeated here, so the one
 * name a category has — its theme key, its vendor_categories.slug, its
 * vendors.vendor_type enum member and its /category/{slug} URL — is defined in
 * a single place. Adding a theme is what adds a category.
 *
 * Re-runnable. Rows are keyed on the slug, so a second run refreshes the same
 * five rather than duplicating them, and any image uploaded through the admin
 * is left alone.
 *
 *   php artisan db:seed --class=VendorCategorySeeder
 *
 * Set TRUNCATE=1 to empty the table first so the five land on ids 1-5. (An
 * environment variable rather than a flag because db:seed reads any extra
 * argument as another seeder class.) Truncating is refused while vendors still
 * reference a category, since the foreign key would orphan them:
 *
 *   TRUNCATE=1 php artisan db:seed --class=VendorCategorySeeder
 */
class VendorCategorySeeder extends Seeder
{
    public function run(): void
    {
        if (filter_var(env('TRUNCATE', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->truncate();
        }

        foreach (ThemeService::getAllThemes() as $slug => $theme) {
            $cat = VendorCategory::updateOrCreate(
                ['slug' => $slug],
                ['name' => $theme['label'] ?? ucfirst($slug)]
            );

            // Ensure all vendors of this type/slug are linked to this category ID
            $canonicalKey = ThemeService::getTheme($slug)['key'] ?? $slug;
            DB::table('vendors')
                ->where('vendor_type', $slug)
                ->orWhere('vendor_type', $canonicalKey)
                ->update(['vendor_category_id' => $cat->id]);
        }

        \Illuminate\Support\Facades\Cache::forget('all_themes');
        \Illuminate\Support\Facades\Cache::flush();

        $this->command?->info(sprintf(
            'Seeded %d vendor categories and synced vendor category IDs: %s.',
            VendorCategory::count(),
            VendorCategory::orderBy('id')->get()->map(fn ($c) => "{$c->slug} ({$c->name})")->implode(', ')
        ));
    }

    /**
     * Empty the table so the five land on ids 1–5.
     *
     * Guarded rather than forced: a vendor pointing at a category that is about
     * to disappear is a data-loss bug, and TRUNCATE would either fail on the
     * foreign key or, with checks disabled, leave the vendor pointing at
     * nothing. Reassign or remove those vendors first.
     */
    private function truncate(): void
    {
        $inUse = DB::table('vendors')->whereNotNull('vendor_category_id')->count();

        if ($inUse > 0) {
            $this->command?->warn(sprintf(
                'Skipping --truncate: %d vendor(s) still reference a category. '
                . 'Existing rows will be updated in place instead.',
                $inUse
            ));
            return;
        }

        VendorCategory::query()->delete();
        DB::statement('ALTER TABLE vendor_categories AUTO_INCREMENT = 1');
        $this->command?->info('Truncated vendor_categories.');
    }
}
