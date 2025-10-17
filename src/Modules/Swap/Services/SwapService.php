<?php

namespace App\Modules\Swap\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Swap\Repositories\SwapRepository;

class SwapService extends BaseService
{
    protected string $serviceName = 'SwapService';

    public function __construct(
        private SwapRepository $swapRepository
    )
    {
        parent::__construct($swapRepository);
    }

    /**
     * update swap
     * @param int $id
     * @param array $data
     * @return ServiceResponse
     */
    public function updateSwap(int $id, array $data): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($id, $data) {
            return $this->swapRepository->update($id, $data);
        }, 'updateSwap');
    }

    /**
     * calculate swap rate
     * @param int $fromCurrencyId
     * @param int $toCurrencyId
     * @return ServiceResponse
     */
    public function calculateSwapRate(int $fromCurrencyId, int $toCurrencyId): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($fromCurrencyId, $toCurrencyId) {
            return $this->swapRepository->calculateSwapRate($fromCurrencyId, $toCurrencyId);
        }, 'calculateSwapRate');
    }

    /**
     * calculate swap amount
     * @param int $fromCurrencyId
     * @param int $toCurrencyId
     * @param float $amount
     * @return ServiceResponse
     */
    public function calculateSwapAmount(int $fromCurrencyId, int $toCurrencyId, float $amount): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($fromCurrencyId, $toCurrencyId, $amount) {
            return $this->swapRepository->calculateSwapAmount($fromCurrencyId, $toCurrencyId, $amount);
        }, 'calculateSwapAmount');
    }
}
