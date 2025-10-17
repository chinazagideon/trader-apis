<?php

return [
    'module' => [
        'name' => 'Withdrawal',
        'version' => '1.0.0',
        'description' => 'Withdrawal module',
        'author' => 'Your Company',
    ],

    'database' => [
        'connection' => env('{{MODULE_NAME}}_DB_CONNECTION', 'default'),
        'prefix' => env('{{MODULE_NAME}}_DB_PREFIX', ''),
    ],

    'api' => [
        'version' => 'v1',
        'prefix' => 'withdrawal',
        'middleware' => ['api', 'auth:sanctum'],
    ],
];