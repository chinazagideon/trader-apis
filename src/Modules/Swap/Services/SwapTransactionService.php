<?php

namespace App\Modules\Swap\Services;

use App\Core\Services\BaseService;
use App\Modules\Swap\Repositories\SwapTransactionRepository;

/**
 * Swap transaction service
 */
class SwapTransactionService extends BaseService
{
    protected string $serviceName = 'SwapTransactionService';
    public function __construct(SwapTransactionRepository $swapTransactionRepository)
    {
        parent::__construct($swapTransactionRepository);
    }
}
