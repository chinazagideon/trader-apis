<?php

namespace App\Modules\Funding\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Funding\Database\Models\Funding;

class FundingRepository extends BaseRepository
{
    /**
     * Service name
     */
    protected string $serviceName = 'FundingRepository';

    /**
     * Constructor
     */
    public function __construct(Funding $model)
    {
        parent::__construct($model);
    }
}
