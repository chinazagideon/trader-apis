<?php

namespace App\Modules\User\Providers;

use App\Core\Providers\ModuleEventServiceProviderBase;
use App\Modules\Funding\Events\FundingWasCompleted;
use App\Modules\User\Listeners\FundingWasCompletedListener;
use App\Modules\Withdrawal\Events\WithdrawalWasCompleted;
use App\Modules\User\Listeners\WithdrawalWasCompletedListener;

class UserEventServiceProvider extends ModuleEventServiceProviderBase
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
