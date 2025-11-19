<?php

namespace App\Modules\User\Providers;

use App\Core\Providers\ModuleEventServiceProviderBase;
use App\Modules\Payment\Events\PaymentWasCompleted;
use App\Modules\User\Listeners\UserPaymentWasCompletedListener;

class UserEventServiceProvider extends ModuleEventServiceProviderBase
{
    /**
     * The event to listener mappings for the application.
     */
    protected $listen = [
        PaymentWasCompleted::class => [
            UserPaymentWasCompletedListener::class,
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
