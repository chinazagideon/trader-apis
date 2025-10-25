<?php

namespace App\Modules\Market\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Market\Services\MarketPriceService;

class MarketPriceController extends CrudController
{
    public function __construct(
        private MarketPriceService $marketPriceService
    ) {
        parent::__construct($marketPriceService);
    }
}
