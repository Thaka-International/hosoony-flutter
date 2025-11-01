<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // ✅ Explicit origins for both local dev and production
    'allowed_origins' => [
        'http://localhost',
        'http://localhost:*',
        'http://127.0.0.1',
        'http://127.0.0.1:*',
        'https://thakaa.me',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // ✅ Credentials allowed for Sanctum / Flutter Web with cookies or tokens
    'supports_credentials' => true,
];