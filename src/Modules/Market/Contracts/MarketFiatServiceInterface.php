<?php

namespace App\Modules\Market\Contracts;

use App\Core\Http\ServiceResponse;


interface MarketFiatServiceInterface
{
    /**
     * Convert fiat to crypto
     *
     * @param array $data
     * @return ServiceResponse
     */
    public function fiatConverter(array $data): ServiceResponse;
}
