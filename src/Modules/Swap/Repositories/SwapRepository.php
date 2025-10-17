<?php

namespace App\Modules\Swap\Repositories;

use App\Modules\Swap\Database\Models\Swap;
use App\Core\Repositories\BaseRepository;


class SwapRepository extends BaseRepository
{
    /**
     * Service name
     */
    protected string $serviceName = 'SwapRepository';

    /**
     * Constructor
     */
    public function __construct(Swap $model)
    {
        parent::__construct($model);
    }

    /**
     * calculate swap rate
     * @param int $fromCurrencyId
     * @param int $toCurrencyId
     * @return float
     */
    public function calculateSwapRate(int $fromCurrencyId, int $toCurrencyId): float
    {
        return $this->model->where('from_currency_id', $fromCurrencyId)->where('to_currency_id', $toCurrencyId)->first()->rate;
    }

    /**
     * calculate swap amount
     * @param int $fromCurrencyId
     * @param int $toCurrencyId
     * @param float $amount
     * @return float
     */
    public function calculateSwapAmount(int $fromCurrencyId, int $toCurrencyId, float $amount): float
    {
        return $this->calculateSwapRate($fromCurrencyId, $toCurrencyId) * $amount;
    }
}
