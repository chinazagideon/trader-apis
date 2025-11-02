<?php

namespace App\Modules\Payment\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Core\Http\ServiceResponse;
use App\Modules\Payment\Services\PaymentGatewayService;

class PaymentGatewayController extends CrudController
{
    public function __construct(
        private PaymentGatewayService $paymentGatewayService
    ) {
        parent::__construct($paymentGatewayService);
    }

    /**
     * Get payment gateway by slug
     * @param array $data
     * @return ServiceResponse
     */
    public function getPaymentGatewayBySlug(array $data): ServiceResponse
    {
        return $this->paymentGatewayService->getPaymentGatewayBySlug($data['slug']);
    }

}
