<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'webhooks/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '')),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Accept', 'Accept-Language', 'Authorization', 'Content-Type', 'X-Requested-With', 'X-CSRF-TOKEN', 'Omise-Signature', 'Omise-Signature-Timestamp'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
