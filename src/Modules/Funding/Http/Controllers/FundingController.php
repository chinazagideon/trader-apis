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
        return $this->prepareData($data);
    }

    /**
     * Prepare data for funding creation
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {

        $convertFiatResponse = $this->fundingService->convertAmountToFiat(
            [
                'amount' => $data['amount'],
                'currency_id' => $data['currency_id'],
                'fiat_currency_id' => $data['fiat_currency_id'],
            ]
        );

        $convertFiat = $convertFiatResponse->getData();

        $data['fundable_type'] = $data['fundable_type'];
        $data['fundable_id'] = $data['fundable_id'];
        $data['amount'] = $convertFiat->crypto_amount;
        $data['currency_id'] = (int) $data['currency_id'];
        $data['fiat_amount'] = $convertFiat->fiat_amount;
        $data['fiat_currency_id'] = $convertFiat->fiat_currency;
        $data['user_id'] = $data['user_id'];
        $data['type'] = $data['type'];
        $data['status'] = FundingStatus::getDefaultStatus();

        return $data;
    }
}
