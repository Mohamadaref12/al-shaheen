<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Coming Soon Gate (API only)
    |--------------------------------------------------------------------------
    |
    | When enabled, public API calls require the access key header/cookie.
    | The Coming Soon UI is handled by the React frontend — not this app.
    | Admin (/admin) and coming-soon API endpoints stay available.
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
    | Matched against the request path (e.g. api/v1/...).
    */
    'except' => [
        'up',
        'api/v1/coming-soon',
        'api/v1/coming-soon/*',
    ],

];
