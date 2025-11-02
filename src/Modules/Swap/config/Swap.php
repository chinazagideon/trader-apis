<?php

return [
    'module' => [
        'name' => 'Swap',
        'version' => '1.0.0',
        'description' => 'Swap module',
        'author' => 'Your Company',
    ],

    'database' => [
        'connection' => env('{{MODULE_NAME}}_DB_CONNECTION', 'default'),
        'prefix' => env('{{MODULE_NAME}}_DB_PREFIX', ''),
    ],

    'api' => [
        'version' => 'v1',
        'prefix' => 'swap',
        'middleware' => ['api', 'auth:sanctum'],
    ],
];