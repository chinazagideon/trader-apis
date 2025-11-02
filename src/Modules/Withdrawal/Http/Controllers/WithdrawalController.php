<?php

namespace App\Modules\Withdrawal\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Withdrawal\Services\WithdrawalService;
use App\Core\Http\ServiceResponse;
use Illuminate\Http\JsonResponse;
use App\Modules\Withdrawal\Enums\WithdrawalStatus;
use Illuminate\Http\Request;
use App\Modules\Market\Services\MarketFiatService;

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
        $data['status'] = WithdrawalStatus::defaultStatus();
        $convertFiatResponse = $this->withdrawalService->convertAmountToFiat($data['amount'], $data['currency_id']);
        $convertFiat = $convertFiatResponse->getData();
        $data['fiat_amount'] = $convertFiat->fiat_amount;
        $data['fiat_currency_id'] = $convertFiat->fiat_currency;
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
