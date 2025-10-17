<?php

namespace App\Modules\Payment\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Payment\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends CrudController
{
    /**
     * Constructor - inject PaymentService
     */
    public function __construct(
        private PaymentService $paymentService
    ) {
        parent::__construct($paymentService);
    }

    /**
     * Before store operation
     * @param array $data
     * @param Request $request
     * @return array
     */
    public function beforeStore(array $data, Request $request): array
    {
        $data['uuid'] = $this->referenceExistInRequest($data)
                            ? $data['reference'] : Str::uuid();
        return $data;
    }

    /**
     * validate if reference exist in request
     * @param array $data
     * @return bool
     */
    public function referenceExistInRequest(array $data): bool
    {
        return isset($data['reference']);
    }
}
