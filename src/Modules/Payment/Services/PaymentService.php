<?php

namespace App\Modules\Payment\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Payment\Repositories\PaymentRepository;
use App\Modules\Payment\Events\PaymentWasCompleted;

class PaymentService extends BaseService
{
    protected string $serviceName = 'PaymentService';

    public function __construct(
        private PaymentRepository $PaymentRepository,
        private PaymentWasCompleted $PaymentWasCompleted
    )
    {
        parent::__construct($PaymentRepository);
    }

    public function store(array $data): ServiceResponse
    {
        $result = parent::store($data);
        return ServiceResponse::success($result, 'Payment created successfully');
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

}
