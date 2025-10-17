<?php

namespace App\Modules\Swap\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Swap\Services\SwapService;
use App\Core\Http\ServiceResponse;
use Illuminate\Http\JsonResponse;

class SwapController extends CrudController
{
    public function __construct(
        private SwapService $swapService
    ) {
        parent::__construct($swapService);
    }

    public function hello(): JsonResponse
    {
        return $this->successResponse([], 'Hello from Swap module');
    }
}