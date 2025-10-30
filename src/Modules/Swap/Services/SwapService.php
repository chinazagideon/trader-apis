<?php

namespace App\Modules\Swap\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Swap\Repositories\SwapRepository;
use App\Modules\Market\Services\MarketPriceService;
use App\Modules\Currency\Services\CurrencyService;
use InvalidArgumentException;

class SwapService extends BaseService
{
    protected string $serviceName = 'SwapService';

    public function __construct(
        private SwapRepository $swapRepository,
        private MarketPriceService $marketPriceService,
        private CurrencyService $currencyService
    ) {
        parent::__construct($swapRepository);
    }

    /**
     * update swap
     * @param int $id
     * @param array $data
     * @return ServiceResponse
     */
    public function updateSwap(int $id, array $data): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($id, $data) {
            return $this->swapRepository->update($id, $data);
        }, 'updateSwap');
    }

    /**
     * calculate swap fee
     * @param array $data
     * @return float
     */
    public function calculateSwapFee(array $data): float
    {
        return $data['from_amount'] * 0.001;
    }

    /**
     * check if currency type is fiat
     * @param string $currencyType
     * @return bool
     */
    protected function fiatType(string $currencyType): bool
    {
        return $currencyType == 'fiat';
    }

    /**
     * check if currency type is crypto
     * @param string $currencyType
     * @return bool
     */
    protected function cryptoType(string $currencyType): bool
    {
        return $currencyType == 'crypto';
    }

    /**
     * get swap type
     * @param array $data
     * @return string
     */
    protected function isFiatSwap(array $data): bool
    {
        return $this->fiatType($data['from_currency_type']) && $this->fiatType($data['to_currency_type']);
    }

    /**
     * check if swap type is crypto
     * @param array $data
     * @return bool
     */
    protected function isCryptoSwap(array $data): bool
    {
        return $this->cryptoType($data['from_currency_type']) && $this->cryptoType($data['to_currency_type']);
    }

    /**
     * calculate swap amount
     * @param array $data
     * @return float
     */
    public function calculateSwapAmount(array $data): float
    {
        $getFromCurrencyPrice = $this->getMarketPrice($data['from_currency_code']);
        $getToCurrencyPrice = $this->getMarketPrice($data['to_currency_code']);

        if ($this->isFiatSwap($data)) {
            $fromCurrencyPrice = $getFromCurrencyPrice->getData();
            $toCurrencyPrice = $getToCurrencyPrice->getData();
            return $data['from_amount'] * $fromCurrencyPrice / $toCurrencyPrice;
        } elseif ($this->isCryptoSwap($data)) {
            return $this->swapCrypto($data);
        } else {
            throw new InvalidArgumentException('Invalid swap type');
        }
    }

    /**
     * swap crypto
     * @param string $fromCurrencyCode
     * @param string $toCurrencyCode
     * @param float $amount
     * @return float
     */
    public function swapCrypto(array $data): float
    {
        $getFromCurrencyPrice = $this->getMarketPrice($data['from_currency_code']);
        $getToCurrencyPrice = $this->getMarketPrice($data['to_currency_code']);
        $fromCurrencyPrice = $getFromCurrencyPrice->getData();
        $toCurrencyPrice = $getToCurrencyPrice->getData();


        $fromCurrencyAmount = $data['from_amount'] * $fromCurrencyPrice;
        $toCurrencyAmount = $fromCurrencyAmount / $toCurrencyPrice;
        $toCurrencyAmount = round($toCurrencyAmount, 8);
        return $toCurrencyAmount;
    }

    /**
     * swap fiat
     * @param array $data
     * @return float
     */
    public function swapFiat(array $data): float
    {
        $fromMarketPrice = $this->getMarketPrice($data['from_currency_code']);
        $toMarketPrice = $this->getMarketPrice($data['to_currency_code']);

        $amount = $data['from_amount'];

        $fromRate = $fromMarketPrice->getData();
        $toRate = $toMarketPrice->getData();


        $toAmount = $amount * $toRate;
        return $toAmount;
    }
    /**
     * get market price
     * @param string $currency
     * @return ServiceResponse
     */
    public function getMarketPrice(string $currency): ServiceResponse
    {
        return $this->marketPriceService->getCurrencyPrice($currency);
    }

    /**
     * get total amount
     * @param array $data
     * @return float
     */
    public function calculateTotalAmount(array $data): float
    {
        $feeAmount = $this->calculateSwapFee($data);
        $toAmount = $this->calculateSwapAmount($data);
        return $toAmount + $feeAmount;
    }

    /**
     * get rate
     * @param array $data
     * @return float
     */
    public function getRate(array $data): float
    {
        $totalAmount = $this->calculateTotalAmount($data);
        return $totalAmount / $data['from_amount'];
    }

    /**
     * get fee amount
     * @param array $data
     * @return float
     */
    public function getFeeAmount(array $data): float
    {
        return $data['fee_amount'];
    }

    /**
     * perform swap calculations
     * @param array $data
     * @return array
     */
    public function performSwapCalculations(array $data): array
    {
        $data['fee_amount'] = $this->calculateSwapFee($data);
        $data['to_amount'] = $this->calculateSwapAmount($data);
        $data['total_amount'] = $this->calculateTotalAmount($data);
        $data['from_currency_id'] = $this->getCurrencyByCode($data['from_currency_code'])->getData();
        $data['to_currency_id'] = $this->getCurrencyByCode($data['to_currency_code'])->getData();
        $data['rate'] = $this->getRate($data);

        return $data;
    }
    /**
     * get currency by code
     * @param string $code
     * @return ServiceResponse
     */
    public function getCurrencyByCode(string $code): ServiceResponse
    {
        return $this->currencyService->getCurrencyByCode($code);
    }
}
