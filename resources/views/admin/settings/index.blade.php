@php
    /*
     | Field metadata for the settings form. Keys must exist in
     | SiteSetting::defaults() — the controller rejects anything else.
     | type: text | email | url | textarea | date-ish text
     */
    $tabs = [
        'company' => ['label' => 'Company & Contact Details', 'hint' => 'Used on the About, Contact and legal pages, and in outgoing emails.'],
        'social'  => ['label' => 'Social Links', 'hint' => 'Shown on the Contact page. Leave blank to hide a network.'],
        'about'   => ['label' => 'About Us Page', 'hint' => 'The copy on the public About Us page.'],
        'contact' => ['label' => 'Contact Page', 'hint' => 'The Contact page intro, where enquiry alerts are sent, and an optional map.'],
        'legal'   => ['label' => 'Terms & Privacy', 'hint' => 'Effective dates, jurisdiction and any extra clauses appended to the legal pages.'],
    ];

    $fields = [
        'company' => [
            'company_name'          => ['Brand Name', 'text', 'Shown across the site and in email footers.'],
            'company_legal_name'    => ['Registered Legal Name', 'text', 'The entity named in the Terms and Privacy Policy.'],
            'company_tagline'       => ['Tagline', 'text', ''],
            'company_email'         => ['Primary Email', 'email', ''],
            'company_support_email' => ['Support Email', 'email', 'Shown to customers as the address to write to.'],
            'company_phone'         => ['Phone', 'text', ''],
            'company_whatsapp'      => ['WhatsApp Number', 'text', 'Used to build the wa.me link on the Contact page.'],
            'company_address'       => ['Street Address', 'text', ''],
            'company_city'          => ['City', 'text', ''],
            'company_state'         => ['State', 'text', ''],
            'company_postal_code'   => ['Postal Code', 'text', ''],
            'company_country'       => ['Country', 'text', ''],
            'company_founded_year'  => ['Founded Year', 'text', ''],
            'company_gstin'         => ['GSTIN / Tax ID', 'text', 'Optional.'],
            'support_hours'         => ['Support Hours', 'text', ''],
        ],
        'social' => [
            'social_instagram' => ['Instagram URL', 'url', ''],
            'social_facebook'  => ['Facebook URL', 'url', ''],
            'social_twitter'   => ['X (Twitter) URL', 'url', ''],
            'social_linkedin'  => ['LinkedIn URL', 'url', ''],
        ],
        'about' => [
            'about_hero_title'    => ['Hero Headline', 'text', ''],
            'about_hero_subtitle' => ['Hero Subheading', 'textarea', '', 3],
            'about_intro'         => ['Who We Are', 'textarea', 'Leave a blank line between paragraphs.', 8],
            'about_mission'       => ['Mission Statement', 'textarea', '', 3],
            'about_vision'        => ['Vision Statement', 'textarea', '', 3],
            'about_story'         => ['Our Story', 'textarea', 'Leave a blank line between paragraphs.', 8],
            'about_values'        => ['Values', 'textarea', 'One value per line. Use "Title — description" to give a card a heading.', 6],
        ],
        'contact' => [
            'contact_intro'         => ['Page Intro', 'textarea', '', 3],
            'contact_response_time' => ['Response Time Note', 'text', 'Shown above the form.'],
            'contact_notify_email'  => ['Send Enquiry Alerts To', 'email', 'Every submitted form is emailed here.'],
            'contact_map_embed_url' => ['Map Embed URL', 'url', 'Optional. Paste the src URL from a Google Maps "Embed a map" iframe.'],
        ],
        'legal' => [
            'terms_effective_date'    => ['Terms Effective Date', 'text', 'Free text, e.g. "1 January 2026".'],
            'privacy_effective_date'  => ['Privacy Effective Date', 'text', ''],
            'legal_governing_city'    => ['Jurisdiction City', 'text', 'Courts named in the governing-law clause.'],
            'legal_governing_state'   => ['Jurisdiction State', 'text', ''],
            'legal_grievance_officer' => ['Grievance Officer Name', 'text', 'Named at the end of the Privacy Policy.'],
            'terms_extra'             => ['Extra Terms Clauses', 'textarea', 'Optional. Appended as a final section of the Terms page.', 8],
            'privacy_extra'           => ['Extra Privacy Clauses', 'textarea', 'Optional. Appended as a final section of the Privacy page.', 8],
        ],
    ];
