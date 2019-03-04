<?php

declare(strict_types = 1);

return [
    // Path to .env file
    'env' => base_path('.env'),

    'cache' => [
        'config' => storage_path('framework/cache/config.php'),
        'routes' => storage_path('framework/cache/routes.php'),
    ],
];
