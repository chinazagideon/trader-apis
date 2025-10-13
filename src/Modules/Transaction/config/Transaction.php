<?php

return [
    'module' => [
        'name' => 'Transaction',
        'version' => '1.0.0',
        'description' => 'Transaction module',
        'author' => 'Your Company',
    ],

    'database' => [
        'connection' => env('{{MODULE_NAME}}_DB_CONNECTION', 'default'),
        'prefix' => env('{{MODULE_NAME}}_DB_PREFIX', ''),
    ],

    'api' => [
        'version' => 'v1',
        'prefix' => 'transaction',
        'middleware' => ['api', 'auth:sanctum'],
    ],

    'allowed_types' => [
        'user' => \App\Modules\User\Database\Models\User::class,
        'investment' => \App\Modules\Investment\Database\Models\Investment::class,
    ],
];
