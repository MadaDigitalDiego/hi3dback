<?php

if (env('APP_ENV') === 'production') {
    $supportedCredentials = false;
} else {
    $supportedCredentials =  true;
}

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['https://dev.hi-3d.com'],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
    
];

