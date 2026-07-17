<?php

$webConfigPath = storage_path('app/firebase/web-config.json');
$webConfig = is_file($webConfigPath)
    ? (json_decode((string) file_get_contents($webConfigPath), true) ?: [])
    : [];

$credentialsPath = env('FIREBASE_CREDENTIALS', storage_path('app/firebase/service-account.json'));

return [
    /*
    | Enable sending once a real service-account.json is in place.
    | Client registration can still use web-config.json.
    */
    'enabled' => (bool) env('FIREBASE_ENABLED', true),

    'project_id' => env('FIREBASE_PROJECT_ID', $webConfig['projectId'] ?? 'al-shaheen-360'),

    'credentials' => $credentialsPath,

    'web_config_path' => $webConfigPath,

    'web' => [
        'apiKey'            => env('FIREBASE_API_KEY', $webConfig['apiKey'] ?? null),
        'authDomain'        => env('FIREBASE_AUTH_DOMAIN', $webConfig['authDomain'] ?? null),
        'projectId'         => env('FIREBASE_PROJECT_ID', $webConfig['projectId'] ?? null),
        'storageBucket'     => env('FIREBASE_STORAGE_BUCKET', $webConfig['storageBucket'] ?? null),
        'messagingSenderId' => env('FIREBASE_MESSAGING_SENDER_ID', $webConfig['messagingSenderId'] ?? null),
        'appId'             => env('FIREBASE_APP_ID', $webConfig['appId'] ?? null),
        'vapidKey'          => env('FIREBASE_VAPID_KEY', $webConfig['vapidKey'] ?? null),
    ],
];
