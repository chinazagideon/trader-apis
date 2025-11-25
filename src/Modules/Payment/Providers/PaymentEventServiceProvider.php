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
use App\Modules\Payment\Observer\PaymentObserver;
use App\Modules\Payment\Database\Models\Payment;

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


    /**
     * Bootstrap the service provider.
     */
    public function boot(): void
    {
        parent::boot();

        Payment::observe(PaymentObserver::class);
    }
}
