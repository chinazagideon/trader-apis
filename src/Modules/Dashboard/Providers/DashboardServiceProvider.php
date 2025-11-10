<?php

namespace App\Modules\Dashboard\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use App\Modules\Dashboard\Services\DashboardService;

class DashboardServiceProvider extends BaseModuleServiceProvider
{

    /**
     * Module namespace
     */
    protected string $moduleNamespace = 'App\\Modules\\Dashboard';

    /**
     * Module name
     */
    protected string $moduleName = 'Dashboard';

    /**
     * Services
     */
    protected array $services = [
        DashboardService::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'dashboard',
    ];
}
