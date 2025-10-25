<?php

namespace App\Modules\Market\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Market\Database\Models\MarketPrice;
use Illuminate\Pagination\LengthAwarePaginator;

class MarketPriceRepository extends BaseRepository
{
    protected string $serviceName = 'MarketPriceRepository';

    public function __construct(MarketPrice $model)
    {
        parent::__construct($model);
    }

    /**
     * Get default relationships for the market price model
     */
    protected function getDefaultRelationships(): array
    {
        return ['market', 'currency'];
    }
    public function index(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->getMarketPrices($filters, $perPage);
    }
    /**
     * Get market prices with relationships
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getMarketPrices(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query();
        // $this->applyBusinessFilters($query, $filters);
        return $this->withRelationships($query, $this->getDefaultRelationships())->paginate($perPage);
    }
}
