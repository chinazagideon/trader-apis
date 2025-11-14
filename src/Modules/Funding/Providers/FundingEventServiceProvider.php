<?php

namespace App\Modules\Funding\Providers;

use App\Core\Providers\ModuleEventServiceProviderBase;
use App\Modules\Payment\Events\PaymentWasCompleted;
use App\Modules\Funding\Listeners\FundingPaymentWasCompletedListener;

class FundingEventServiceProvider extends ModuleEventServiceProviderBase
{
    /**
     * The event to listener mappings for the application.
     */
    protected $listen = [
        PaymentWasCompleted::class => [
            FundingPaymentWasCompletedListener::class,
        ],
    ];


    public function boot(): void
    {
        parent::boot();
    }
}
