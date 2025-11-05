<?php

namespace App\Modules\Funding\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Currency\Services\CurrencyService;
use App\Modules\Funding\Repositories\FundingRepository;
use App\Modules\Funding\Database\Models\Funding;
use App\Modules\Funding\Events\FundingWasCompleted;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Market\Services\MarketFiatService;

class FundingService extends BaseService
{
    protected string $serviceName = 'FundingService';

    public function __construct(
        private FundingRepository $FundingRepository,
        private MarketFiatService $marketFiatService,
        private CurrencyService $currencyService
    ) {
        parent::__construct($FundingRepository);
    }
    /**
     * Convert amount to fiat
     * @param float $amount
     * @param int $currencyId
     * @return ServiceResponse
     */
    public function convertAmountToFiat(float $amount, int $currencyId): ServiceResponse
    {
        return $this->marketFiatService->fiatConverter($amount, $currencyId);
    }
    /**
     * Override the completed method to emit the FundingWasCompleted event
     *
     * @param array $data
     * @param Model $model
     * @param string $operation
     * @return void
     */
    protected function completed(array $data, Model $model, string $operation = ''): void
    {
        /** @var Funding $model */
        FundingWasCompleted::dispatch($model, $this->FundingRepository->moduleName);

    }
}
