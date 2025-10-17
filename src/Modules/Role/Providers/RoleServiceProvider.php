<?php

namespace App\Modules\Role\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use Illuminate\Support\Facades\Route;

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
        'RoleService'::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'role',
    ];
}