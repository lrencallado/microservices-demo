<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => explode(',', env('ALLOWED_ORIGINS', sprintf('%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1'
    ))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
