<?php

namespace App\Modules\Market\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Market\Services\MarketPriceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MarketPriceController extends CrudController
{
    public function __construct(
        private MarketPriceService $marketPriceService
    ) {
        parent::__construct($marketPriceService);
    }

    /**
     * Get currency price by symbol
     * @param Request $request
     * @return JsonResponse
     */
    public function getCurrencyPriceBySymbol(Request $request): JsonResponse
    {
        $symbol = $request->route('symbol');
        $response = $this->marketPriceService->getCurrencyPriceBySymbol(['symbol' => $symbol]);
        return $this->successResponse($response->getData(), $response->getMessage(), $response->getHttpStatusCode());
    }
}
