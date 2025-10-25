<?php

namespace App\Modules\Market\Services;

use App\Core\Services\BaseService;
use App\Modules\Market\Repositories\MarketPriceRepository;
use App\Core\Http\ServiceResponse;

class MarketPriceService extends BaseService
{
    protected string $serviceName = 'MarketPriceService';

    public function __construct(
        private MarketPriceRepository $marketPriceRepository
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
}
