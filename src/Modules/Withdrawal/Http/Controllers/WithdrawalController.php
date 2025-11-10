<?php

namespace App\Modules\Withdrawal\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Withdrawal\Services\WithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Modules\User\Enums\UserBalanceEnum;

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
        $this->withdrawalService->validateUserBalance($this->prepareCheckBalanceData($data));
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

    /**
     * Prepare data for checking user balance
     * @param array $data
     * @return array
     */
    private function prepareCheckBalanceData(array $data): array
    {

        return [
            'user_id' => $data['user_id'],
            'amount' => $data['amount'],
            'type' => UserBalanceEnum::Withdraw->value,
        ];
    }
}
