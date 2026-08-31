<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brand / Project Naming
    |--------------------------------------------------------------------------
    |
    | Every user-facing occurrence of the project name is driven from here so
    | the whole application can be re-branded by editing the .env file only.
    | The canonical full name lives in APP_NAME (config('app.name')) and is
    | reused for <title>, the PWA manifest, e-mails and payment gateways.
    |
    | The logo is rendered as two visually-styled parts, e.g. BOOK|APPOINTMENT,
    | where the suffix receives the accent/gradient colour.
    |
    */

    'logo_prefix' => env('BRAND_LOGO_PREFIX', 'BOOK'),
    'logo_suffix' => env('BRAND_LOGO_SUFFIX', 'APPOINTMENT'),

    // Footer word-mark suffix (e.g. BOOK|AI) and the platform label used in the
    // footer copyright line and marketing call-to-actions.
    'footer_suffix' => env('BRAND_FOOTER_SUFFIX', 'AI'),
    'platform'      => env('BRAND_PLATFORM', 'BookAI Platform'),

    // Short name shown when the site is installed as a PWA to the home screen.
    'short_name' => env('BRAND_SHORT_NAME', 'Booking'),

    // Footer marketing tagline.
    'tagline' => env('BRAND_TAGLINE', 'The Next-Generation Multi-Vendor Booking Experience for Global Professionals.'),

];
