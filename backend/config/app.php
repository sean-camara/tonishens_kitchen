<?php

return [

    'name' => env('APP_NAME', "Tonishen's Kitchen"),

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    'timezone' => 'Asia/Manila',

    'locale' => 'en',

    'fallback_locale' => 'en',

    'faker_locale' => 'en_PH',

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    'maintenance' => [
        'driver' => 'file',
    ],

    'providers' => [
        // Laravel Framework Service Providers
    ],

    'aliases' => [],
];
