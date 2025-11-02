<?php

namespace App\Modules\User\Listeners;

use App\Modules\Withdrawal\Events\WithdrawalWasCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Core\Contracts\ConfigurableListenerInterface;
use App\Core\Traits\ConfigurableListener;
use App\Modules\User\Services\UserService;
use Illuminate\Support\Facades\Log;

class WithdrawalWasCompletedListener implements ConfigurableListenerInterface, ShouldQueue
{
    use ConfigurableListener;

    /**
     * Configuration keys for this listener
     */
    protected string $eventConfigKey = 'withdrawal_completed';
    protected string $listenerConfigKey = 'user_balance_updated';

    public function __construct(
        public UserService $userService
    ) {}

    /**
     * Handle the withdrawal was completed event
     * @param WithdrawalWasCompleted $event
     * @return void
     */
    public function handle(WithdrawalWasCompleted $event): void
    {
        Log::info('User Heard Withdrawal Event: Withdrawal was completed', [
            'withdrawal' => $event,
            'module' => $event->moduleName,
        ]);
    }
}
