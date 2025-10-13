<?php

return [
    'module' => [
        'name' => 'Currency',
        'version' => '1.0.0',
        'description' => 'Manage App Currency',
        'author' => 'Your Company',
    ],

    'database' => [
        'connection' => env('{{MODULE_NAME}}_DB_CONNECTION', 'default'),
        'prefix' => env('{{MODULE_NAME}}_DB_PREFIX', ''),
    ],

    'api' => [
        'version' => 'v1',
        'prefix' => 'currency',
        'middleware' => ['api', 'auth:sanctum'],
    ],
];