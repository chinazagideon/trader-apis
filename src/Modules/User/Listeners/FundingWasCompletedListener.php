<?php

namespace App\Modules\User\Listeners;

use App\Modules\Funding\Events\FundingWasCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\User\Services\UserService;
use Illuminate\Support\Facades\Log;
use App\Core\Contracts\ConfigurableListenerInterface;
use App\Core\Traits\ConfigurableListener;

class FundingWasCompletedListener implements ConfigurableListenerInterface, ShouldQueue
{
    use ConfigurableListener;

    /**
     * Configuration keys for this listener
     */
    protected string $eventConfigKey = 'funding_completed';
    protected string $listenerConfigKey = 'user_balance_updated';

    public function __construct(
        public UserService $userService
    ) {}

    /**
     * Handle the funding was completed event
     * @param FundingWasCompleted $event
     * @return void
     */
    public function handle(FundingWasCompleted $event): void
    {

        try {
            // Check if funding exists and is accessible
            if (!isset($event->funding) || $event->funding === null) {
                throw new \Exception('Funding model is null or not accessible');
            }
            //add funding to user
            $this->userService->addFundingToUser([
                'user_id' => $event->funding->user_id,
                'amount' => $event->funding->amount,
                'type' => $event->funding->type,
            ]);

        } catch (\Exception $e) {
            Log::error('Listener: Failed to add funding to user', [
                'funding_id' => $event->funding->id ?? null,
                'funding' => $event->funding,
                'service' => $this->userService->getServiceName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to allow queue retry mechanism
            throw $e;
        }
    }
}
