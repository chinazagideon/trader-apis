<?php

namespace App\Modules\Funding\Repositories;

use App\Core\Repositories\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Modules\Funding\Database\Models\Funding;
use App\Core\Traits\LoadsRelationships;

class FundingRepository extends BaseRepository
{
    // use LoadsRelationships;
    /**
     * Service name
     */
    protected string $serviceName = 'FundingRepository';

    /**
     * Module name
     */
    public string $moduleName = "funding";

    /**
     * Constructor
     */
    public function __construct(Funding $model)
    {
        parent::__construct($model);
    }

    /**
     * Get default relationships for the funding model
     */
    protected function getDefaultRelationships(): array
    {
        return ['fundable', 'currency', 'fiatCurrency'];
    }

    /**
     * Get the fundings
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    // public function getFundings(array $filters = [], int $perPage = 15): LengthAwarePaginator
    // {
    //     $query = $this->queryUnfiltered();
    //     $query = $this->applyFilters($query, $filters);
    //     return $this->withRelationships($query, $this->getDefaultRelationships())
    //         ->orderBy('created_at', 'desc')
    //         ->paginate($perPage);
    // }
}
