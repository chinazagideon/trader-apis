<?php

namespace App\Modules\Payment\Services;

use App\Core\Exceptions\AppException;
use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Core\Services\EventDispatcher;
use App\Modules\Payment\Repositories\PaymentProcessorRepository;
use App\Modules\Payment\Contracts\PaymentProcessorServiceContract;
use App\Modules\Payment\Enums\PaymentStatusEnum;
use App\Modules\Market\Services\MarketFiatService;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Payment\Events\PaymentWasInitialised;

class PaymentProcessorService extends BaseService implements PaymentProcessorServiceContract
{
    protected string $serviceName = 'PaymentProcessorService';

    protected float $fee = 0.012;

    public function __construct(
        private PaymentProcessorRepository $paymentProcessorRepository,
        private PaymentService $paymentService,
        private MarketFiatService $marketFiatService,
        private EventDispatcher $eventDispatcher
    ) {
        parent::__construct($paymentProcessorRepository);
    }

    /**
     * Initialise a payment transaction
     * @param array $data
     * @return ServiceResponse
     */
    public function initialise(array $data): ServiceResponse
    {
        $payment_reference = $data['payment_reference'];
        $payment_id = $this->getPaymentByReference($payment_reference)->id;
        $data = $this->prepareData($data, $payment_id);
        $paymentProcessor = parent::store($data);

        return $paymentProcessor;
    }

    /**
     * Verify a payment transaction
     * @param array $data
     * @return ServiceResponse
     */
    public function verify(array $data): ServiceResponse
    {
        $paymentProcessorUuid = $data['reference'];
        $payment = $this->paymentProcessorRepository->findBy('reference', $paymentProcessorUuid);
        if (!$payment) {
            return ServiceResponse::error('Payment processor not found');
        }
        return ServiceResponse::success($payment->refresh(), 'Payment processor verified successfully');
    }

    /**
     * Complete a payment transaction
     * @param array $data
     * @return ServiceResponse
     */
    public function complete(array $data): ServiceResponse
    {
        $paymentProcessorUuid = $data['reference'];
        $paymentProcessor = $this->paymentProcessorRepository->findBy('reference', $paymentProcessorUuid);
        if (!$paymentProcessor) {
            return ServiceResponse::error('Payment processor not found');
        }
        $paymentProcessor->update([
            'status' => PaymentStatusEnum::COMPLETED->value,
        ]);
        return ServiceResponse::success($paymentProcessor->refresh(), 'Payment processor completed successfully');
    }

    /**
     * Get a payment by reference
     * @param string $reference
     * @return object
     */
    protected function getPaymentByReference(string $reference): object
    {
        return $this->paymentService->getReference($reference);
    }

    /**
     * Prepare data for a payment processor
     * @param array $data
     * @param int $payment_id
     * @return array
     */
    protected function prepareData(array $data, int $payment_id): array
    {
        $convertFiat = $this->convertAmountToFiat($data);
        return [
            'payment_hash' => $data['payment_hash'],
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'payment_gateway_id' => $data['payment_gateway_id'],
            'fee' => $this->fee,
            'market_rate' => $convertFiat['market_rate'],
            'total_amount' => $convertFiat['total_amount'],
            'fiat_amount' => $convertFiat['fiat_amount'],
            'fiat_currency' => $convertFiat['fiat_currency'],
            'payment_id' => $payment_id,
            'processor_data' => $this->filterData($data),
            'status' => PaymentStatusEnum::PROCESSING->value,
        ];
    }
    /**
     * Convert amount to fiat
     * @param float $amount
     * @param int $currency_id
     * @return array
     */
    protected function convertAmountToFiat(array $data): array
    {
        $data = [
            'amount' => $data['amount'],
            'currency_id' => $data['currency_id'],
            'fiat_currency_id' => $data['fiat_currency_id'],
        ];
        $convertFiat = $this->marketFiatService->fiatConverter($data);
        $convertFiat = $convertFiat->getData();
        return [
            'fiat_amount' => $convertFiat->fiat_amount,
            'fiat_currency' => $convertFiat->market_data?->currency?->code ?? null,
            'market_rate' => $convertFiat->market_data?->price ?? 0.0,
            'total_amount' => $convertFiat->fiat_amount,
        ];
    }

    /**
     * Filter data for a payment processor
     * @param array $data
     * @return array
     */
    protected function filterData(array $data): array
    {
        unset($data['currency_id']);
        return $data;
    }

    /**
     * Completed a payment processor
     * @param array $data
     * @param Model $model
     * @param string $operation
     * @return void
     */
    protected function completed(array $data, Model $model, string $operation = 'store|update|destroy'): void
    {
        $this->logBusinessLogic('Payment processor processing', [
            'data' => $data,
            'model' => $model,
            'operation' => $operation,
        ]);

        /** @var PaymentProcessor $paymentProcessor = $model */
        $this->paymentService->updatePaymentStatus($model->payment_id, PaymentStatusEnum::PROCESSING);

        /** @var PaymentProcessor $paymentProcessor = $model */
        PaymentWasInitialised::dispatch($paymentProcessor);
    }

    /**
     * Update the status of a payment processor
     * @param array $data
     * @return ServiceResponse
     */
    public function updateStatus(array $data): ServiceResponse
    {
        $paymentProcessorUuid = $data['uuid'];
        $paymentProcessor = $this->paymentProcessorRepository->findBy('uuid', $paymentProcessorUuid);
        if (!$paymentProcessor) {
            throw new AppException('Invalid payment reference');
        }
        $updatedPayment = $this->paymentService->updatePaymentStatus($paymentProcessor->payment_id, PaymentStatusEnum::from($data['status']));
        if (!$updatedPayment) {
            throw new AppException('Failed to update payment status');
        }
        $paymentProcessor->update(['status' => $data['status']]);
        return ServiceResponse::success($paymentProcessor->refresh(), "Payment status updated to {$data['status']} successfully");
    }
}
