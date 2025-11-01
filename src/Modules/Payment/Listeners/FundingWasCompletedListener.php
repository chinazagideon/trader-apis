<?php

namespace App\Modules\Payment\Listeners;

use App\Modules\Funding\Events\FundingWasCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Payment\Services\PaymentService;
use Illuminate\Support\Facades\Log;
use App\Core\Contracts\ConfigurableListenerInterface;
use App\Core\Traits\ConfigurableListener;
use App\Modules\Payment\Enums\PaymentStatusEnum;

class FundingWasCompletedListener implements ConfigurableListenerInterface, ShouldQueue
{
    use ConfigurableListener;

    /**
     * Configuration keys for this listener
     */
    protected string $eventConfigKey = 'funding_completed';
    /**
     * Configuration key for this listener
     */
    protected string $listenerConfigKey = 'create_funding_payment';

    public function __construct(
        public PaymentService $paymentService,
    ) {}

    /**
     * Handle the funding was completed event
     * @param FundingWasCompleted $event
     * @return void
     */
    public function handle(FundingWasCompleted $event): void
    {
        try {
            $data = [
                'payable_id' => $event->funding->id,
                'payable_type' => $event->moduleName,
                'amount' => $event->funding->amount,
                'currency_id' => $event->funding->currency_id,
                'status' => PaymentStatusEnum::defaultStatus(),
            ];

            $this->paymentService->makePayment($data);
        } catch (\Exception $e) {
            Log::error('Listener: Failed to create payment', [
                'funding_id' => $event->funding->id,
                'funding' => $event->funding,
                'service' => $this->paymentService->getServiceName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
