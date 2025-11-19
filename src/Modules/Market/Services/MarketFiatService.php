<?php

namespace App\Modules\Market\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Market\Services\MarketPriceService;
use App\Modules\Currency\Enums\CurrencyType;
use App\Modules\Market\Repositories\MarketRepository;
use App\Core\Exceptions\AppException;
use App\Modules\Currency\Contracts\CurrencyServiceContract;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use App\Modules\Market\Contracts\MarketFiatServiceInterface;

class MarketFiatService extends BaseService implements MarketFiatServiceInterface
{
    protected string $serviceName = 'MarketFiatService';
    private const CRYPTO_SCALE = 8;
    private const FIAT_SCALE = 8;


    public function __construct(
        private MarketRepository $marketRepository,
        private MarketPriceService $marketPriceService,
        private CurrencyServiceContract $currencyService
    ) {
        parent::__construct($marketRepository);
    }

    /**
     * Convert crypto currency to fiat currency
     * convert a cryptocurrency to a fiat any fiat currency
     * @param array $data [amount (in fiat|crypto currency), fiat_currency_id (fiat currency id), currency_id (crypto currency id)]
     * @return ServiceResponse
     */
    public function fiatConverter(array $data): ServiceResponse
    {
        $amount = (float) $data['amount'];  //in FIAT or CRYPTO currency
        $currencyId = (int) $data['currency_id'];
        $fiatCurrencyId = (int) $data['fiat_currency_id'];

        $this->validateCurrencyIdType($currencyId);
        $this->validateFiatCurrencyIdType($fiatCurrencyId);

        $cryptoCurrency = $this->currencyService->getCurrency($currencyId);
        $cryptoCurrencyCode = $cryptoCurrency->getData()->code;
        $cryptoMarketPrice = $this->marketPriceService->getCurrencyPriceRaw($cryptoCurrencyCode);


        $rawCryptoMarketData = $cryptoMarketPrice->getData();
        $price = $rawCryptoMarketData->price;
        $cryptoCurrencyData = $cryptoCurrency->getData()->toArray();

        $fiatAmount = $this->computeFiatAmount($amount, $price, $cryptoCurrencyData);
        $cryptoAmount = $this->computeCryptoAmount($amount, $price, $cryptoCurrencyData);

        $data = (object) [];
        $data->market_data = $rawCryptoMarketData->toArray();
        $data->fiat_currency = $rawCryptoMarketData->currency_id;
        $data->amount = $amount;
        $data->price = $rawCryptoMarketData->price;
        $data->fiat_amount = $fiatAmount;
        $data->crypto_amount = $cryptoAmount;

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
    /**
     * Convert a fiat amount to crypto using a fiat-per-crypto price.
     * Returns string to preserve precision.
     */
    public function computeCryptoAmount(float|string $fiatAmount, float|string $fiatPerCryptoPrice, array $currency): string
    {
        $price = BigDecimal::of((string) $fiatPerCryptoPrice);

        if ($price->isLessThanOrEqualTo('0')) {
            throw new AppException('Invalid market price');
        }

        $fiat = BigDecimal::of((string) $fiatAmount);

        // crypto = fiat / price
        $result = (string) $fiat
            ->dividedBy($price, self::CRYPTO_SCALE, RoundingMode::DOWN)
            ->toScale(self::CRYPTO_SCALE, RoundingMode::DOWN);



        return $result;
    }
    /**
     * Validate currency type
     * @param string $currencyType
     * @return void
     */
    private function validateCurrencyIdType(int $currencyId): void
    {
        $isCryptoCurrency = $this->currencyService->isCryptoCurrency($currencyId);
        if (!$isCryptoCurrency)
            throw new AppException('Currency ID must be crypto');
    }
    /**
     * Validate fiat currency id type
     * @param int $fiatCurrencyId
     * @return void
     */
    private function validateFiatCurrencyIdType(int $fiatCurrencyId): void
    {
        $isFiatCurrency = $this->currencyService->isFiatCurrency($fiatCurrencyId);
        if (!$isFiatCurrency)
            throw new AppException('Fiat currency ID must be fiat');
    }
    /**
     * Compute fiat amount.
     * - If input currency is crypto: fiat = crypto * fiatPerCryptoPrice
     * - If input currency is fiat: return amount (scaled)
     */
    public function computeFiatAmount(float|string $amount, float|string $fiatPerCryptoPrice, array $currency): string
    {
        $price = BigDecimal::of((string) $fiatPerCryptoPrice);
        if ($price->isLessThanOrEqualTo('0')) {
            throw new AppException('Invalid market price');
        }

        $amt = BigDecimal::of((string) $amount);

        // If the provided currency is fiat, no conversion is needed; just scale.
        if (($currency['type'] ?? null) === CurrencyType::Fiat->value) {
            return (string) $amt->toScale(self::FIAT_SCALE, RoundingMode::HALF_UP);
        }

        // Otherwise, treat amount as crypto and convert to fiat.
        return (string) $amt
            ->multipliedBy($price)
            ->toScale(self::FIAT_SCALE, RoundingMode::HALF_UP);
    }
}
