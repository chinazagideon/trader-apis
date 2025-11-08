<?php

namespace App\Modules\Market\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Core\Traits\LoadsRelationships;
use App\Modules\Market\Repositories\MarketRepository;

class MarketService extends BaseService
{
    use LoadsRelationships;

    protected string $serviceName = 'MarketService';

    public function __construct(
        private MarketRepository $marketRepository
    ) {
        parent::__construct($marketRepository);
    }

    public function getMarketBySymbol(string $symbol): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($symbol) {
            return $this->marketRepository->findBy('symbol', $symbol);
        }, 'getMarketBySymbol');
    }

    public function isStableMarket(int $marketId): bool
    {
        return $this->marketRepository->findBy('is_stable', $marketId)->exists();
    }
}
