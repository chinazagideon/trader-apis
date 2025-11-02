<?php

namespace App\Modules\Payment\Listeners;

use App\Modules\Withdrawal\Events\WithdrawalWasCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Core\Contracts\ConfigurableListenerInterface;
use App\Core\Traits\ConfigurableListener;
use Illuminate\Support\Facades\Log;
use App\Modules\Payment\Services\PaymentService;
use App\Modules\Payment\Enums\PaymentStatusEnum;

class WithdrawalWasCompletedListener implements ConfigurableListenerInterface, ShouldQueue
{
    use ConfigurableListener;

    /**
     * Configuration keys for this listener
     */
    protected string $eventConfigKey = 'withdrawal_completed';
    /**
     * Configuration key for this listener
     */
    protected string $listenerConfigKey = 'create_withdrawal_payment';

    /**
     * Constructor
     * @param PaymentService $paymentService
     */
    public function __construct(
        public PaymentService $paymentService
    ) {}

    /**
     * Handle the withdrawal was completed event
     * @param WithdrawalWasCompleted $event
     * @return void
     */
    public function handle(WithdrawalWasCompleted $event): void
    {

        try {
            $data = [
                'reference' => $event->withdrawal->uuid,
                'payable_id' => $event->withdrawal->id,
                'payable_type' => $event->moduleName,
                'amount' => $event->withdrawal->amount,
                'currency_id' => $event->withdrawal->currency_id,
                'status' => PaymentStatusEnum::defaultStatus(),
            ];

            $this->paymentService->makePayment($data);
        } catch (\Exception $e) {
            Log::error('Listener: Failed to create payment', [
                'withdrawal_id' => $event->withdrawal->id,
                'withdrawal' => $event->withdrawal,
                'service' => $this->paymentService->getServiceName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
