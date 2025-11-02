<?php

namespace App\Modules\Payment\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Payment\Services\PaymentService;

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
}
