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
        \App\Modules\Auth\Services\TokenService::class,
        \App\Modules\Auth\Services\PasswordResetService::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'auth',
    ];

    /**
     * Register services
     */
    public function register(): void
    {
        parent::register();

        // Bind the interfaces to their implementations
        $this->app->bind(
            \App\Modules\Auth\Contracts\AuthServiceInterface::class,
            \App\Modules\Auth\Services\AuthService::class
        );

        $this->app->bind(
            \App\Modules\Auth\Contracts\TokenServiceInterface::class,
            \App\Modules\Auth\Services\TokenService::class
        );

        $this->app->bind(
            \App\Modules\Auth\Contracts\PasswordResetInterface::class,
            \App\Modules\Auth\Services\PasswordResetService::class
        );
    }
}
