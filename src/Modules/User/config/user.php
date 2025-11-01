<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Module Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the User module.
    | You can customize these settings based on your application needs.
    |
    */

    'module' => [
        'name' => 'User',
        'version' => '1.0.0',
        'description' => 'User management module',
        'author' => 'Your Company',
    ],

    'database' => [
        'connection' => env('USER_DB_CONNECTION', 'default'),
        'prefix' => env('USER_DB_PREFIX', ''),
    ],

    'api' => [
        'version' => 'v1',
        'prefix' => 'users',
        'middleware' => ['api', 'auth:sanctum'],
        'rate_limit' => [
            'max_attempts' => 60,
            'decay_minutes' => 1,
        ],
    ],

    'validation' => [
        'rules' => [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean',
        ],
    ],

    'pagination' => [
        'default_per_page' => 15,
        'max_per_page' => 100,
    ],

    'features' => [
        'soft_deletes' => true,
        'uuid' => true,
        'api_tokens' => true,
        'email_verification' => true,
        'password_reset' => true,
    ],

    'cache' => [
        'enabled' => env('USER_CACHE_ENABLED', true),
        'ttl' => env('USER_CACHE_TTL', 3600), // 1 hour
        'prefix' => 'user_module',
    ],
    'min_withdrawal' => 100,
    'max_deposit' => 1000000,
    'min_payment' => 0,
    'allow_auto_credit' => env('ALLOW_AUTO_CREDIT', true),
];
