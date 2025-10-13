<?php

namespace App\Modules\Pricing\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Pricing\Database\Models\Pricing;
use App\Modules\Pricing\Http\Resources\IndexResource;

class PricingService extends BaseService
{
    protected string $serviceName = 'PricingService';

    /**
     * Get health check
     *
     * @return ServiceResponse
     */
    public function getHealth(): ServiceResponse
    {
        return $this->executeServiceOperation(function () {
            return ServiceResponse::success([
                'status' => 'healthy',
                'module' => 'Pricing',
                'timestamp' => now(),
            ], 'Pricing service is healthy');
        }, 'health check');
    }

    /**
     * Get all pricings
     *
     * @return ServiceResponse
     */
    public function getPricing(): ServiceResponse
    {
        return $this->executeServiceOperation(function () {
            $pricings = Pricing::with('currency')->get();

            $this->log('Pricings retrieved', [
                'count' => $pricings->count(),
            ]);

            // Transform data using IndexResource
            $transformedData = IndexResource::collection($pricings);

            return ServiceResponse::success($transformedData, 'Pricings retrieved successfully');
        }, 'get all pricings');
    }
}
