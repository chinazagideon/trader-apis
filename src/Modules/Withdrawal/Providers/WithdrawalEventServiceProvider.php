<?php

namespace App\Modules\Withdrawal\Providers;

use App\Core\Providers\ModuleEventServiceProviderBase;
use App\Modules\Withdrawal\Events\WithdrawalWasCompleted;
use Illuminate\Support\Facades\Event;

class WithdrawalEventServiceProvider extends ModuleEventServiceProviderBase
{
    /**
     * The event to listener mappings for the application.
     */
    protected $listen = [];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
        //register withdrawal completed event
        Event::listen(WithdrawalWasCompleted::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
