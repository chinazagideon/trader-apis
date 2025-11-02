<?php

return [
    'module' => [
        'name' => 'Funding',
        'version' => '1.0.0',
        'description' => 'Funding module',
        'author' => 'Your Company',
    ],

    'database' => [
        'connection' => env('{{MODULE_NAME}}_DB_CONNECTION', 'default'),
        'prefix' => env('{{MODULE_NAME}}_DB_PREFIX', ''),
    ],

    'api' => [
        'version' => 'v1',
        'prefix' => 'funding',
        'middleware' => ['api', 'auth:sanctum'],
    ],
];