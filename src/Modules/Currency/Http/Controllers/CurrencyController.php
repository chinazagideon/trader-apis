<?php

namespace App\Modules\Currency\Http\Controllers;

use App\Core\Controllers\BaseController;
use Illuminate\Http\JsonResponse;

class CurrencyController extends BaseController
{
    public function index(): JsonResponse
    {
        return $this->successResponse([], 'Currency module is working');
    }

    public function health(): JsonResponse
    {
        return $this->successResponse([
            'status' => 'healthy',
            'module' => 'Currency',
            'timestamp' => now(),
        ], 'Currency module health check');
    }
}