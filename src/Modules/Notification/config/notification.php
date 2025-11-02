<?php

return [
    'module' => [
        'name' => 'Notification',
        'version' => '1.0.0',
        'description' => 'Notification module',
        'author' => 'Your Company',
    ],

    'database' => [
        'connection' => env('NOTIFICATION_DB_CONNECTION', 'default'),
        'prefix' => env('NOTIFICATION_DB_PREFIX', ''),
    ],

    'api' => [
        'version' => 'v1',
        'prefix' => 'notifications',
        'middleware' => ['api', 'auth:sanctum'],
    ],

    'allowed_types' => [
        'user' => \App\Modules\User\Database\Models\User::class,
        'investment' => \App\Modules\Investment\Database\Models\Investment::class,
        'transaction' => \App\Modules\Transaction\Database\Models\Transaction::class,
        'payment' => \App\Modules\Payment\Database\Models\Payment::class,
    ],

     /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    |
    | Supported notification channels. Laravel's built-in channels plus custom.
    |
    */
    'channels' => ['database'],

    /*
    |--------------------------------------------------------------------------
    | Default Channel
    |--------------------------------------------------------------------------
    |
    | The default channel to use when none is specified.
    |
    */
    'default_channel' => env('NOTIFICATION_DEFAULT_CHANNEL', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for queued notification processing.
    |
    */
    'queue' => [
        'enabled' => env('NOTIFICATION_QUEUE_ENABLED', true),
        'connection' => env('NOTIFICATION_QUEUE_CONNECTION', 'redis'),
        'name' => env('NOTIFICATION_QUEUE_NAME', 'notifications'),
        'tries' => env('NOTIFICATION_QUEUE_TRIES', 3),
        'timeout' => env('NOTIFICATION_QUEUE_TIMEOUT', 60),
        'backoff' => env('NOTIFICATION_QUEUE_BACKOFF', '30,60,120'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider Failover
    |--------------------------------------------------------------------------
    |
    | Enable automatic failover to backup providers on failure.
    |
    */
    'failover' => [
        'enabled' => env('NOTIFICATION_FAILOVER_ENABLED', true),
        'max_retries' => env('NOTIFICATION_FAILOVER_RETRIES', 3),
        'retry_delay' => env('NOTIFICATION_FAILOVER_DELAY', 5), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Providers (when DB config not available)
    |--------------------------------------------------------------------------
    |
    | These providers are used as fallback when database configuration
    | is not available. Priority order matters (first = highest priority).
    |
    */
    'providers' => [
        'email' => [
            [
                'name' => 'smtp',
                'driver' => 'smtp',
                'priority' => 1,
            ],
            [
                'name' => 'log',
                'driver' => 'log',
                'priority' => 99,
            ],
        ],
        'sms' => [
            [
                'name' => 'log',
                'driver' => 'log',
                'priority' => 99,
            ],
        ],
        'push' => [
            [
                'name' => 'log',
                'driver' => 'log',
                'priority' => 99,
            ],
        ],
        'slack' => [
            [
                'name' => 'webhook',
                'driver' => 'slack',
                'priority' => 1,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Provider Configurations
    |--------------------------------------------------------------------------
    */
    'email_providers' => [
        'smtp' => [
            'host' => env('MAIL_HOST'),
            'port' => env('MAIL_PORT'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'encryption' => env('MAIL_ENCRYPTION'),
        ],
        'ses' => [
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        ],
        'mailgun' => [
            'domain' => env('MAILGUN_DOMAIN'),
            'secret' => env('MAILGUN_SECRET'),
            'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        ],
        'sendgrid' => [
            'api_key' => env('SENDGRID_API_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Provider Configurations
    |--------------------------------------------------------------------------
    */
    'sms_providers' => [
        'twilio' => [
            'sid' => env('TWILIO_SID'),
            'token' => env('TWILIO_TOKEN'),
            'from' => env('TWILIO_FROM'),
        ],
        'vonage' => [
            'key' => env('VONAGE_KEY'),
            'secret' => env('VONAGE_SECRET'),
            'from' => env('VONAGE_FROM'),
        ],
        'sns' => [
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Push Notification Provider Configurations
    |--------------------------------------------------------------------------
    */
    'push_providers' => [
        'firebase' => [
            'credentials' => env('FIREBASE_CREDENTIALS'),
            'project_id' => env('FIREBASE_PROJECT_ID'),
        ],
        'onesignal' => [
            'app_id' => env('ONESIGNAL_APP_ID'),
            'api_key' => env('ONESIGNAL_API_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Slack Provider Configuration
    |--------------------------------------------------------------------------
    */
    'slack_providers' => [
        'webhook' => [
            'url' => env('SLACK_WEBHOOK_URL'),
        ],
        'api' => [
            'token' => env('SLACK_API_TOKEN'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Templates
    |--------------------------------------------------------------------------
    |
    | Default templates for notifications. Can be overridden by database.
    |
    */
    'templates' => [
        'investment_created' => [
            'subject' => 'Investment Created Successfully',
            'body' => 'Your investment #:id has been created successfully.',
        ],
        'transaction_completed' => [
            'subject' => 'Transaction Completed',
            'body' => 'Your transaction #:id has been completed.',
        ],
        'payment_received' => [
            'subject' => 'Payment Received',
            'body' => 'We have received your payment of :amount.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging & Monitoring
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'log_sent' => env('NOTIFICATION_LOG_SENT', true),
        'log_failed' => env('NOTIFICATION_LOG_FAILED', true),
        'log_provider_switch' => env('NOTIFICATION_LOG_PROVIDER_SWITCH', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cleanup Configuration
    |--------------------------------------------------------------------------
    */
    'cleanup' => [
        'enabled' => env('NOTIFICATION_CLEANUP_ENABLED', true),
        'read_after_days' => env('NOTIFICATION_CLEANUP_READ_DAYS', 30),
        'unread_after_days' => env('NOTIFICATION_CLEANUP_UNREAD_DAYS', 90),
    ],
];
