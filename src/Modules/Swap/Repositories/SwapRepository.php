<?php

namespace App\Modules\Swap\Repositories;

use App\Modules\Swap\Database\Models\Swap;
use App\Core\Repositories\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;



class SwapRepository extends BaseRepository
{
    /**
     * Service name
     */
    protected string $serviceName = 'SwapRepository';

    /**
     * Constructor
     */
    public function __construct(Swap $model)
    {
        parent::__construct($model);
    }
    /**
     * Get the default relationships for the swap model
     */
    protected function getDefaultRelationships(): array
    {
        return ['user', 'fromCurrency', 'toCurrency'];
    }
    /**
     * calculate swap rate
     * @param int $fromCurrencyId
     * @param int $toCurrencyId
     * @return float
     */
    public function getSwapRate(int $fromCurrencyId, int $toCurrencyId): float
    {
        return $this->model
            ->where('from_currency_id', $fromCurrencyId)
            ->where('to_currency_id', $toCurrencyId)
            ->first()
            ->rate;
    }

    /**
     * Get swaps with pagination and filters
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getSwaps(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query(); // Already has ownership filtering applied

        // Apply additional business filters
        $this->applyBusinessFilters($query, $filters);

        return $this->withRelationships($query, $this->getDefaultRelationships())
            ->paginate($perPage);
    }
}
