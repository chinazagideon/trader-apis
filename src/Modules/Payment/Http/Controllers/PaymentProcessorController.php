<?php

namespace App\Modules\Payment\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Payment\Services\PaymentProcessorService;
use App\Core\Http\ServiceResponse;
use App\Modules\Payment\Http\Requests\PaymentProcessorUpdateStatusRequest;

class PaymentProcessorController extends CrudController
{
    public function __construct(
        private PaymentProcessorService $paymentProcessorService
    ) {
        parent::__construct($paymentProcessorService);
    }

    /**
     * Initiate payment
     *
     * @param array $data
     * @return ServiceResponse
     */
    public function Initiate(array $data): ServiceResponse
    {
        return $this->paymentProcessorService->initialise($data);
    }

    /**
     * Update the status of a payment processor
     *
     * @param array $data
     * @return ServiceResponse
     */
    public function Status(array $data): ServiceResponse
    {
        return $this->paymentProcessorService->updateStatus($data);
    }
}
