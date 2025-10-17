<?php

namespace App\Modules\Swap\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Swap\Services\SwapTransactionService;

class SwapTransactionController extends CrudController
{
    public function __construct(
        private SwapTransactionService $swapTransactionService
    ) {
        parent::__construct($swapTransactionService);
    }
}
