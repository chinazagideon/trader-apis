<?php

return [
    'module' => [
        'name' => 'Market',
        'version' => '1.0.0',
        'description' => 'Market module',
        'author' => 'Your Company',
    ],

    'database' => [
        'connection' => env('{{MODULE_NAME}}_DB_CONNECTION', 'default'),
        'prefix' => env('{{MODULE_NAME}}_DB_PREFIX', ''),
    ],

    'api' => [
        'version' => 'v1',
        'prefix' => 'market',
        'middleware' => ['api', 'auth:sanctum'],
    ],
];