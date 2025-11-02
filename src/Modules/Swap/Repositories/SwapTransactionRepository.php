<?php

namespace App\Modules\Swap\Repositories;

use App\Modules\Swap\Database\Models\SwapTransaction;
use App\Core\Repositories\BaseRepository;

class SwapTransactionRepository extends BaseRepository
{
    /**
     * Service name
     */
    protected string $serviceName = 'SwapTransactionRepository';

    /**
     * Constructor
     */
    public function __construct(SwapTransaction $model)
    {
        parent::__construct($model);
    }
}
