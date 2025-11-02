<?php

return [
    'module' => [
        'name' => 'Payment',
        'version' => '1.0.0',
        'description' => 'Payment module',
        'author' => 'Your Company',
    ],

    'database' => [
        'connection' => env('PAYMENT_DB_CONNECTION', 'default'),
        'prefix' => env('PAYMENT_DB_PREFIX', ''),
    ],

    'api' => [
        'version' => 'v1',
        'prefix' => 'payment',
        'middleware' => ['api', 'auth:sanctum'],
    ],

    'allowed_types' => [
        'user' => \App\Modules\User\Database\Models\User::class,
        'investment' => \App\Modules\Investment\Database\Models\Investment::class,
        'transaction' => \App\Modules\Transaction\Database\Models\Transaction::class,
        'funding' => \App\Modules\Funding\Database\Models\Funding::class,
    ],
];
