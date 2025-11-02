<?php

namespace App\Modules\Payment\Contracts;

use App\Core\Contracts\ServiceInterface;
use App\Core\Http\ServiceResponse;

interface PaymentGatewayServiceContract extends ServiceInterface
{

    /**
     * Process traditional payment
     * @param array $data
     * @return ServiceResponse
     */
    public function processTraditionalPayment(array $data): ServiceResponse;

}
