<?php

namespace App\Modules\Withdrawal\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Withdrawal\Services\WithdrawalService;
use App\Core\Http\ServiceResponse;
use Illuminate\Http\JsonResponse;

class WithdrawalController extends CrudController
{
    public function __construct(
        private WithdrawalService $withdrawalService
    ) {
        parent::__construct($withdrawalService);
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
