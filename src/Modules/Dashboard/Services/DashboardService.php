<?php

namespace App\Modules\Dashboard\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Dashboard\Repositories\DashboardRepository;

class DashboardService extends BaseService
{
    protected string $serviceName = 'DashboardService';

    public function __construct(
        private DashboardRepository $dashboardRepository
    ) {
        parent::__construct($dashboardRepository);
    }

    /**
     * Get dashboard statistics with filters
     */
    public function getStatistics(array $filters = []): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($filters) {
            $statistics = $this->dashboardRepository->getDashboardStatistics($filters);

            // Optionally include time-series data
            if (isset($filters['include_chart_data']) && $filters['include_chart_data']) {
                $groupBy = $filters['group_by'] ?? 'day';
                $statistics['chart_data'] = $this->dashboardRepository->getStatisticsByDate($filters, $groupBy);
            }

            return ServiceResponse::success($statistics, 'Dashboard statistics retrieved successfully');
        }, 'getStatistics');
    }
}
