<?php

namespace App\Modules\Market\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use App\Modules\Market\Services\MarketPriceService;
use App\Modules\Market\Services\MarketService;
use App\Modules\Market\Services\MarketFiatService;
use App\Modules\Market\Facade\MarketFiatServiceFacade;
use Illuminate\Support\Facades\Facade as Facades;
use App\Modules\Market\Database\Models\MarketPrice;
use App\Modules\Market\Policies\MarketPricePolicy;
use App\Modules\Market\Contracts\MarketFiatServiceInterface;
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
        MarketFiatService::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'market',
    ];

    /**
     * Policies
     */
    protected array $policies = [
        MarketPrice::class => MarketPricePolicy::class,
    ];

    public function registerServices(): void
    {
        $this->app->bind(MarketFiatServiceInterface::class, MarketFiatService::class);
    }
}