@endphp

<x-admin-layout>
    <div class="space-y-8">
        <div class="flex flex-col gap-2">
            <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white">Site Settings</h2>
            <p class="text-xs md:text-sm font-medium text-slate-400 uppercase tracking-widest">Content and contact details behind the public pages</p>
        </div>

        <!-- Where each tab shows up -->
        <div class="glass-card p-5 flex flex-wrap items-center gap-x-6 gap-y-2">
            <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Live pages</span>
            @foreach([['About Us', route('about')], ['Contact', route('contact')], ['Terms', route('terms')], ['Privacy', route('privacy')]] as [$label, $url])
                <a href="{{ $url }}" target="_blank" rel="noopener" class="text-[10px] font-black uppercase tracking-widest text-sky-400 hover:text-white transition-colors">
                    {{ $label }} &nearr;
                </a>
            @endforeach
        </div>

        <!-- Tabs -->
        <div class="flex flex-wrap gap-2">
            @foreach($tabs as $key => $meta)
                <a href="{{ route('admin.settings.index', ['tab' => $key]) }}"
                   class="px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all {{ $tab === $key ? 'bg-white text-black' : 'bg-white/10 text-white/60 hover:bg-white/20' }}">
                    {{ $meta['label'] }}
                </a>
            @endforeach
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div class="glass-card p-6 md:p-8">
                <div class="mb-8">
                    <h3 class="text-xl font-black text-white tracking-tight">{{ $tabs[$tab]['label'] }}</h3>
                    <p class="text-xs text-slate-500 font-medium mt-1">{{ $tabs[$tab]['hint'] }}</p>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    @foreach($fields[$tab] as $key => $meta)
                        @php
                            [$label, $type, $help] = [$meta[0], $meta[1], $meta[2]];
                            $rows = $meta[3] ?? 4;
                            $value = old("settings.$key", $settings[$key] ?? '');
                            $wide  = $type === 'textarea';
                        @endphp
                        <div class="space-y-2 {{ $wide ? 'md:col-span-2' : '' }}">
                            <label for="s-{{ $key }}" class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">{{ $label }}</label>

                            @if($type === 'textarea')
                                <textarea id="s-{{ $key }}" name="settings[{{ $key }}]" rows="{{ $rows }}"
                                          class="w-full p-4 bg-white/5 border border-white/10 rounded-xl text-sm font-medium text-white leading-relaxed placeholder:text-white/25 focus:bg-white/10 focus:outline-none">{{ $value }}</textarea>
                            @else
                                <input id="s-{{ $key }}" type="{{ $type === 'email' ? 'email' : ($type === 'url' ? 'url' : 'text') }}"
                                       name="settings[{{ $key }}]" value="{{ $value }}"
                                       class="w-full h-12 px-4 bg-white/5 border border-white/10 rounded-xl text-sm font-semibold text-white placeholder:text-white/25 focus:bg-white/10 focus:outline-none">
                            @endif

                            @error("settings.$key")
                                <p class="text-rose-400 text-[10px] font-black uppercase tracking-widest">{{ $message }}</p>
                            @else
                                @if($help)<p class="text-[11px] text-slate-500 font-medium">{{ $help }}</p>@endif
                            @enderror
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center gap-4 mt-10 pt-8 border-t border-white/10">
                    <button type="submit" class="btn-primary py-3 px-10 text-[10px] font-black uppercase tracking-widest rounded-xl">
                        Save Changes
                    </button>
                    <span class="text-[11px] text-slate-500 font-medium">Saving affects only this tab.</span>
                </div>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.settings.reset') }}"
              onsubmit="return confirm('Restore the {{ $tabs[$tab]['label'] }} fields to their original defaults?')">
            @csrf
            <input type="hidden" name="group" value="{{ $tab }}">
            <button type="submit" class="text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-rose-400 transition-colors">
                Restore this tab to defaults
            </button>
        </form>
    </div>
</x-admin-layout>
