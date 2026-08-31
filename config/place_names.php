<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Place Name Corrections
    |--------------------------------------------------------------------------
    |
    | Suburb names come from OpenStreetMap (via Photon), and OSM's data is
    | occasionally misspelled. Jaipur's Jhotwara, for example, is tagged
    | "Jothwara" on the suburb polygon itself — the surrounding roads spell it
    | correctly, but the polygon we read does not, and it carries no alternate
    | name tag to fall back on. Every free geocoder built on OSM repeats it.
    |
    | This map rewrites those names on the way to the screen. Keys are matched
    | case-insensitively against the raw geocoder value; values are what the
    | customer actually sees. Add a line whenever a wrong spelling turns up.
    |
    | The permanent fix for any entry here is to correct the name in
    | OpenStreetMap itself (openstreetmap.org, free to edit) — once the fix
    | propagates, the entry becomes a harmless no-op and can be deleted.
    |
    */

    'aliases' => [
        'jothwara' => 'Jhotwara',
    ],

];
