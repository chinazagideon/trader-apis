<?php

namespace App\Modules\User\Providers;

use App\Modules\User\Contracts\UserServiceInterface;
use App\Modules\User\Services\UserService;
use App\Core\Providers\BaseModuleServiceProvider;

class UserServiceProvider extends BaseModuleServiceProvider
{
    /**
     * The module namespace
     */
    protected string $moduleNamespace = 'App\\Modules\\User';

    /**
     * The module name
     */
    protected string $moduleName = 'User';

    /**
     * Services
     */
    protected array $services = [
        UserService::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'user',
    ];

    /**
     * Register services
     */
    protected function registerServices(): void
    {
        $this->app->bind(UserServiceInterface::class, UserService::class);
    }

    /**
     * Get module namespace
     */
    public function getModuleNamespace(): string
    {
        return $this->moduleNamespace;
    }

    /**
     * Get module name
     */
    public function getModuleName(): string
    {
        return $this->moduleName;
    }
}
