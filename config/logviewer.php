<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pattern and storage path settings
    |--------------------------------------------------------------------------
    |
    | The env key for pattern and storage path with a default value
    |
    */
    'max_file_size' => 52428800, // size in Byte (50MB)
    'pattern'       => env('LOGVIEWER_PATTERN', '*.log'),
    'storage_path'  => env('LOGVIEWER_STORAGE_PATH', storage_path('logs')),

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Configure access control for the log viewer
    |
    */
    'middleware' => ['web'], // Add auth middleware if needed: ['web', 'auth']

    /*
    |--------------------------------------------------------------------------
    | Environment Restrictions
    |--------------------------------------------------------------------------
    |
    | Only allow log viewer in specific environments
    |
    */
    'allowed_environments' => ['local', 'development', 'dev'],

    /*
    |--------------------------------------------------------------------------
    | Log File Patterns
    |--------------------------------------------------------------------------
    |
    | Define patterns for different log types in your application
    |
    */
    'log_patterns' => [
        'laravel' => 'laravel*.log',
        'operations' => 'operations*.log',
        'performance' => 'performance*.log',
        'business' => 'business*.log',
        'errors' => 'errors*.log',
    ],
];
