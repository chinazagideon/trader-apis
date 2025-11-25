<?php

namespace App\Modules\User\Providers;

use App\Core\Providers\ModuleEventServiceProviderBase;
use App\Modules\Investment\Events\InvestmentCreated;
use App\Modules\Payment\Events\PaymentWasCompleted;
use App\Modules\User\Listeners\UserPaymentWasCompletedListener;
use App\Modules\User\Listeners\UpdateUserBalanceListener;

class UserEventServiceProvider extends ModuleEventServiceProviderBase
{
    /**
     * The event to listener mappings for the application.
     */
    protected $listen = [
        PaymentWasCompleted::class => [
            UserPaymentWasCompletedListener::class,
        ],
        InvestmentCreated::class => [
            UpdateUserBalanceListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
