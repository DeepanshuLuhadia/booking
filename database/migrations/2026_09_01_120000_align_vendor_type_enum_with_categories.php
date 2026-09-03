<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bring vendors.vendor_type onto the same five names as the categories.
 *
 * The column was created with its own spelling of the trades while the rest of
 * the app moved to the category names, so it rejected most of the values its
 * own sign-up form submitted. This retires the older spelling for good.
 *
 * Nothing here assumes what the column currently holds. The enum's members
 * differ between installs — this one has been edited by hand before — so the
 * live definition and the live values are both read back and carried through
 * the widening step. A value that survives with no mapping is resolved from the
 * vendor's own category, and only falls back to the default if even that is
 * unknown, so no row is silently truncated.
 *
 * Runs after the categories are renamed (…_110000_rename_vendor_categories…),
 * which is what makes that per-vendor fallback trustworthy.
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

    private const CATEGORIES = ['health', 'beauty', 'sports', 'education', 'consultant'];

    private const FALLBACK = 'consultant';

    public function up(): void
    {
        if (!Schema::hasColumn('vendors', 'vendor_type')) {
            return;
        }

        // Widen to everything currently legal or currently stored, plus the
        // five names. Narrowing in one step would truncate the old values.
        $this->setEnum(
            array_unique(array_merge($this->currentMembers(), $this->currentValues(), self::CATEGORIES)),
            self::FALLBACK
        );

        foreach (self::RENAMES as $legacy => $category) {
            DB::table('vendors')->where('vendor_type', $legacy)->update(['vendor_type' => $category]);
        }

        $this->resolveStragglers();

        $this->setEnum(self::CATEGORIES, self::FALLBACK);
    }

    public function down(): void
    {
        if (!Schema::hasColumn('vendors', 'vendor_type')) {
            return;
        }

        $legacy = ['doctor', 'salon', 'sports', 'training', 'consultancy'];

        // array_unique: 'sports' is spelt the same in both sets, and MySQL
        // rejects an ENUM that lists a value twice.
        $this->setEnum(array_unique(array_merge(self::CATEGORIES, $legacy)), 'consultancy');

        foreach (['health' => 'doctor', 'beauty' => 'salon', 'education' => 'training', 'consultant' => 'consultancy'] as $category => $old) {
            DB::table('vendors')->where('vendor_type', $category)->update(['vendor_type' => $old]);
        }

        $this->setEnum($legacy, 'consultancy');
    }

    /**
     * Anything still outside the five — a value this install invented that the
     * rename table has never heard of. The vendor's own category is the better
     * answer than a blanket default, so use it wherever there is one.
     */
    private function resolveStragglers(): void
    {
        $stragglers = DB::table('vendors')
            ->whereNotIn('vendor_type', self::CATEGORIES)
            ->pluck('vendor_type')
            ->unique();

        if ($stragglers->isEmpty()) {
            return;
        }

        if (Schema::hasColumn('vendors', 'vendor_category_id')) {
            DB::table('vendors')
                ->join('vendor_categories', 'vendors.vendor_category_id', '=', 'vendor_categories.id')
                ->whereNotIn('vendors.vendor_type', self::CATEGORIES)
                ->whereIn('vendor_categories.slug', self::CATEGORIES)
                ->update(['vendors.vendor_type' => DB::raw('vendor_categories.slug')]);
        }

        DB::table('vendors')
            ->whereNotIn('vendor_type', self::CATEGORIES)
            ->update(['vendor_type' => self::FALLBACK]);
    }

    /** The enum members the column accepts right now. */
    private function currentMembers(): array
    {
        $column = collect(DB::select("SHOW COLUMNS FROM vendors LIKE 'vendor_type'"))->first();

        if (!$column) {
            return [];
        }

        preg_match_all("/'((?:[^']|'')*)'/", $column->Type, $matches);

        return array_map(fn ($m) => str_replace("''", "'", $m), $matches[1]);
    }

    /** The values actually stored right now. */
    private function currentValues(): array
    {
        return DB::table('vendors')
            ->select('vendor_type')
            ->distinct()
            ->pluck('vendor_type')
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->values()
            ->all();
    }

    /** @param array<int, string> $members */
    private function setEnum(array $members, string $default): void
    {
        $list = collect($members)
            ->map(fn ($m) => "'" . str_replace("'", "''", $m) . "'")
            ->implode(', ');

        DB::statement(sprintf(
            "ALTER TABLE `vendors` MODIFY `vendor_type` ENUM(%s) NOT NULL DEFAULT '%s'",
            $list,
            $default
        ));
    }
};
