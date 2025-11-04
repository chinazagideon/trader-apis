<?php

namespace App\Modules\Payment\Providers;

use App\Core\Providers\ModuleEventServiceProviderBase;
use App\Modules\Funding\Events\FundingWasCompleted;
use App\Modules\Payment\Events\PaymentWasCompleted;
use App\Modules\Payment\Listeners\FundingWasCompletedListener;
use Illuminate\Support\Facades\Event;
use App\Modules\Withdrawal\Events\WithdrawalWasCompleted;
use App\Modules\Payment\Listeners\WithdrawalWasCompletedListener;
use App\Modules\Payment\Events\PaymentWasInitialised;

class PaymentEventServiceProvider extends ModuleEventServiceProviderBase
{
    /**
     * The event to listener mappings for the application.
     */
    protected $listen = [
        FundingWasCompleted::class => [
            FundingWasCompletedListener::class,
        ],
        WithdrawalWasCompleted::class => [
            WithdrawalWasCompletedListener::class,
        ],
    ];


    public function boot(): void
    {
        parent::boot();
    }
}
