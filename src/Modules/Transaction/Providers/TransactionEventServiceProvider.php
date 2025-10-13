<?php

namespace App\Modules\Transaction\Providers;

use App\Core\Providers\ModuleEventServiceProviderBase;
use App\Core\Events\EntityTransactionEvent;
use App\Modules\Investment\Events\InvestmentCreated;
use App\Modules\Transaction\Listeners\CreateTransactionForEntity;
use App\Modules\Transaction\Listeners\CreateCategoryForTransaction;
use App\Modules\Transaction\Events\TransactionWasCreated;



class TransactionEventServiceProvider extends ModuleEventServiceProviderBase
{
    /**
     * The event to listener mappings for the application.
     */
    protected $listen = [
        EntityTransactionEvent::class => [
            CreateTransactionForEntity::class,
        ],
        InvestmentCreated::class => [
            CreateTransactionForEntity::class,
        ],
        TransactionWasCreated::class => [
            CreateCategoryForTransaction::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
