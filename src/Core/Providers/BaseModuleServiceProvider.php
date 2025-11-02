<?php

namespace App\Core\Providers;

use App\Core\Contracts\ModuleServiceProviderInterface;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

abstract class BaseModuleServiceProvider extends ModuleServiceProviderBase implements ModuleServiceProviderInterface
{
    /**
     * Get the module namespace
     */
    public function getModuleNamespace(): string
    {
        return 'App\\Modules\\' . $this->getModuleName();
    }

    /**
     * Get the module path
     */
    public function getModulePath(): string
    {
        if ($this->modulePath === null) {
            $this->modulePath = base_path('src/Modules/' . $this->getModuleName());
        }
        return $this->modulePath;
    }

    /**
     * Get the priority for this provider (higher = earlier registration)
     * Default is 0, negative values register later
     */
    public function getPriority(): int
    {
        return 0;
    }

    /**
     * Check if this provider should be registered
     * Useful for conditional registration based on environment or config
     */
    public function shouldRegister(): bool
    {
        return true;
    }

    // Child modules override to bind concrete services
    protected function registerServices(): void {}

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->registerServices();
    }

    /**
     * Bootstrap the service provider.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy($this->getModuleNamespace(), $this->getModuleNamespace() . '\\Policies\\' . $this->getModuleName() . 'Policy');
    }
}
