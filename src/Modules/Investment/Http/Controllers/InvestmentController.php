<?php

namespace App\Modules\Investment\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Transaction\Database\Models\Transaction;
use App\Modules\Investment\Services\InvestmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InvestmentController extends CrudController
{
    /**
     * Constructor - inject InvestmentService
     */
    public function __construct(InvestmentService $investmentService)
    {
        parent::__construct($investmentService);
    }

    /**
     * Health check endpoint (custom method, not CRUD)
     */
    public function health(): JsonResponse
    {
        $response = $this->service->getHealth();
        return $this->handleServiceResponse($response);
    }

    /**
     * Custom method to get investment statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        // Custom business logic can be implemented here
        return $this->successResponse([
            'total_investments' => 0,
            'total_value' => 0,
            'by_type' => []
        ], 'Investment statistics');
    }

    /**
     * Before send data to repository
     */
    protected function beforeStore(array $data, Request $request): array
    {
 

        $data['start_date'] = $data['start_date'] ?? Carbon::now();
        return $data;
    }
}
