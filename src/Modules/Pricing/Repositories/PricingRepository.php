<?php

namespace App\Modules\Pricing\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Pricing\Database\Models\Pricing;

class PricingRepository extends BaseRepository
{
    public function __construct(Pricing $model)
    {
        parent::__construct($model);
    }


    /**
     * Define allowed filter fields for security and performance
     */
    protected function getAllowedFilterFields(): array
    {
        return ['type', 'status', 'currency_id', 'created_at'];
    }
}
