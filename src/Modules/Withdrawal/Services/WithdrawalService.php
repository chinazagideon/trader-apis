<?php

namespace App\Modules\Withdrawal\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Withdrawal\Repositories\WithdrawalRepository;
use App\Core\Exceptions\BusinessLogicException;
use App\Core\Exceptions\NotFoundException;

class WithdrawalService extends BaseService
{
    protected string $serviceName = 'WithdrawalService';

    public function __construct(
        private WithdrawalRepository $withdrawalRepository
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
}
