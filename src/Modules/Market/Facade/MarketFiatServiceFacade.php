<?php

namespace App\Modules\Market\Facade;

use Illuminate\Support\Facades\Facade;
use App\Modules\Market\Services\MarketFiatService;

class MarketFiatServiceFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return MarketFiatService::class;
    }
}
