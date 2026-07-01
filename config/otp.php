<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OTP Authentication Toggle
    |--------------------------------------------------------------------------
    |
    | When enabled, users are required to verify their mobile number via a
    | one-time password (OTP) as part of the auth flow. When disabled, no OTP
    | is generated or sent and every OTP check is skipped, letting the rest of
    | the flow proceed without mobile verification.
    |
    */

    'enabled' => env('OTP_ENABLED', false),

];
