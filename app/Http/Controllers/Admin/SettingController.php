<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Admin screen for the content behind the public pages.
 *
 * Everything editable here is declared in {@see SiteSetting::defaults()} — the
 * form is generated from that list, and only keys present in it are accepted,
 * so a crafted POST cannot introduce arbitrary settings.
 */
class SettingController extends Controller
{
    /**
     * Fields that must look like an email address / URL when filled in.
     */
    private const EMAIL_KEYS = ['company_email', 'company_support_email', 'contact_notify_email'];
    private const URL_KEYS   = ['social_facebook', 'social_instagram', 'social_twitter', 'social_linkedin', 'contact_map_embed_url'];

    public function index(Request $request)
    {
        $tab = $request->query('tab', 'company');

        return view('admin.settings.index', [
            'settings' => SiteSetting::all_settings(),
            'tab'      => in_array($tab, ['company', 'social', 'about', 'contact', 'legal'], true) ? $tab : 'company',
        ]);
    }

    public function update(Request $request)
    {
        $allowed = array_keys(SiteSetting::defaults());

        // Only the keys this submission actually carried are touched, so saving
        // one tab cannot blank out the fields on another.
        $submitted = array_intersect_key(
            (array) $request->input('settings', []),
            array_flip($allowed)
        );

        $rules = [];
        foreach (array_keys($submitted) as $key) {
            $rules["settings.$key"] = match (true) {
                in_array($key, self::EMAIL_KEYS, true) => ['nullable', 'email:rfc', 'max:190'],
                in_array($key, self::URL_KEYS, true)   => ['nullable', 'url', 'max:500'],
                default => ['nullable', 'string', 'max:20000'],
            };
        }

        // company_name is the one field the pages cannot render without.
        if (array_key_exists('company_name', $submitted)) {
            $rules['settings.company_name'] = ['required', 'string', 'max:150'];
        }

        $request->validate($rules, [
            'settings.*.email' => 'Enter a valid email address.',
            'settings.*.url'   => 'Enter a full URL including https://',
        ]);

        SiteSetting::setMany(array_map(
            fn ($value) => is_string($value) ? trim($value) : $value,
            $submitted
        ));

        // The About page caches its headline counts; drop them so an admin who
        // just edited the page does not see a stale render.
        Cache::forget('pages.about.stats');

        return back()->with('success', 'Settings saved. The public pages are updated.');
    }

    /**
     * Restore one tab to the shipped defaults.
     */
    public function reset(Request $request)
    {
        $data = $request->validate([
            'group' => ['required', 'in:company,social,about,contact,legal'],
        ]);

        $keys = array_filter(
            array_keys(SiteSetting::defaults()),
            fn ($key) => SiteSetting::groupFor($key) === $data['group']
        );

        SiteSetting::whereIn('key', $keys)->delete();
        Cache::forget(SiteSetting::CACHE_KEY);
        Cache::forget('pages.about.stats');

        return back()->with('success', ucfirst($data['group']) . ' settings restored to defaults.');
    }
}
