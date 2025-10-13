<?php

namespace App\Modules\Currency\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use App\Modules\Currency\Services\CurrencyService;

class CurrencyServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Module namespace
     */
    protected string $moduleNamespace = 'App\\Modules\\Currency';

    /**
     * Module name
     */
    protected string $moduleName = 'Currency';

    /**
     * Services
     */
    protected array $services = [
        CurrencyService::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'currency',
    ];
}
