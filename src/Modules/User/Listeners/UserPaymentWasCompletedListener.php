<?php

namespace App\Modules\User\Listeners;


use Illuminate\Support\Facades\Log;
use App\Core\Contracts\ConfigurableListenerInterface;
use App\Modules\Payment\Events\PaymentWasCompleted;
use App\Core\Traits\ConfigurableListener;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use App\Modules\User\Services\UserService;

class UserPaymentWasCompletedListener implements ConfigurableListenerInterface, ShouldDispatchAfterCommit
{
    use ConfigurableListener;

    /**
     * Configuration keys for this listener
     */
    protected string $eventConfigKey = 'payment_was_completed';
    /**
     * Configuration key for this listener
     */
    protected string $listenerConfigKey = 'user_payment_was_completed';

    public function __construct(
        public UserService $userService,
    ) {}

    /**
     * Handle the payment was completed event
     * @param PaymentWasCompleted $event
     * @return void
     */
    public function handle(PaymentWasCompleted $event): void
    {
        Log::info('UserPaymentWasCompletedListener: Payment was completed', [
            'event' => $event,
            'data' => $event->getSerializedData(),
        ]);
        $data = $event->getSerializedData();
        try {

            $this->userService->resolvePayment([
                'payment_id' => $data['payment_id'],
                'status' => $data['status'],
                'type' => $data['payable_type'],
                'user_id' => $data['user_id'],
                'amount' => $data['amount'],
                'currency_id' => $data['currency_id'],
            ]);
        } catch (\Exception $e) {
            Log::error('Listener: Failed to update funding status', [
                'payment_id' => $data['payment_id'],
                'payment' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
