<?php

namespace App\Modules\Auth\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use App\Modules\Auth\Services\AuthService;

class AuthServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Module namespace
     */
    protected string $moduleNamespace = 'App\\Modules\\Auth';

    /**
     * Module name
     */
    protected string $moduleName = 'Auth';

    /**
     * Services
     */
    protected array $services = [
        AuthService::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'auth',
    ];

}
