<?php

namespace App\Modules\Currency\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Currency\Database\Models\Currency;
use Illuminate\Pagination\LengthAwarePaginator;

class CurrencyRepository extends BaseRepository
{
    protected string $serviceName = 'CurrencyRepository';

    /**
     * Constructor
     */
    public function __construct(Currency $model)
    {
        parent::__construct($model);
    }

    /**
     * Get currencies with filters and pagination
     */
    public function getCurrencies(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->queryUnfiltered();
        $query = $this->applyFilters($query, $filters);
        return $this->withRelationships($query, $this->getDefaultRelationships())
            ->paginate($perPage);
    }

    /**
     * Get default currency
     * @return Currency
     */
    public function getDefaultCurrency(): Currency
    {
        return $this->model->where('is_default', true)->first();
    }

}
