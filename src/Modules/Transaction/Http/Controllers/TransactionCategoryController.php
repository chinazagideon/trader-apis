<?php

namespace App\Modules\Transaction\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Transaction\Services\TransactionCategoryService;

class TransactionCategoryController extends CrudController
{
    protected string $resourceName = 'TransactionCategory';
    protected string $serviceName = 'TransactionCategoryService';

    public function __construct(TransactionCategoryService $service)
    {
        parent::__construct($service);
    }
}
