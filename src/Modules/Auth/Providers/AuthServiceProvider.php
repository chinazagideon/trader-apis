<?php

namespace App\Modules\Auth\Providers;

use App\Modules\Auth\Services\AuthService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AuthServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'App\\Modules\\Auth';
    protected string $moduleName = 'Auth';

    public function register(): void
    {
        $this->registerConfig();
        $this->registerMigrations();
        $this->registerServices();
    }

    public function boot(): void
    {

        $this->loadViews();
        $this->loadTranslations();
    }

    protected function registerConfig(): void
    {
        $configPath = $this->getModulePath('config');

        if (is_dir($configPath)) {
            $this->publishes([
                $configPath => config_path("modules/auth"),
            ], "auth-config");
        }
    }

    /*
     * Register migrations
     */
    protected function registerMigrations(): void
    {
        $migrationPath = $this->getModulePath('database/migrations');

        if (is_dir($migrationPath)) {
            $this->loadMigrationsFrom($migrationPath);
        }
    }

    /**
     * Register services
     */
    protected function registerServices(): void
    {
        // Register Auth services
        $this->app->bind(\App\Modules\Auth\Contracts\AuthServiceInterface::class, \App\Modules\Auth\Services\AuthService::class);
        $this->app->bind(\App\Modules\Auth\Contracts\TokenServiceInterface::class, \App\Modules\Auth\Services\TokenService::class);
        $this->app->bind(\App\Modules\Auth\Contracts\PasswordResetInterface::class, \App\Modules\Auth\Services\PasswordResetService::class);
    }

    /**
     * Load routes
     */
    protected function loadRoutes(): void
    {
        $routesPath = $this->getModulePath('routes');

        if (is_dir($routesPath)) {
            if (file_exists($routesPath . '/api.php')) {
                Route::prefix('api/v1')
                    ->middleware(['api'])
                    ->group($routesPath . '/api.php');
            }

            if (file_exists($routesPath . '/web.php')) {
                Route::middleware(['web'])
                    ->group($routesPath . '/web.php');
            }
        }
    }

    /**
     * Load views
     */
    protected function loadViews(): void
    {
        $viewsPath = $this->getModulePath('resources/views');

        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, strtolower($this->moduleName));
        }
    }

    /**
     * Load translations
     */
    protected function loadTranslations(): void
    {
        $langPath = $this->getModulePath('resources/lang');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, strtolower($this->moduleName));
        }
    }

    /**
     * Get module path
     */
    protected function getModulePath(string $path = ''): string
    {
        $basePath = base_path("src/Modules/Auth");

        return $path ? $basePath . '/' . $path : $basePath;
    }
}
