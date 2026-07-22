<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Coming Soon Gate
    |--------------------------------------------------------------------------
    |
    | When enabled, visitors must enter the access key before browsing the
    | site or calling the public API. Admin (/admin) stays available.
    |
    */

    'enabled' => (bool) env('COMING_SOON_ENABLED', false),

    'access_key' => env('COMING_SOON_ACCESS_KEY'),

    'cookie' => env('COMING_SOON_COOKIE', 'coming_soon_access'),

    /*
    | Cookie lifetime in minutes (default: 30 days).
    */
    'cookie_minutes' => (int) env('COMING_SOON_COOKIE_MINUTES', 60 * 24 * 30),

    /*
    | Header the React client can send after unlock:
    | X-Coming-Soon-Key: <access_key>
    */
    'header' => 'X-Coming-Soon-Key',

    /*
    | Paths that bypass the gate (supports * wildcards).
    */
    'except' => [
        'up',
        'admin',
        'admin/*',
        'coming-soon',
        'unlock',
        'livewire/*',
        'api/v1/coming-soon',
        'api/v1/coming-soon/*',
    ],

];
