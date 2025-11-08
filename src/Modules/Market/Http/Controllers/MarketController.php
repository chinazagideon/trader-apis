<?php

namespace App\Modules\Market\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Market\Services\MarketService;
use App\Core\Http\ServiceResponse;
use Illuminate\Http\JsonResponse;

class MarketController extends CrudController
{
    public string $moduleName = 'Market';

    public function __construct(
        private MarketService $marketService
    ) {
        parent::__construct($marketService);
    }

    public function hello(): JsonResponse
    {
        return $this->successResponse([], 'Hello from Market module');
    }
}
