<?php

namespace App\Modules\Payment\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Payment\Services\PaymentService;
use App\Core\Http\ServiceResponse;
use App\Modules\Payment\Enums\PaymentStatusEnum;
class PaymentController extends CrudController
{
    /**
     * Constructor - inject PaymentService
     */
    public function __construct(
        private PaymentService $paymentService
    ) {
        parent::__construct($paymentService);
    }

    /**
     * Update the status of a payment
     *
     * @param array $data
     * @return ServiceResponse
     */
    public function Status(array $data): ServiceResponse
    {
        $paymentUuid = $data['uuid'];
        $status = PaymentStatusEnum::from($data['status']);
        $payment = $this->getPaymentByUuid($paymentUuid)->getData();
        $updatedPayment = $this->paymentService->updatePaymentStatus($payment->id, $status);
        return ServiceResponse::success($updatedPayment, 'Payment status updated to ' . $status->value . ' successfully');
    }

    public function getPaymentByUuid(string $uuid): ServiceResponse
    {
        return $this->paymentService->findByUuid($uuid);
    }
}
