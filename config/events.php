<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Event System Enable/Disable
    |--------------------------------------------------------------------------
    |
    | Master switch for the event-driven architecture. When disabled, events
    | are not dispatched. Useful for debugging or emergency rollback.
    |
    */

    'enabled' => env('EVENT_SYSTEM_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Event Processing Mode
    |--------------------------------------------------------------------------
    |
    | Controls how events are processed globally. Can be overridden per event.
    |
    | Supported: "sync", "queue", "scheduled"
    |
    | - sync: Process events immediately in the same request
    | - queue: Dispatch to queue workers for async processing
    | - scheduled: Store and process in batches via cron
    |
    */

    'default_mode' => env('EVENT_PROCESSING_MODE', 'sync'),

    /*
    |--------------------------------------------------------------------------
    | Processing Configuration
    |--------------------------------------------------------------------------
    |
    | Global processing settings for events.
    |
    */

    'processing' => [
        'mode' => env('EVENT_PROCESSING_MODE', 'sync'),
        'queue_connection' => env('EVENT_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'redis')),
        'queue_name' => env('EVENT_QUEUE_NAME', 'events'),
        'max_tries' => env('EVENT_QUEUE_TRIES', 3),
        'timeout_seconds' => env('EVENT_QUEUE_TIMEOUT', 60),
        'backoff' => env('EVENT_QUEUE_BACKOFF', '30,60,120'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Mode Overrides by Environment
    |--------------------------------------------------------------------------
    |
    | Override processing mode based on environment. Takes precedence over
    | default_mode if environment matches.
    |
    */

    'mode_overrides' => [
        'local' => 'sync',      // Development: immediate feedback
        'testing' => 'sync',    // Tests: synchronous execution
        'staging' => 'queue',   // Staging: production-like
        'production' => 'queue', // Production: async by default
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for queued event processing.
    |
    */

    'queue' => [
        'connection' => env('EVENT_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'sync')),
        'name' => env('EVENT_QUEUE_NAME', 'events'),
        'tries' => env('EVENT_QUEUE_TRIES', 3),
        'timeout' => env('EVENT_QUEUE_TIMEOUT', 60),
        'backoff' => env('EVENT_QUEUE_BACKOFF', '30,60,120'), // Comma-separated seconds
        'retry_after' => env('EVENT_QUEUE_RETRY_AFTER', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Scheduled Event Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for scheduled (batched) event processing.
    |
    */

    'scheduled' => [
        'enabled' => env('EVENT_SCHEDULED_ENABLED', true),
        'storage' => env('EVENT_SCHEDULED_STORAGE', 'database'), // database or redis
        'table' => 'scheduled_events',
        'batch_size' => env('EVENT_SCHEDULED_BATCH_SIZE', 100),
        'frequency' => env('EVENT_SCHEDULED_FREQUENCY', '*/5 * * * *'), // Every 5 minutes
        'max_age_days' => env('EVENT_SCHEDULED_MAX_AGE_DAYS', 7), // Auto-cleanup after 7 days
    ],

    /*
    |--------------------------------------------------------------------------
    | Event-Specific Configuration
    |--------------------------------------------------------------------------
    |
    | Fine-grained control over specific events and their listeners.
    | Each event can override the global settings.
    |
    */

    'events' => [
        // Investment Events
        'investment_created' => [
            'class' => \App\Modules\Investment\Events\InvestmentCreated::class,
            'mode' => env('EVENT_INVESTMENT_MODE', 'queue'), // null = use default
            'queue' => env('EVENT_INVESTMENT_QUEUE', 'default'),
            'priority' => 'high',
            'listeners' => [
                'create_transaction' => [
                    'class' => \App\Modules\Transaction\Listeners\CreateTransactionForEntity::class,
                    'mode' => env('EVENT_INVESTMENT_TRANSACTION_MODE', 'queue'),
                    'queue' => env('EVENT_INVESTMENT_TRANSACTION_QUEUE', 'default'),
                    'tries' => env('EVENT_INVESTMENT_TRANSACTION_TRIES', 5),
                    'backoff' => [30, 60, 120],
                ],
                'send_notification' => [
                    'class' => \App\Modules\Notification\Listeners\SendEntityNotification::class,
                    'mode' => env('EVENT_NOTIFICATION_MODE', 'queue'),
                    'queue' => env('EVENT_NOTIFICATION_QUEUE', 'notifications'),
                    'tries' => env('EVENT_NOTIFICATION_TRIES', 3),
                    'backoff' => [30, 60, 120],
                ],
            ],
        ],

        // Transaction Events
        'transaction_created' => [
            'class' => \App\Modules\Transaction\Events\TransactionWasCreated::class,
            'mode' => env('EVENT_TRANSACTION_MODE', 'queue'),
            'queue' => env('EVENT_TRANSACTION_QUEUE', 'default'),
            'priority' => 'high',
            'listeners' => [
                'create_category' => [
                    'class' => \App\Modules\Transaction\Listeners\CreateCategoryForTransaction::class,
                    'mode' => env('EVENT_TRANSACTION_CATEGORY_MODE', 'queue'),
                    'queue' => env('EVENT_TRANSACTION_CATEGORY_QUEUE', 'default'),
                    'tries' => env('EVENT_TRANSACTION_CATEGORY_TRIES', 3),
                    'backoff' => [30, 60, 120],
                ],
                'send_notification' => [
                    'class' => \App\Modules\Notification\Listeners\SendEntityNotification::class,
                    'mode' => env('EVENT_NOTIFICATION_MODE', 'queue'),
                    'queue' => env('EVENT_NOTIFICATION_QUEUE', 'notifications'),
                    'tries' => env('EVENT_NOTIFICATION_TRIES', 3),
                    'backoff' => [30, 60, 120],
                ],
            ],
        ],

        // Payment Events
        'payment_was_completed' => [
            'class' => \App\Modules\Payment\Events\PaymentWasCompleted::class,
            'mode' => env('EVENT_PAYMENT_MODE', 'sync'),
            'priority' => 'high',
            'listeners' => [
                'funding_payment_was_completed' => [
                    'class' => \App\Modules\Funding\Listeners\FundingPaymentWasCompletedListener::class,
                    'mode' => env('EVENT_PAYMENT_FUNDING_MODE', 'queue'),
                    'queue' => env('EVENT_PAYMENT_FUNDING_QUEUE', 'default'),
                    'tries' => env('EVENT_PAYMENT_FUNDING_TRIES', 3),
                    'backoff' => [30, 60, 120],
                ],
                'withdrawal_was_completed' => [
                    'class' => \App\Modules\Withdrawal\Listeners\WithdrawalPaymentWasCompletedListener::class,
                    'mode' => env('EVENT_PAYMENT_WITHDRAWAL_MODE', 'queue'),
                    'queue' => env('EVENT_PAYMENT_WITHDRAWAL_QUEUE', 'default'),
                    'tries' => env('EVENT_PAYMENT_WITHDRAWAL_TRIES', 3),
                    'backoff' => [30, 60, 120],
                ],
                'send_notification' => [
                    'class' => \App\Modules\Notification\Listeners\SendEntityNotification::class,
                    'mode' => env('EVENT_NOTIFICATION_MODE', 'queue'),
                    'queue' => env('EVENT_NOTIFICATION_QUEUE', 'notifications'),
                    'tries' => env('EVENT_NOTIFICATION_TRIES', 3),
                    'backoff' => [30, 60, 120],
                ],
                'user_payment_was_completed' => [
                    'class' => \App\Modules\User\Listeners\UserPaymentWasCompletedListener::class,
                    'mode' => env('EVENT_PAYMENT_USER_MODE', 'sync'), // or 'queue' if you want it queued
                    'queue' => env('EVENT_PAYMENT_USER_QUEUE', 'default'),
                    'tries' => env('EVENT_PAYMENT_USER_TRIES', 3),
                    'backoff' => [30, 60, 120],
                ],
            ],
        ],
        'funding_completed' => [
            'class' => \App\Modules\Funding\Events\FundingWasCompleted::class,
            'mode' => env('EVENT_FUNDING_MODE', 'queue'),
            'priority' => 'high',
            'listeners' => [
                'create_funding_payment' => [
                    'class' => \App\Modules\Payment\Listeners\FundingWasCompletedListener::class,
                    'mode' => env('EVENT_FUNDING_PAYMENT_MODE', 'queue'),
                    'queue' => env('EVENT_FUNDING_PAYMENT_QUEUE', 'default'),
                    'tries' => env('EVENT_FUNDING_PAYMENT_TRIES', 3),
                    'backoff' => [30, 60, 120],
                ],
            ]
        ],
        'withdrawal_completed' => [
            'class' => \App\Modules\Withdrawal\Events\WithdrawalWasCompleted::class,
            'mode' => env('EVENT_WITHDRAWAL_MODE', 'queue'),
            'priority' => 'high',
            'listeners' => [
                'create_withdrawal_payment' => [
                    'class' => \App\Modules\Payment\Listeners\WithdrawalWasCompletedListener::class,
                    'mode' => env('EVENT_WITHDRAWAL_PAYMENT_MODE', 'queue'),
                    'queue' => env('EVENT_WITHDRAWAL_PAYMENT_QUEUE', 'default'),
                    'tries' => env('EVENT_WITHDRAWAL_PAYMENT_TRIES', 3),
                    'backoff' => [30, 60, 120],
                ],
            ]
        ],
        'user_was_created' => [
            'class' => \App\Modules\User\Events\UserWasCreatedEvent::class,
            'mode' => env('EVENT_USER_CREATED_MODE', 'queue'),
            'priority' => 'high',
            'listeners' => [
                'send_notification' => [
                    'class' => \App\Modules\Notification\Listeners\SendEntityNotification::class,
                    'mode' => env('EVENT_NOTIFICATION_MODE', 'queue'),
                    'queue' => env('EVENT_NOTIFICATION_QUEUE', 'notifications'),
                    'tries' => env('EVENT_NOTIFICATION_TRIES', 3),
                    'backoff' => [30, 60, 120],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Priority Queues
    |--------------------------------------------------------------------------
    |
    | Map priority levels to queue names for better resource allocation.
    |
    */

    'priority_queues' => [
        'critical' => 'events-critical',
        'high' => 'events-high',
        'medium' => 'events-medium',
        'low' => 'events-low',
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy Settings (Backward Compatibility)
    |--------------------------------------------------------------------------
    */

    'queue_events' => env('QUEUE_EVENTS', true), // Deprecated, use default_mode
    'default_transaction_category_id' => env('DEFAULT_TRANSACTION_CATEGORY_ID', 1),

    /*
    |--------------------------------------------------------------------------
    | Transaction Defaults
    |--------------------------------------------------------------------------
    |
    | Default settings for automatically created transactions from events.
    |
    */

    'transaction_defaults' => [
        'category_id' => env('DEFAULT_TRANSACTION_CATEGORY_ID', 1),
        'status' => 'completed',
        'entry_type' => 'credit',
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring & Logging
    |--------------------------------------------------------------------------
    |
    | Configuration for event monitoring and debugging.
    |
    */

    'monitoring' => [
        'log_dispatched' => env('EVENT_LOG_DISPATCHED', false),
        'log_processed' => env('EVENT_LOG_PROCESSED', false),
        'log_failed' => env('EVENT_LOG_FAILED', true),
        'track_performance' => env('EVENT_TRACK_PERFORMANCE', true),
    ],
];
