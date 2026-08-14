<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Admin-editable key/value settings powering the public content pages.
 *
 * Every read goes through the `site_settings.all` cache entry so rendering a
 * page costs one query at most (and zero once warm). Writing any key flushes
 * that entry.
 *
 * Anything missing from the table falls back to {@see self::defaults()}, so a
 * fresh install renders complete, sensible pages before an admin has touched
 * the settings screen.
 */
class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    public const CACHE_KEY = 'site_settings.all';

    /**
     * Shipped defaults. The admin screen is built from this list, so adding a
     * key here is all that is needed to expose a new editable field.
     *
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return [
            // --- Company identity (group: company) -----------------------
            'company_name'        => config('app.name', 'Book Appointment'),
            'company_legal_name'  => config('app.name', 'Book Appointment') . ' Technologies Pvt. Ltd.',
            'company_tagline'     => config('brand.tagline'),
            'company_email'       => config('support.admin_email'),
            'company_support_email' => config('support.admin_email'),
            'company_phone'       => config('support.admin_phone'),
            'company_whatsapp'    => config('support.admin_phone'),
            'company_address'     => 'Level 4, Business Avenue, MG Road',
            'company_city'        => 'Bengaluru',
            'company_state'       => 'Karnataka',
            'company_postal_code' => '560001',
            'company_country'     => 'India',
            'company_founded_year' => '2024',
            'company_gstin'       => '',
            'support_hours'       => 'Monday to Saturday, 9:00 AM – 7:00 PM IST',

            // --- Social links (group: social) ----------------------------
            'social_facebook'  => '',
            'social_instagram' => '',
            'social_twitter'   => '',
            'social_linkedin'  => '',

            // --- About Us page (group: about) ----------------------------
            'about_hero_title'    => 'Appointments without the waiting room.',
            'about_hero_subtitle' => 'We connect neighbourhood businesses and the people they serve through one live, honest queue.',
            'about_intro'         => "We started with a small, stubborn problem: nobody enjoys waiting, and no business enjoys making people wait. Between the phone calls, the notebook at the counter and the customers who show up at the same moment, a good day at a busy shop quietly turns into a bad experience for someone.\n\nOur platform gives every business a live token queue that customers can join from their phone in seconds — no app to install, no account to create, no guessing how long it will take. Businesses get a dashboard that runs the floor for them, and customers get a number, a realistic wait time and a notification when their turn is close.",
            'about_mission'       => 'To make every local appointment as predictable as an online order — booked in seconds, tracked in real time, and honoured on time.',
            'about_vision'        => 'A world where no customer stands in a queue they cannot see, and no business loses a customer to a busy phone line.',
            'about_story'         => "The first version of this platform ran in a single salon, on a borrowed tablet at the reception desk. The owner was tracking walk-ins on paper and losing track of who arrived first. We replaced the paper with a live queue, and the arguments at the counter stopped the same week.\n\nWhat began as a queue for one shop now serves salons, clinics, consultants, service centres and studios — each with their own specialists, their own working hours and their own way of running a day. The core promise has not changed since that tablet: show people the truth about their wait, and everything else gets easier.",
            'about_values'        => "Honest waits — a token means nothing if the time attached to it is a guess.\nNo forced sign-ups — customers book with a phone number, not a password.\nThe business stays in charge — pricing, staff, timings and availability are always the owner's call.\nPrivacy by default — we collect the minimum needed to hold a booking, and nothing we would not want collected about us.",

            // --- Contact page (group: contact) ---------------------------
            'contact_intro'         => 'Questions about a booking, interested in listing your business, or reporting something that looks wrong? Send us a message and a real person will get back to you.',
            'contact_notify_email'  => config('support.admin_email'),
            'contact_map_embed_url' => '',
            'contact_response_time' => 'We reply to most messages within one business day.',

            // --- Legal pages (group: legal) ------------------------------
            'terms_effective_date'   => '1 January 2026',
            'privacy_effective_date' => '1 January 2026',
            'legal_governing_city'   => 'Bengaluru',
            'legal_governing_state'  => 'Karnataka',
            'legal_grievance_officer' => 'Grievance Officer',
            'terms_extra'            => '',
            'privacy_extra'          => '',
        ];
    }

    /**
     * Which tab each key belongs to on the admin settings screen.
     */
    public static function groupFor(string $key): string
    {
        return match (true) {
            str_starts_with($key, 'social_')  => 'social',
            str_starts_with($key, 'about_')   => 'about',
            str_starts_with($key, 'contact_') => 'contact',
            str_starts_with($key, 'terms_'), str_starts_with($key, 'privacy_'), str_starts_with($key, 'legal_') => 'legal',
            default => 'company',
        };
    }

    /**
     * Every setting, stored values layered over the shipped defaults.
     *
     * @return array<string, string>
     */
    public static function all_settings(): array
    {
        $stored = Cache::rememberForever(self::CACHE_KEY, function () {
            // Guarded so `migrate` on a fresh database (and any boot-time read
            // before this table exists) cannot blow up.
            if (! Schema::hasTable('site_settings')) {
                return [];
            }

            return static::query()->pluck('value', 'key')->all();
        });

        // Only keys never saved fall back to a default. An admin who clears a
        // field (say, the WhatsApp number) means it, and the empty string has
        // to survive — otherwise the default reappears and the field cannot be
        // removed from the public pages at all.
        return array_merge(self::defaults(), array_filter(
            $stored,
            fn ($value) => $value !== null
        ));
    }

    /**
     * Read one setting, falling back to its shipped default.
     */
    public static function get(string $key, ?string $fallback = null): ?string
    {
        return self::all_settings()[$key] ?? $fallback;
    }

    /**
     * Persist one setting and drop the cache.
     */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => self::groupFor($key)]
        );

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Persist many settings in one pass (one cache flush, not N).
     *
     * @param array<string, string|null> $values
     */
    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => self::groupFor($key)]
            );
        }

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Company address as a single display line.
     */
    public static function fullAddress(string $separator = ', '): string
    {
        $parts = array_filter([
            self::get('company_address'),
            self::get('company_city'),
            self::get('company_state'),
            self::get('company_postal_code'),
            self::get('company_country'),
        ], fn ($p) => trim((string) $p) !== '');

        return implode($separator, $parts);
    }
}
