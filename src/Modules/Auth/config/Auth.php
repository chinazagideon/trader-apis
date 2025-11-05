<?php

return [
    'module' => [
        'name' => 'Auth',
        'version' => '1.0.0',
        'description' => 'Authentication and authorization module',
        'author' => 'x4bot',
    ],

    'database' => [
        'connection' => env('AUTH_DB_CONNECTION', 'default'),
        'prefix' => env('AUTH_DB_PREFIX', ''),
    ],

    'api' => [
        'version' => 'v1',
        'prefix' => 'auth',
        'middleware' => ['api'],
    ],

    'guards' => [
        'sanctum' => [
            'driver' => 'sanctum',
            'expiration' => env('SANCTUM_TOKEN_EXPIRATION', 24), // hours
        ],
        'jwt' => [
            'driver' => 'jwt',
            'expiration' => env('JWT_TOKEN_EXPIRATION', 1), // hours
        ],
        'api' => [
            'driver' => 'jwt',
            'expiration' => env('API_TOKEN_EXPIRATION', 1), // hours
        ],
    ],

    'password_reset' => [
        'expiration' => env('PASSWORD_RESET_EXPIRATION', 1), // hours
        'throttle' => env('PASSWORD_RESET_THROTTLE', 60), // seconds
    ],

    'email_verification' => [
        'enabled' => env('EMAIL_VERIFICATION_ENABLED', true),
        'expiration' => env('EMAIL_VERIFICATION_EXPIRATION', 24), // hours
    ],

    'rate_limiting' => [
        'login' => [
            'max_attempts' => env('AUTH_LOGIN_MAX_ATTEMPTS', 5),
            'decay_minutes' => env('AUTH_LOGIN_DECAY_MINUTES', 15),
        ],
        'register' => [
            'max_attempts' => env('AUTH_REGISTER_MAX_ATTEMPTS', 3),
            'decay_minutes' => env('AUTH_REGISTER_DECAY_MINUTES', 60),
        ],
        'password_reset' => [
            'max_attempts' => env('AUTH_PASSWORD_RESET_MAX_ATTEMPTS', 3),
            'decay_minutes' => env('AUTH_PASSWORD_RESET_DECAY_MINUTES', 60),
        ],
    ],

    'security' => [
        'password_min_length' => env('AUTH_PASSWORD_MIN_LENGTH', 8),
        'password_require_special_chars' => env('AUTH_PASSWORD_REQUIRE_SPECIAL', false),
        'password_require_numbers' => env('AUTH_PASSWORD_REQUIRE_NUMBERS', true),
        'password_require_uppercase' => env('AUTH_PASSWORD_REQUIRE_UPPERCASE', false),
        'max_login_attempts' => env('AUTH_MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration' => env('AUTH_LOCKOUT_DURATION', 15), // minutes
        'require_lowercase' => env('PASSWORD_REQUIRE_LOWERCASE', true),

    ],
    
    'features' => [
        'registration' => env('AUTH_REGISTRATION_ENABLED', true),
        'email_verification' => env('AUTH_EMAIL_VERIFICATION_ENABLED', true),
        'password_reset' => env('AUTH_PASSWORD_RESET_ENABLED', true),
        'two_factor' => env('AUTH_TWO_FACTOR_ENABLED', false),
        'social_login' => env('AUTH_SOCIAL_LOGIN_ENABLED', false),
    ],
];
