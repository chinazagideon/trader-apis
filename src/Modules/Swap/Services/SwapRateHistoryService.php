<?php

namespace App\Modules\Swap\Services;

use App\Core\Services\BaseService;
use App\Modules\Swap\Repositories\SwapRateHistoryRepository;

class SwapRateHistoryService extends BaseService
{
    protected string $serviceName = 'SwapRateHistoryService';
    public function __construct(SwapRateHistoryRepository $swapRateHistoryRepository)
    {
        parent::__construct($swapRateHistoryRepository);
    }
}
