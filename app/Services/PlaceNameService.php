<?php

namespace App\Services;

/**
 * Corrects place names on their way to the screen.
 *
 * Suburbs are read from OpenStreetMap, whose data carries the occasional
 * misspelling that no geocoder can work around (see config/place_names.php).
 * The raw value stays in the cookie; only the displayed label is corrected, so
 * a later fix upstream needs no data migration.
 */
class PlaceNameService
{
    /**
     * Apply the configured correction to a place name, if one exists.
     * Unknown names pass through untouched.
     */
    public static function correct(?string $name): ?string
    {
        $name = is_string($name) ? trim($name) : null;

        if ($name === null || $name === '') {
            return $name;
        }

        $aliases = config('place_names.aliases', []);

        // Case-insensitive match, so "JOTHWARA" and "Jothwara" both correct.
        foreach ($aliases as $wrong => $right) {
            if (mb_strtolower((string) $wrong) === mb_strtolower($name)) {
                return $right;
            }
        }

        return $name;
    }

    /**
     * The correction map in the shape the browser needs — lowercased keys, so
     * the client-side suburb backfill corrects labels the same way Blade does.
     */
    public static function aliasMap(): array
    {
        $map = [];

        foreach (config('place_names.aliases', []) as $wrong => $right) {
            $map[mb_strtolower((string) $wrong)] = $right;
        }

        return $map;
    }
}
