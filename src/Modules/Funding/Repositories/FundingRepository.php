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
}
