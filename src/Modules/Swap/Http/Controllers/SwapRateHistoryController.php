<?php

namespace App\Modules\Swap\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Swap\Services\SwapRateHistoryService;

class SwapRateHistoryController extends CrudController
{
    public function __construct(
        private SwapRateHistoryService $swapRateHistoryService
    ) {
        parent::__construct($swapRateHistoryService);
    }
}
