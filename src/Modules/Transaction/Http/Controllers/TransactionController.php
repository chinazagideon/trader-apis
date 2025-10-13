<?php

namespace App\Modules\Transaction\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Transaction\Services\TransactionService;

class TransactionController extends CrudController
{
    public function __construct(
        private TransactionService $transactionService
    ) {
        parent::__construct($transactionService);
    }



}
