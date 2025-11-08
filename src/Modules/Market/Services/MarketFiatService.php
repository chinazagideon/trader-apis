<?php

namespace App\Modules\Market\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Market\Services\MarketPriceService;
use App\Modules\Currency\Enums\CurrencyType;
use App\Modules\Market\Repositories\MarketRepository;
use App\Core\Exceptions\AppException;
use App\Modules\Currency\Contracts\CurrencyServiceContract;

class MarketFiatService extends BaseService
{
    protected string $serviceName = 'MarketFiatService';

    public function __construct(
        private MarketRepository $marketRepository,
        private MarketPriceService $marketPriceService,
        private CurrencyServiceContract $currencyService
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
        $currency = $this->getCurrency($currencyId);
        $defaultCurrency = $this->currencyService->getDefaultCurrency();
        $marketPrice = $this->marketPriceService->getCurrencyPriceRaw($currency->getData()->code);

        if (!$marketPrice->isSuccess()) {
            throw new AppException($marketPrice->getMessage());
        }

        $rawMarketPrice = $marketPrice->getData();
        $isStable = $rawMarketPrice->market->is_stable;
        $cryptoAmount = $this->computeCryptoAmount($amount, $rawMarketPrice->price, $currencyId, $isStable);

        $data = (object) [
            'market_data' => $rawMarketPrice,
            'fiat_currency' => $rawMarketPrice->currency_id,
            'amount' => $amount,
            'price' => $rawMarketPrice->price,
        ];

        if ($currency->getData()->type == CurrencyType::Fiat->value) {
            $data->fiat_amount = $amount;
            $data->crypto_amount = $cryptoAmount;

        } else {
            $convertedAmount = $amount * $rawMarketPrice->price;
            $data->fiat_amount = $convertedAmount;
            $data->crypto_amount = $cryptoAmount;
        }
        dd($data);

        return ServiceResponse::success($data, 'Fiat converted successfully');
    }

    /**
     * Get currency by id
     * @param int $id
     * @return ServiceResponse
     */
    public function getCurrency(int $id): ServiceResponse
    {
        return $this->currencyService->getCurrency($id);
    }

    /**
     * Compute crypto amount
     * @param float $amount
     * @param float $price
     * @param int $currencyId
     * @param bool $isStable
     * @return float
     */
    public function computeCryptoAmount(float $amount, float $price, int $currencyId, bool $isStable = false): float
    {
        $currencyType = $this->currencyService->getCurrencyTypeById($currencyId);
        if ($currencyType !== CurrencyType::Crypto->value && !$isStable) {
            throw new AppException('Currency is not a stable currency');
        }
        return $isStable ? $amount / $price : $amount * $price;
    }
}
