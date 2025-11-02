<?php

namespace App\Modules\Market\Facade;

use Illuminate\Support\Facades\Facade;
use App\Modules\Market\Services\MarketService;

class MarketFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return MarketService::class;
    }
}
