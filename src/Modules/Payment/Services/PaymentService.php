<?php

namespace App\Modules\Payment\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Payment\Repositories\PaymentRepository;
use App\Modules\Payment\Events\PaymentWasCompleted;
use Illuminate\Support\Str;
use App\Modules\Payment\Enums\PaymentStatusEnum;
use App\Core\Services\EventDispatcher;
use App\Modules\Payment\Database\Models\Payment;
use Illuminate\Support\Facades\Log;
use App\Core\Exceptions\NotFoundException;

class PaymentService extends BaseService
{
    protected string $serviceName = 'PaymentService';

    public function __construct(
        private PaymentRepository $PaymentRepository,
        private EventDispatcher $eventDispatcher
    ) {
        parent::__construct($PaymentRepository);
    }

    public function store(array $data): ServiceResponse
    {
        // Handle reference UUID: use it as payment UUID if provided, otherwise generate one
        if (isset($data['reference']) && !empty($data['reference'])) {
            $data['uuid'] = $data['reference'];
            unset($data['reference']); // Remove reference from data as it's now stored as uuid
        } elseif (!isset($data['uuid']) || empty($data['uuid'])) {
            $data['uuid'] = (string) Str::uuid();
        }

        $response = parent::store($data);

        // Return response with custom message if successful
        if ($response->isSuccess()) {
            return ServiceResponse::success($response->getData(), 'Payment created successfully');
        }

        return $response;
    }
    /**
     * Make a payment
     * @param array $data
     * @return ServiceResponse
     */
    public function makePayment(array $data): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($data) {
            $result = $this->store($data);
            return $result;
        }, 'make payment');
    }


    /**
     * Get a payment by reference
     * @param string $reference
     * @return object|null
     */
    public function getReference(string $reference): ?object
    {
        $result = $this->PaymentRepository->findBy('uuid', $reference);
        if (!$result) {
            return null;
        }
        return $result;
    }

    /**
     * Update the payment status
     * @param int $id
     * @param PaymentStatusEnum $status
     * @return object
     */
    public function updatePaymentStatus(int $id, PaymentStatusEnum $status): object
    {
        $payment = $this->PaymentRepository->findOrFail($id);
        if($payment->status === PaymentStatusEnum::COMPLETED->value) {
            throw new \Exception('Payment already completed');
        }

        $payment->update([
            'status' => $status->value,
        ]);

        $payment->load(['payable', 'currency']);
        $payment->refresh();

        $this->dispatchPaymentEvent($payment);

        return $payment;
    }

    /**
     * Show a payment
     * @param int $id
     * @return ServiceResponse
     */
    public function show(int $id): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($id) {
            $payment = $this->PaymentRepository->getPayment($id);
            if (!$payment) {
                throw new \Exception('Payment not found');
            }
            return ServiceResponse::success($payment, 'Payment retrieved successfully');
        }, 'show payment');
    }

    /**
     * Dispatch a payment event
     * @param Payment $payment
     * @return void
     */
    private function dispatchPaymentEvent(Payment $payment): void
    {
        // Only dispatch for statuses that require downstream updates
        $actionableStatuses = $this->actionableStatuses();
        $supportedPayableTypes = $this->supportedPayableTypes();
        if (
            !in_array($payment->payable_type, $supportedPayableTypes)
            || !in_array($payment->status, $actionableStatuses)
        ) {
            return;
        }

        Log::info('PaymentService: Dispatching payment completed event', [
            'payment' => $payment,
        ]);
        $this->eventDispatcher->dispatch(
            new PaymentWasCompleted($payment),
            'payment_was_completed'
        );
    }

    /**
     * Get the actionable statuses
     * @return array
     */
    private function actionableStatuses(): array
    {
        return [
            PaymentStatusEnum::COMPLETED->value,
            PaymentStatusEnum::FAILED->value,
        ];
    }

    /**
     * Get the supported payable types
     * @return array
     */
    private function supportedPayableTypes(): array
    {
        return [
            'funding',
            'withdrawal',
        ];
    }

    /**
     * Get a payment by uuid
     * @param string $uuid
     * @return ServiceResponse
     * @throws NotFoundException
     */
    public function findByUuid(string $uuid): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($uuid) {
            $payment = $this->PaymentRepository->findBy('uuid', $uuid);
            if (!$payment) {
                throw new NotFoundException('Payment not found');
            }
            return $payment;
        }, 'find payment by uuid');
    }
}
