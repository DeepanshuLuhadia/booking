<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin / Support Contact Details
    |--------------------------------------------------------------------------
    |
    | Shown to vendors who are awaiting admin approval so they have a direct
    | way to reach the platform team. Used by the "approval pending" screen
    | that gates the vendor panel while OTP verification is disabled.
    |
    */

    'admin_email' => env('ADMIN_CONTACT_EMAIL', 'apnibaari.support@gmail.com'),

    'admin_phone' => env('ADMIN_CONTACT_PHONE', '+917610090041'),

];
