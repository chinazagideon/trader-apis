<?php

return [
    'module' => [
        'name' => 'Role',
        'version' => '1.0.0',
        'description' => 'Manage access application  previllages',
        'author' => 'Your Company',
    ],

    'database' => [
        'connection' => env('{{MODULE_NAME}}_DB_CONNECTION', 'default'),
        'prefix' => env('{{MODULE_NAME}}_DB_PREFIX', ''),
    ],

    'api' => [
        'version' => 'v1',
        'prefix' => 'role',
        'middleware' => ['api', 'auth:sanctum'],
    ],
];