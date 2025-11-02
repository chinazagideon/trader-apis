<?php

namespace App\Modules\Payment\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Payment\Repositories\PaymentRepository;
use App\Modules\Payment\Events\PaymentWasCompleted;
use Illuminate\Support\Str;
use App\Modules\Payment\Enums\PaymentStatusEnum;

class PaymentService extends BaseService
{
    protected string $serviceName = 'PaymentService';

    public function __construct(
        private PaymentRepository $PaymentRepository,
        private PaymentWasCompleted $PaymentWasCompleted
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
        $payment->update([
            'status' => $status->value,
        ]);
        return $payment->refresh();
    }
}
