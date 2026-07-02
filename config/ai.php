<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Article Improvement
    |--------------------------------------------------------------------------
    |
    | Disabled by default. Enable from Admin → AI Settings or set AI_ENABLED=true.
    | OPENAI_API_KEY in .env is used as fallback when dashboard key is empty.
    | Suggestions are stored separately — never auto-applied to articles.
    |
    */

    'enabled' => (bool) env('AI_ENABLED', false),

    'provider' => env('AI_PROVIDER', 'openai'),

    'openai' => [
        'api_key'  => env('OPENAI_API_KEY'),
        'model'    => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'timeout'  => (int) env('OPENAI_TIMEOUT', 60),
    ],

];
