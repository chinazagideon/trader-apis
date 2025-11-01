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

class WithdrawalService extends BaseService
{
    protected string $serviceName = 'WithdrawalService';

    public function __construct(
        private WithdrawalRepository $withdrawalRepository,
        private WithdrawalWasCompleted $withdrawalWasCompleted,
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
        // if (str_contains($operation, "store")) {
        $moduleName = strtolower($this->withdrawalRepository->moduleName);
        /** @var Withdrawal $withdrawal = $model */
        $this->withdrawalWasCompleted->dispatch($model, $moduleName);
        // }
    }
}
