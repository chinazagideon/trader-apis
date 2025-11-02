<?php

namespace App\Modules\Market\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Market\Services\MarketPriceService;
use App\Modules\Currency\Services\CurrencyService;
use App\Modules\Currency\Enums\CurrencyType;
use App\Modules\Market\Repositories\MarketRepository;

class MarketFiatService extends BaseService
{
    protected string $serviceName = 'MarketFiatService';

    public function __construct(
        private MarketRepository $marketRepository,
        private MarketPriceService $marketPriceService,
        private CurrencyService $currencyService
    ) {
        parent::__construct($marketRepository);
    }

    /**
     * Convert crypto currency to fiat currency
     * @param float $amount
     * @param int $currencyId
     * @return ServiceResponse
     */
    public function fiatConverter(float $amount, int $currencyId): ServiceResponse
    {
        $currency = $this->getCurrencyById($currencyId);
        $marketPrice = $this->marketPriceService->getCurrencyPriceRaw($currency->getData()->code);
        $rawMarketPrice = $marketPrice->getData();
        $data = (object) [
            'market_data' => $rawMarketPrice,
            'fiat_currency' => $rawMarketPrice->currency_id,
            'amount' => $amount,
            'price' => $rawMarketPrice->price,
        ];

        if ($currency->getData()->type == CurrencyType::Fiat->value) {
            $data->fiat_amount = $amount;
        } else {
            $convertedAmount = $amount * $rawMarketPrice->price;
            $data->fiat_amount = $convertedAmount;
        }

        return ServiceResponse::success($data, 'Fiat converted successfully');
    }

    /**
     * Get currency by id
     * @param int $id
     * @return ServiceResponse
     */
    public function getCurrencyById(int $id): ServiceResponse
    {
        return $this->currencyService->getCurrency($id);
    }
}
