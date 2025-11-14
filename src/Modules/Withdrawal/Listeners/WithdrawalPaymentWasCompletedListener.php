<?php

namespace App\Modules\Withdrawal\Listeners;


use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use App\Core\Contracts\ConfigurableListenerInterface;
use App\Modules\Payment\Events\PaymentWasCompleted;
use App\Core\Traits\ConfigurableListener;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use App\Modules\Withdrawal\Services\WithdrawalService;
use App\Modules\User\Enums\UserPaymentTypes;

class WithdrawalPaymentWasCompletedListener implements ConfigurableListenerInterface, ShouldQueue, ShouldDispatchAfterCommit
{
    use ConfigurableListener;

    /**
     * Configuration keys for this listener
     */
    protected string $eventConfigKey = 'payment_was_completed';
    /**
     * Configuration key for this listener
     */
    protected string $listenerConfigKey = 'withdrawal_was_completed';

    public function __construct(
        public WithdrawalService $withdrawalService,
    ) {}

    /**
     * Handle the payment was completed event
     * @param PaymentWasCompleted $event
     * @return void
     */
    public function handle(PaymentWasCompleted $event): void
    {
        $data = $event->getSerializedData();

        try {

            if ($data['payable_type'] === $this->getWithdrawalPayableType()) {
                $this->withdrawalService->updateWithdrawalStatus([
                    'withdrawal_id' => $data['payable_id'],
                    'status' => $data['status'],
                ]);
            }
            
        } catch (\Exception $e) {

            Log::error('Listener: Failed to update withdrawal status', [
                'payment_id' => $data['payment_id'],
                'payment' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get the payable type
     * @return string
     */
    private function getWithdrawalPayableType(): string
    {
        return UserPaymentTypes::Withdrawal->value;
    }
}
