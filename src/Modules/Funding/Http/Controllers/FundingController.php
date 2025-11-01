<?php

namespace App\Modules\Funding\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Funding\Services\FundingService;
use App\Core\Http\ServiceResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FundingController extends CrudController
{
    public function __construct(
        private FundingService $fundingService
    ) {
        parent::__construct($fundingService);
    }

    /**
     * @inheritDoc
     */
    public function beforeStore(array $data, Request $request): array
    {
        $data['user_id'] = $request->user()->id;
        $data['amount'] = $request->input('amount');
        $data['type'] = $request->input('type');
        return $data;
    }
}
