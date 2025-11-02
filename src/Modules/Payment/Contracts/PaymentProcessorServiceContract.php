<?php

namespace App\Modules\Payment\Contracts;

use App\Core\Contracts\ServiceInterface;
use App\Core\Http\ServiceResponse;

interface PaymentProcessorServiceContract extends ServiceInterface
{
    /**
     * Initialise a payment transaction
     * @param array $data
     * @return ServiceResponse
     */
    public function initialise(array $data): ServiceResponse;

    /**
     * Verify a payment transaction
     * @param array $data
     * @return ServiceResponse
     */
    public function verify(array $data): ServiceResponse;

    /**
     * Complete a payment transaction
     * @param array $data
     * @return ServiceResponse
     */
    public function complete(array $data): ServiceResponse;
}
