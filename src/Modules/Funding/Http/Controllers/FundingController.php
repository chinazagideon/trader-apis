<?php

namespace App\Modules\Funding\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Funding\Services\FundingService;
use App\Core\Http\ServiceResponse;
use Illuminate\Http\JsonResponse;

class FundingController extends CrudController
{
    public function __construct(
        private FundingService $fundingService
    ) {
        parent::__construct($fundingService);
    }

    public function hello(): JsonResponse
    {
        return $this->successResponse([], 'Hello from Funding module');
    }
}