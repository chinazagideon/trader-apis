<?php

namespace App\Modules\Swap\Repositories;

use App\Modules\Swap\Database\Models\SwapRateHistory;
use App\Core\Repositories\BaseRepository;

class SwapRateHistoryRepository extends BaseRepository
{
    /**
     * Service name
     */
    protected string $serviceName = 'SwapRateHistoryRepository';

    /**
     * Constructor
     */
    public function __construct(SwapRateHistory $model)
    {
        parent::__construct($model);
    }
}
