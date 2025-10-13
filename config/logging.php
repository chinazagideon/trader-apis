<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Switches
    |--------------------------------------------------------------------------
    |
    | Control which logging features are enabled. This allows you to
    | selectively enable/disable logging features for performance tuning.
    |
    */

    'switches' => [
        'operation_lifecycle' => env('LOG_OPERATION_LIFECYCLE', true),
        'performance_metrics' => env('LOG_PERFORMANCE_METRICS', true),
        'request_context' => env('LOG_REQUEST_CONTEXT', true),
        'business_logic' => env('LOG_BUSINESS_LOGIC', true),
        'repository_queries' => env('LOG_REPOSITORY_QUERIES', false),
        'detailed_errors' => env('LOG_DETAILED_ERRORS', true),
        'memory_tracking' => env('LOG_MEMORY_TRACKING', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Thresholds
    |--------------------------------------------------------------------------
    |
    | Define thresholds for performance monitoring. Operations exceeding
    | these thresholds will be logged with warning level.
    |
    */

    'thresholds' => [
        'slow_query_ms' => env('LOG_SLOW_QUERY_MS', 1000),
        'slow_operation_ms' => env('LOG_SLOW_OPERATION_MS', 2000),
        'memory_warning_mb' => env('LOG_MEMORY_WARNING_MB', 128),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', (string) env('LOG_STACK', 'single')),
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', 'Laravel Log'),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'handler_with' => [
                'stream' => 'php://stderr',
            ],
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        // Enhanced logging channels
        'operations' => [
            'driver' => 'daily',
            'path' => storage_path('logs/operations.log'),
            'level' => env('LOG_LEVEL', 'info'),
            'days' => env('LOG_OPERATIONS_DAYS', 30),
            'replace_placeholders' => true,
        ],

        'performance' => [
            'driver' => 'daily',
            'path' => storage_path('logs/performance.log'),
            'level' => env('LOG_LEVEL', 'info'),
            'days' => env('LOG_PERFORMANCE_DAYS', 7),
            'replace_placeholders' => true,
        ],

        'business_logic' => [
            'driver' => 'daily',
            'path' => storage_path('logs/business.log'),
            'level' => env('LOG_LEVEL', 'info'),
            'days' => env('LOG_BUSINESS_DAYS', 30),
            'replace_placeholders' => true,
        ],

        'errors' => [
            'driver' => 'daily',
            'path' => storage_path('logs/errors.log'),
            'level' => 'error',
            'days' => env('LOG_ERRORS_DAYS', 90),
            'replace_placeholders' => true,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Service-Specific Logging
    |--------------------------------------------------------------------------
    |
    | Configure logging behavior for specific services. This allows
    | fine-grained control over what gets logged for each service.
    |
    */

    'services' => [
        'AuthService' => [
            'log_credentials' => false, // Never log sensitive data
            'log_token_operations' => true,
            'log_user_actions' => true,
        ],

        'UserService' => [
            'log_user_data' => false, // Never log user data
            'log_operations' => true,
        ],

        'TransactionService' => [
            'log_transaction_details' => true,
            'log_amounts' => true,
        ],

        'InvestmentService' => [
            'log_investment_details' => true,
            'log_calculations' => true,
        ],
    ],

];
