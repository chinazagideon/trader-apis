<?php

namespace App\Modules\Pricing\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use App\Modules\Pricing\Services\PricingService;

class PricingServiceProvider extends BaseModuleServiceProvider
{

    /**
     * Module namespace
     */
    protected string $moduleNamespace = 'App\\Modules\\Pricing';

    /**
     * Module name
     */
    protected string $moduleName = 'Pricing';

    /**
     * Services
     */
    protected array $services = [
        PricingService::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'pricing',
    ];
}
