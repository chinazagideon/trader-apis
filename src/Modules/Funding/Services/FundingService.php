<?php

namespace App\Modules\Funding\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Funding\Repositories\FundingRepository;
use App\Modules\Funding\Database\Models\Funding;
use App\Modules\Funding\Events\FundingWasCompleted;
use Illuminate\Database\Eloquent\Model;

class FundingService extends BaseService
{
    protected string $serviceName = 'FundingService';

    public function __construct(
        private FundingRepository $FundingRepository,
    ) {
        parent::__construct($FundingRepository);
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
        $this->logBusinessLogic(
            'Funding was completed',
            [
                'data' => $data,
                'model' => $model,
                'operation' => $operation,
            ]
        );
        /** @var Funding $model */
        FundingWasCompleted::dispatch($model, $this->FundingRepository->moduleName);

    }
}
