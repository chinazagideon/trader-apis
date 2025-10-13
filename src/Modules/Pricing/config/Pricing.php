<?php

return [
    'module' => [
        'name' => 'Pricing',
        'version' => '1.0.0',
        'description' => 'Manage Pricing',
        'author' => 'x4',
    ],

    'database' => [
        'connection' => env('{{MODULE_NAME}}_DB_CONNECTION', 'default'),
        'prefix' => env('{{MODULE_NAME}}_DB_PREFIX', ''),
    ],

    'api' => [
        'version' => 'v1',
        'prefix' => 'pricing',
        'middleware' => ['api', 'auth:sanctum'],
    ],
];