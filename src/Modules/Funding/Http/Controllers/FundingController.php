<?php

namespace App\Modules\Funding\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Funding\Services\FundingService;
use App\Core\Http\ServiceResponse;
use App\Modules\Funding\Enums\FundingType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Modules\Funding\Enums\FundingStatus;

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
        $convertFiatResponse = $this->fundingService->convertAmountToFiat($data['amount'], $data['currency_id']);
        $convertFiat = $convertFiatResponse->getData();

        $data['currency_id'] = (int) $data['currency_id'];
        $data['fiat_amount'] = $convertFiat->fiat_amount;
        $data['fiat_currency_id'] = $convertFiat->fiat_currency;
        $data['user_id'] = $request->user()->id;
        $data['type'] = $request->input('type');
        $data['status'] = FundingStatus::getDefaultStatus();
        return $data;
    }
}
