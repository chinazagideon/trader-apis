<?php

namespace App\Modules\Currency\Http\Controllers;

use App\Core\Controllers\BaseController;
use App\Core\Controllers\CrudController;
use Illuminate\Http\JsonResponse;
use App\Modules\Currency\Services\CurrencyService;

class CurrencyController extends CrudController
{

    public function __construct(
        CurrencyService $currencyService
    ) {
        parent::__construct($currencyService);
    }

    // public function health(): JsonResponse
    // {
    //     return $this->successResponse([
    //         'status' => 'healthy',
    //         'module' => 'Currency',
    //         'timestamp' => now(),
    //     ], 'Currency module health check');
    // }
}
