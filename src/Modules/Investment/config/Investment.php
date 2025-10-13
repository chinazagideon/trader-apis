<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Investment Module Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration specific to the Investment module including transaction
    | settings and business rules.
    |
    */

    'transaction' => [
        'entry_type' => 'debit',
        'status' => 'completed',
        'auto_approve' => true,
    ],

    'business_rules' => [
        'min_amount' => env('INVESTMENT_MIN_AMOUNT', 100),
        'max_amount' => env('INVESTMENT_MAX_AMOUNT', 1000000),
        'default_duration_days' => env('INVESTMENT_DEFAULT_DURATION', 365),
    ],
];
