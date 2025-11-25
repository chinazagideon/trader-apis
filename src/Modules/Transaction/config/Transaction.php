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
        'funding' => \App\Modules\Funding\Database\Models\Funding::class,
        'withdrawal' => \App\Modules\Withdrawal\Database\Models\Withdrawal::class,
        'payment' => \App\Modules\Payment\Database\Models\Payment::class,
    ],
];
