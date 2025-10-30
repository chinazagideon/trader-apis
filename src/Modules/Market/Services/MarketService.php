<?php

namespace App\Modules\Market\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Market\Repositories\MarketRepository;

class MarketService extends BaseService
{
    protected string $serviceName = 'MarketService';

    public function __construct(
        private MarketRepository $MarketRepository
    )
    {
        parent::__construct($MarketRepository);
    }

    public function getMarketBySymbol(string $symbol): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($symbol) {
            return $this->MarketRepository->findBy('symbol', $symbol);
        }, 'getMarketBySymbol');
    }

}
