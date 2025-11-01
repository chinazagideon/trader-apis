<?php

return [
    /**
     * Morph maps for the core module
     */
    'morph_maps' => [
        // Funding module
        'funding' => \App\Modules\Funding\Database\Models\Funding::class,

        // User module
        'user' => \App\Modules\User\Database\Models\User::class,

        // Transaction module
        'transaction' => \App\Modules\Transaction\Database\Models\Transaction::class,

        // Transaction category module
        'transaction_category' => \App\Modules\Transaction\Database\Models\TransactionCategory::class,

        // Investment module
        'investment' => \App\Modules\Investment\Database\Models\Investment::class,

        // Payment module
        'payment' => \App\Modules\Payment\Database\Models\Payment::class,

        // Category module
        'category' => \App\Modules\Category\Database\Models\Category::class,

        // Currency module
        'currency' => \App\Modules\Currency\Database\Models\Currency::class,

        // Pricing module
        'pricing' => \App\Modules\Pricing\Database\Models\Pricing::class,

        // Swap module
        'swap' => \App\Modules\Swap\Database\Models\Swap::class,

        // Withdrawal module
        'withdrawal' => \App\Modules\Withdrawal\Database\Models\Withdrawal::class,
    ],
];
