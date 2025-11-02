<?php

namespace App\Modules\Swap\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Swap\Services\SwapService;
use App\Core\Http\ServiceResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SwapController extends CrudController
{
    public function __construct(
        private SwapService $swapService
    ) {
        parent::__construct($swapService);
    }

    public function hello(): JsonResponse
    {
        return $this->successResponse([], 'Hello from Swap module');
    }

    /**
     * before store
     * @param array $data
     * @param Request $request
     * @return array
     */
    protected function beforeStore(array $data, Request $request): array
    {
        return $this->swapService->performSwapCalculations($data);
    }

    /**
     * before update
     * @param array $data
     * @param Request $request
     * @return array
     */
    protected function beforeUpdate(array $data, Request $request, $id): array
    {
        $data = $this->swapService->performSwapCalculations($data);
        $data['id'] = $id;
        return $data;
    }
}
