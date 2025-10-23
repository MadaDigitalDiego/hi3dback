<?php

if (env('APP_ENV') === 'production') {
    $supportedCredentials = false;
} else {
    $supportedCredentials =  true;
}

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:3000'),
        env('APP_URL', 'http://localhost:8000'),
        'https://hi3d.mada-digital.xyz',
        'https://hi-3d.salon.mada-digital.xyz',
        'https://backhi3d.mada-digital.xyz',
        'https://dev-backend.hi-3d.com',
        'https://dev2.mada-digital.xyz',
        'https://dev2.hi-3d.com',
        'http://localhost:3000',
        'http://localhost:3001',
    ],

    'allowed_origins_patterns' => [
        '/^https?:\/\/.*\.mada-digital\.xyz$/',
        '/^https?:\/\/.*\.hi-3d\.com$/',
        '/^https?:\/\/localhost(:\d+)?$/',
    ],

    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
        'Origin',
        'Cache-Control',
        'Pragma',
        'Content-Length',
        'X-Content-Length',
        'X-File-Size',
        'X-File-Name',
        'X-File-Type',
    ],

    'exposed_headers' => [
        'Content-Length',
        'X-Content-Length',
        'X-File-Size',
        'X-File-Name',
        'X-File-Type',
    ],

    'max_age' => 0,

    'supports_credentials' => $supportedCredentials,

];

