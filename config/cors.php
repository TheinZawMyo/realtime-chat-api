<?php

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

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'broadcasting/auth'], // Ensure 'sanctum/csrf-cookie' is included
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:5173'], // Adjust for production
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['X-XSRF-TOKEN', 'Authorization', 'Content-Type', 'Accept'], // Allow all headers
    'exposed_headers' => ['Authorization', 'X-XSRF-TOKEN'],
    'max_age' => 0,
    'supports_credentials' => true,
];
