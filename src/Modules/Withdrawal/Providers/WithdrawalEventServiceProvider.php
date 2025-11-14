<?php

namespace App\Modules\Withdrawal\Providers;

use App\Core\Providers\ModuleEventServiceProviderBase;
use App\Modules\Withdrawal\Events\WithdrawalWasCompleted;
use App\Modules\Payment\Events\PaymentWasCompleted;
use App\Modules\Withdrawal\Listeners\WithdrawalPaymentWasCompletedListener;
use Illuminate\Support\Facades\Event;

class WithdrawalEventServiceProvider extends ModuleEventServiceProviderBase
{
    /**
     * The event to listener mappings for the application.
     */
    protected $listen = [
        PaymentWasCompleted::class => [
            WithdrawalPaymentWasCompletedListener::class,
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
