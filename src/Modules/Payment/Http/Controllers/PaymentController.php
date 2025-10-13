<?php

namespace App\Modules\Payment\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Payment\Services\PaymentService;
use App\Core\Http\ServiceResponse;
use Illuminate\Http\JsonResponse;

class PaymentController extends CrudController
{
    public function __construct(
        private PaymentService $paymentService
    ) {
        parent::__construct($paymentService);
    }

    public function hello(): JsonResponse
    {
        return $this->successResponse([], 'Hello from Payment module');
    }
}
