<?php

namespace App\Modules\Swap\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Modules\Swap\Services\SwapService;

class SwapServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Module namespace
     */
    protected string $moduleNamespace = 'App\\Modules\\Swap';

     /**
     * Services
     */
    protected array $services = [
        SwapService::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'swap',
    ];
}
