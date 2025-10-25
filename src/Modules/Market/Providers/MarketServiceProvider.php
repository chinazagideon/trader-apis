<?php

namespace App\Modules\Market\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use App\Modules\Market\Services\MarketPriceService;
use App\Modules\Market\Services\MarketService;

class MarketServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Module namespace
     */
    protected string $moduleNamespace = 'App\\Modules\\Market';

     /**
     * Services
     */
    protected array $services = [
        MarketPriceService::class,
        MarketService::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'market',
    ];
}
