<?php

namespace App\Modules\Role\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Modules\Role\Contracts\RoleServiceContract;
use App\Modules\Role\Services\RoleService;

class RoleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Module namespace
     */
    protected string $moduleNamespace = 'App\\Modules\\Role';

     /**
     * Services
     */
    protected array $services = [
        RoleService::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'role',
    ];

    public function registerServices(): void
    {
        $this->app->bind(RoleServiceContract::class, RoleService::class);
    }
}
