<?php

namespace App\Modules\Funding\Listeners;


use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use App\Core\Contracts\ConfigurableListenerInterface;
use App\Modules\Payment\Events\PaymentWasCompleted;
use App\Core\Traits\ConfigurableListener;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use App\Modules\Funding\Services\FundingService;
use App\Modules\User\Enums\UserPaymentTypes;

class FundingPaymentWasCompletedListener implements ConfigurableListenerInterface, ShouldQueue, ShouldDispatchAfterCommit
{
    use ConfigurableListener;

    /**
     * Configuration keys for this listener
     */
    protected string $eventConfigKey = 'payment_was_completed';
    /**
     * Configuration key for this listener
     */
    protected string $listenerConfigKey = 'funding_payment_was_completed';

    public function __construct(
        public FundingService $fundingService,
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

            if ($data['payable_type'] === $this->getFundingPayableType()) {
                $this->fundingService->updateFundingStatus([
                    'funding_id' => $data['payable_id'],
                    'status' => $data['status'],
                ]);
            }
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

    /**
     * Get the funding payable type
     * @return string
     */
    private function getFundingPayableType(): string
    {
        return UserPaymentTypes::Funding->value;
    }
}
