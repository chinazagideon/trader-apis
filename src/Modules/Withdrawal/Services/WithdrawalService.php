<?php

namespace App\Modules\Withdrawal\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Withdrawal\Repositories\WithdrawalRepository;
use App\Core\Exceptions\BusinessLogicException;
use App\Core\Exceptions\NotFoundException;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Withdrawal\Events\WithdrawalWasCompleted;
use Illuminate\Support\Facades\Log;
// use App\Modules\Market\Services\MarketFiatService;
use App\Modules\Market\Contracts\MarketFiatServiceInterface;
use App\Modules\Withdrawal\Enums\WithdrawalStatus;
use App\Modules\User\Contracts\UserBalanceServiceInterface;

class WithdrawalService extends BaseService
{
    protected string $serviceName = 'WithdrawalService';

    public function __construct(
        private WithdrawalRepository $withdrawalRepository,
        private MarketFiatServiceInterface $marketFiatService,
        private UserBalanceServiceInterface $userBalanceService
    ) {
        parent::__construct($withdrawalRepository);
    }

    /**
     * Cancel a withdrawal
     */
    public function cancelWithdrawal(int $id): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($id) {

            $withdrawal = $this->withdrawalRepository->find($id);
            if (!$withdrawal) {
                throw NotFoundException::resource('Withdrawal');
            }
            if ($withdrawal->status != 'pending') {
                throw new BusinessLogicException('Withdrawal is not pending');
            }
            $withdrawal->status = 'cancelled';
            $withdrawal->save();
            return ServiceResponse::success($withdrawal, 'Withdrawal cancelled successfully');
        }, 'cancelWithdrawal');
    }

    /**
     * Complete a withdrawal
     */
    public function completeWithdrawal(int $id): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($id) {
            $withdrawal = $this->withdrawalRepository->find($id);

            if ($withdrawal->status != 'pending') {
                throw new BusinessLogicException('Withdrawal is not pending');
            }
            $withdrawal->status = 'completed';
            $withdrawal->save();
            return ServiceResponse::success($withdrawal, 'Withdrawal completed successfully');
        }, 'completeWithdrawal');
    }

    /**
     * Dispatch the withdrawal was completed event
     * @param array $data
     * @param Model $model
     * @param string $operation
     * @return void
     */
    protected function completed(array $data, Model $model, string $operation = ''): void
    {
        Log::info('Withdrawal was completed', [
            'data' => $data,
            'model' => $model,
            'operation' => $operation,
        ]);

        /** @var Withdrawal $model */
        WithdrawalWasCompleted::dispatch($model);
    }

    /**
     * Convert amount to fiat
     * @param float $amount
     * @param int $currencyId
     * @return ServiceResponse
     */
    public function convertAmountToFiat(array $data): ServiceResponse
    {
        return $this->marketFiatService->fiatConverter([
            'amount' => $data['amount'],
            'currency_id' => $data['currency_id'],
            'fiat_currency_id' => $data['fiat_currency_id'],
        ]);
    }

    /**
     * Prepare data for store
     * @param array $data
     * @return array
     */
    public function prepareDataForStore(array $data): array
    {

        $preparedData = [
            'amount' => $data['amount'],
            'currency_id' => $data['currency_id'],
            'fiat_currency_id' => $data['fiat_currency_id'],
        ];

        $fiatAmount = $data['amount'];
        $convertFiatResponse = $this->convertAmountToFiat($preparedData);

        $convertFiat = $convertFiatResponse->getData();

        $data['amount'] = $convertFiat->crypto_amount;
        $data['fiat_amount'] = $fiatAmount;
        $data['fiat_currency_id'] = $data['fiat_currency_id'];
        $data['status'] = WithdrawalStatus::defaultStatus();


        return $data;
    }

    /**
     * Validate user balance
     * @param array $data
     * @return void
     */
    public function validateUserBalance(array $data): ?bool
    {
        return $this->userBalanceService->checkBalance($data);
    }

    /**
     * Update the withdrawal status
     * @param array $data
     * @return ServiceResponse
     */
    public function updateWithdrawalStatus(array $data): ServiceResponse
    {
        $withdrawal = $this->withdrawalRepository->find($data['withdrawal_id']);
        if (!$withdrawal) {
            throw new NotFoundException('Withdrawal not found');
        }
        $withdrawal->status = $data['status'];
        $withdrawal->save();
        return ServiceResponse::success($withdrawal, 'Withdrawal status updated successfully');
    }
}
