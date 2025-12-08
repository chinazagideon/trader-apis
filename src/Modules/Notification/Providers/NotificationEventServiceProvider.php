<?php

namespace App\Modules\Notification\Providers;

use App\Core\Providers\ModuleEventServiceProviderBase;
use App\Modules\Notification\Listeners\SendEntityNotification;
use App\Modules\Investment\Events\InvestmentCreated;
use App\Modules\Investment\Events\InvestmentWasCreated;
use App\Modules\Transaction\Events\TransactionWasCreated;
use App\Modules\User\Events\UserWasCreatedEvent;
use App\Modules\Funding\Events\FundingWasCompleted;
use App\Modules\Payment\Events\PaymentWasCompleted;
use App\Modules\Withdrawal\Events\WithdrawalWasCompleted;
use App\Modules\Auth\Events\PasswordResetRequestedEvent;

class NotificationEventServiceProvider extends ModuleEventServiceProviderBase
{
    /**
     * The event to listener mappings for the application.
     */
    protected $listen = [
        // InvestmentCreated::class => [
        //     SendEntityNotification::class,
        // ],
        // TransactionWasCreated::class => [
        //     SendEntityNotification::class,
        // ],
        UserWasCreatedEvent::class => [
            SendEntityNotification::class,
        ],
        InvestmentWasCreated::class => [
            SendEntityNotification::class,
        ],
        FundingWasCompleted::class => [
            SendEntityNotification::class,
        ],
        PaymentWasCompleted::class => [
            SendEntityNotification::class,
        ],
        WithdrawalWasCompleted::class => [
            SendEntityNotification::class,
        ],
        PasswordResetRequestedEvent::class => [
            SendEntityNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

