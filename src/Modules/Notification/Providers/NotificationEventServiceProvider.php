<?php

namespace App\Modules\Notification\Providers;

use App\Core\Providers\ModuleEventServiceProviderBase;
use App\Modules\Notification\Listeners\SendEntityNotification;
use App\Modules\Investment\Events\InvestmentCreated;
use App\Modules\Transaction\Events\TransactionWasCreated;

class NotificationEventServiceProvider extends ModuleEventServiceProviderBase
{
    /**
     * The event to listener mappings for the application.
     */
    protected $listen = [
        InvestmentCreated::class => [
            SendEntityNotification::class,
        ],
        TransactionWasCreated::class => [
            SendEntityNotification::class,
        ],
        // Add more events as needed
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

