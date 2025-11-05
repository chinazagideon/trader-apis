<?php

namespace App\Modules\Swap\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Modules\Swap\Services\SwapService;
use App\Modules\Swap\Database\Models\Swap;
use App\Modules\Swap\Policies\SwapPolicy;

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
     * Policies
     */
    protected array $policies = [
        Swap::class => SwapPolicy::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'swap',
    ];
}
