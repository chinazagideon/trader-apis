<?php

namespace App\Modules\Currency\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;

class CurrencyService extends BaseService
{
    protected string $serviceName = 'CurrencyService';

    public function getHealth(): ServiceResponse
    {
        return $this->executeServiceOperation(function () {
            return ServiceResponse::success([
                'status' => 'healthy',
                'module' => 'Currency',
                'timestamp' => now(),
            ], 'Currency service is healthy');
        }, 'health check');
    }
}