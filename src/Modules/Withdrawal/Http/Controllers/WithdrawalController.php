<?php

namespace App\Modules\Withdrawal\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Withdrawal\Services\WithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WithdrawalController extends CrudController
{
    public function __construct(
        private WithdrawalService $withdrawalService,
    ) {
        parent::__construct($withdrawalService);
    }

    /**
     * Before store operation
     * @param array $data
     * @param Request $request
     * @return array
     */
    public function beforeStore(array $data, Request $request): array
    {
        $data = $this->withdrawalService->prepareDataForStore($data);
        return $data;
    }
    /**
     * Cancel a withdrawal
     */
    public function cancel(int $id): JsonResponse
    {
        $response = $this->withdrawalService->cancelWithdrawal($id);
        return $this->handleServiceResponse($response);
    }

    /**
     * Complete a withdrawal
     */
    public function complete(int $id): JsonResponse
    {
        $response = $this->withdrawalService->completeWithdrawal($id);
        return $this->handleServiceResponse($response);
    }
}
