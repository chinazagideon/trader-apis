<?php

namespace App\Modules\Market\Services;

use App\Core\Services\BaseService;
use App\Modules\Market\Repositories\MarketPriceRepository;
use App\Core\Http\ServiceResponse;
use App\Modules\Market\Services\MarketService;
use App\Core\Exceptions\NotFoundException;
use App\Core\Exceptions\AppException;
use App\Core\Contracts\SubModuleServiceContract;
use App\Modules\Market\Database\Models\MarketPrice;

class MarketPriceService extends BaseService implements SubModuleServiceContract
{
    protected string $serviceName = 'MarketPriceService';
    protected string $defaultSubModuleName = 'MarketPrice';

    public function __construct(
        private MarketPriceRepository $marketPriceRepository,
        private MarketService $marketService
    ) {
        parent::__construct($marketPriceRepository);
    }

    /**
     * Get the default module name
     */
    public function getDefaultSubModuleName(): string
    {
        return $this->defaultSubModuleName;
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

            $market = $this->getMarketBySymbol($currency);

            $marketDataId = $market->getData()?->id ?? null;

            if (!$marketDataId) {
                throw new AppException('Market not found with symbol: ' . $currency);
            }

            $marketPrice = $this->marketPriceRepository->getCurrencyPriceRaw($marketDataId);

            $marketPriceResponse = $this->marketPriceResponse($marketPrice);

            if (!$marketPriceResponse->isSuccess()) {
                return $marketPriceResponse;
            }
            return $marketPriceResponse;

    }

    /**
     * Market price response
     * @param mixed $marketPrice
     * @return ServiceResponse
     */
    private function marketPriceResponse(mixed $marketPrice): ServiceResponse
    {
        if ($marketPrice instanceof ServiceResponse) {
            return $marketPrice;
        }
        if ($marketPrice instanceof MarketPrice) {
            return ServiceResponse::success($marketPrice, 'Currency price retrieved successfully');
        }
        if (is_array($marketPrice)) {
            return ServiceResponse::success($marketPrice, 'Currency price retrieved successfully');
        }
        return ServiceResponse::error('Invalid market price');
    }
}
