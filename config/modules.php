<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Module Provider Discovery Caching
    |--------------------------------------------------------------------------
    |
    | This option controls whether module provider discovery should be cached.
    | In production, this should always be true for optimal performance.
    | In development, set to false for auto-discovery on file changes.
    |
    */
    'cache_discovery' => env('MODULE_CACHE_DISCOVERY', env('APP_ENV') === 'production'),

    /*
    |--------------------------------------------------------------------------
    | Module Provider Cache TTL
    |--------------------------------------------------------------------------
    |
    | The number of seconds to cache the module providers manifest.
    | Only applies when cache_discovery is true and config is not cached.
    |
    */
    'cache_ttl' => env('MODULE_CACHE_TTL', 86400), // 24 hours

    /*
    |--------------------------------------------------------------------------
    | Log Provider Discovery
    |--------------------------------------------------------------------------
    |
    | Enable detailed logging of provider discovery and registration.
    | This should only be enabled in development for debugging purposes.
    |
    */
    'log_discovery' => env('MODULE_LOG_DISCOVERY', false),

    /*
    |--------------------------------------------------------------------------
    | Module Directories
    |--------------------------------------------------------------------------
    |
    | The directories where modules are located. By default, modules are
    | located in the src/Modules directory.
    |
    */
    'directories' => [
        base_path('src/Modules'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Required Module Structure
    |--------------------------------------------------------------------------
    |
    | The required directories that must exist for a directory to be
    | considered a valid module.
    |
    */
    'required_structure' => [
        'Providers',
        'Services',
        'Http/Controllers',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Discovery
    |--------------------------------------------------------------------------
    |
    | Enable or disable automatic module discovery. When disabled, you must
    | manually register modules in the modules.manifest configuration.
    |
    */
    'auto_discovery' => env('MODULE_AUTO_DISCOVERY', true),

    /*
    |--------------------------------------------------------------------------
    | Module Manifest
    |--------------------------------------------------------------------------
    |
    | Manually define modules and their providers. This is used when
    | auto_discovery is disabled or as an override for specific modules.
    | This is the recommended approach for production environments.
    |
    */
    'manifest' => [
        // Example:
        // 'Transaction' => [
        //     'providers' => [
        //         \App\Modules\Transaction\Providers\TransactionServiceProvider::class,
        //         \App\Modules\Transaction\Providers\TransactionEventServiceProvider::class,
        //     ],
        // ],
    ],
];

