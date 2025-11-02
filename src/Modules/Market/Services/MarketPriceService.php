<?php

namespace App\Modules\Market\Services;

use App\Core\Services\BaseService;
use App\Modules\Market\Repositories\MarketPriceRepository;
use App\Core\Http\ServiceResponse;
use App\Modules\Market\Services\MarketService;
use App\Core\Exceptions\NotFoundException;

class MarketPriceService extends BaseService
{
    protected string $serviceName = 'MarketPriceService';

    public function __construct(
        private MarketPriceRepository $marketPriceRepository,
        private MarketService $marketService
    ) {
        parent::__construct($marketPriceRepository);
    }

    /**
     * Override index method to call the correct repository method
     */
    public function index(array $filters = [], int $perPage = 15): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($filters, $perPage) {
            $paginator = $this->marketPriceRepository->getMarketPrices($filters, $perPage);
            return $this->createPaginatedResponse($paginator, 'Market prices retrieved successfully');
        }, 'index');
    }

    /**
     * Get currency price
     * @param string $currency
     * @return ServiceResponse
     */
    public function getCurrencyPrice(string $currency): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($currency) {
            $market = $this->getMarketBySymbol($currency); // get market by symbol
            $marketId = $market->getData()->id; // get market id
            $response = $this->marketPriceRepository->getCurrencyPrice($marketId); // get currency price
            return ServiceResponse::success($response, 'Currency price retrieved successfully');
        }, 'getCurrencyPrice');
    }


    /**
     * Get currency market data by symbol
     * @param string $symbol
     * @return ServiceResponse
     */
    public function getMarketBySymbol(string $symbol): ServiceResponse
    {
        return $this->marketService->getMarketBySymbol($symbol);
    }

    /**
     * Get currency price by symbol
     * @param array $data
     * @return ServiceResponse
     */
    public function getCurrencyPriceBySymbol(array $data): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($data) {
            $market = $this->getMarketBySymbol($data['symbol']); //get market data by symbol
            $marketId = $market->getData()->id;
            $response = $this->marketPriceRepository->getCurrencyPrice($marketId); // get currency price
            return ServiceResponse::success($response, 'Currency price retrieved successfully');
        }, 'getCurrencyPriceBySymbol');
    }

    /**
     * Get currency price raw
     * @param string $currency
     * @return ServiceResponse
     */
    public function getCurrencyPriceRaw(string $currency): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($currency) {
            $market = $this->getMarketBySymbol($currency);
            $marketId = $market->getData()->id;
            $response = $this->marketPriceRepository->getCurrencyPriceRaw($marketId);
            return ServiceResponse::success($response, 'Currency price retrieved successfully');
        }, 'getCurrencyPriceRaw');
    }

}
