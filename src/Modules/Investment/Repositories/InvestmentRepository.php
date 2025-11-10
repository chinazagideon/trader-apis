<?php

namespace App\Modules\Investment\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Investment\Database\Models\Investment;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Core\Traits\LoadsRelationships;

class InvestmentRepository extends BaseRepository
{
    use LoadsRelationships;

    public string $moduleName = 'investment';
    /**
     * Constructor
     */
    public function __construct(Investment $model)
    {
        parent::__construct($model);
    }


    /**
     * Get default relationships for the withdrawal model
     */
    protected function getDefaultRelationships(): array
    {
        return ['user', 'currency', 'pricing'];
    }




}
