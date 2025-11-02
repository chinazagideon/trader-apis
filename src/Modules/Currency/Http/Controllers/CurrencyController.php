<?php

namespace App\Modules\Currency\Http\Controllers;

use App\Core\Controllers\BaseController;
use App\Core\Controllers\CrudController;
use Illuminate\Http\JsonResponse;
use App\Modules\Currency\Services\CurrencyService;

class CurrencyController extends CrudController
{

    public function __construct(
        CurrencyService $currencyService
    ) {
        parent::__construct($currencyService);
    }

    /**
     * Get default currency
     * @return JsonResponse
     */
    public function getDefaultCurrency(): JsonResponse
    {
        $response = $this->service->getDefaultCurrency();
        return $response->toJsonResponse();
    }
}
