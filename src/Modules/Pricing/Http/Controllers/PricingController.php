<?php

namespace App\Modules\Pricing\Http\Controllers;

use App\Core\Controllers\BaseController;
use App\Core\Controllers\CrudController;
use App\Modules\Pricing\Http\Requests\IndexRequest;
use App\Modules\Pricing\Services\PricingService;
use Illuminate\Http\JsonResponse;

class PricingController extends CrudController
{
    public string $moduleName = 'Pricing';
    public function __construct(
        protected PricingService $pricingService
    ) {
        parent::__construct($pricingService);
    }
    /**
     * Get all pricings
     *
     * @return JsonResponse
     */
    // public function index(): JsonResponse
    // {
    //     $response = $this->pricingService->getPricing();
    //     return $this->handleServiceResponse($response);
    // }

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

    // public function index(IndexRequest $request): JsonResponse
    // {
    //     // dd($request->all());
    //     $pricings = parent::handleIndex($request);
    //     return $this->successResponse($pricings->getData(), 'Pricings retrieved successfully');
    // }
}
