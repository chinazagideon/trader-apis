<?php

return [
    'module' => [
        'name' => 'Category',
        'version' => '1.0.0',
        'description' => 'Category module',
        'author' => 'Your Company',
    ],

    'database' => [
        'connection' => env('{{MODULE_NAME}}_DB_CONNECTION', 'default'),
        'prefix' => env('{{MODULE_NAME}}_DB_PREFIX', ''),
    ],

    'api' => [
        'version' => 'v1',
        'prefix' => 'category',
        'middleware' => ['api', 'auth:sanctum'],
    ],
];