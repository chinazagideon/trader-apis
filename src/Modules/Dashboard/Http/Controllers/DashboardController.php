<?php

namespace App\Modules\Dashboard\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Dashboard\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends CrudController
{
    public function __construct(DashboardService $dashboardService)
    {
        parent::__construct($dashboardService);
    }

    /**
     * Get dashboard statistics
     * GET /api/dashboard/statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $this->validateFormRequest($request, 'index');

        // Extract filters from request
        $filters = $this->extractFilters($request);

        $response = $this->service->getStatistics($filters);

        $resourceClass = $this->resolveCustomResourceClass('Statistics');
        if ($resourceClass && $response->isSuccess()) {
            $response->setData(new $resourceClass($response->getData()));
        }

        return $this->handleServiceResponse($response);
    }

    /**
     * Extract filters from request
     */
    protected function extractFilters(Request $request = null): array
    {
        if (!$request) {
            return [];
        }

        return $request->only([
            'date_from',
            'date_to',
            'status',
            'user_id',
            'currency_id',
            'type',
            'include_chart_data',
            'group_by', // 'day', 'week', 'month', 'year'
        ]);
    }
}
