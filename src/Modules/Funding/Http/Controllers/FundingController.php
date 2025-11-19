<?php

namespace App\Modules\Funding\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Funding\Services\FundingService;
use App\Core\Http\ServiceResponse;
use App\Modules\Funding\Enums\FundingType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Modules\Funding\Enums\FundingStatus;
use App\Modules\Funding\Http\Requests\FundingIndexRequest;
use App\Modules\Funding\Http\Requests\FundingCreateRequest;

class FundingController extends CrudController
{
    public function __construct(
        private FundingService $fundingService,
    ) {
        parent::__construct($fundingService);
    }

    /**
     * @inheritDoc
     */
    public function beforeStore(array $data, Request $request): array
    {
        return $this->fundingService->prepareDataForStore($data);
    }

}
