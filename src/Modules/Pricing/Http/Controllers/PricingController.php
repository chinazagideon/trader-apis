<?php

namespace App\Modules\Pricing\Http\Controllers;

use App\Core\Controllers\BaseController;
use App\Modules\Pricing\Services\PricingService;
use Illuminate\Http\JsonResponse;

class PricingController extends BaseController
{
    public function __construct(
        protected PricingService $pricingService
    )
    {

    }
    /**
     * Get all pricings
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $response = $this->pricingService->getPricing();
        return $this->handleServiceResponse($response);
    }

    /**
     * Health check
     *
     * @return JsonResponse
     */
    public function health(): JsonResponse
    {
        return $this->successResponse([
            'status' => 'healthy',
            'module' => 'Pricing',
            'timestamp' => now(),
        ], 'Pricing module health check');
    }
}
