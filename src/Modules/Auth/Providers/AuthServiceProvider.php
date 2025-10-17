<?php

namespace App\Modules\Auth\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Support\Facades\Gate;

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
    protected function registerServices(): void
    {
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

    public function boot(): void
    {
        parent::boot();
        Gate::policy(\App\Modules\Auth\Database\Models\Auth::class, \App\Modules\Auth\Policies\AuthPolicy::class);
    }
}
