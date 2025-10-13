<?php

namespace App\Modules\Investment\Providers;

use App\Modules\Investment\Services\InvestmentService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class InvestmentServiceProvider extends ServiceProvider
{
    /**
     * Module namespace
     */
    protected string $moduleNamespace = 'App\\Modules\\Investment';

    /**
     * Module name
     */
    protected string $moduleName = 'Investment';

    /**
     * Module name
     */
    protected array $allowedTypes;

    /**
     * Register
     */
    public function register(): void
    {
        $this->registerConfig();
        $this->registerMigrations();
        $this->registerServices();
    }

    /**
     * Boot
     */
    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadViews();
        $this->loadTranslations();
    }

    /**
     * Register config
     */
    protected function registerConfig(): void
    {
        $configPath = $this->getModulePath('config');

        if (is_dir($configPath)) {
            // Load investment config
            $this->mergeConfigFrom($configPath . '/investment.php', 'investment');

            $this->publishes([
                $configPath => config_path("modules/investment"),
            ], "investment-config");
        }
    }

    /**
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
        $this->app->bind(InvestmentService::class, InvestmentService::class);
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
        $basePath = base_path("src/Modules/Investment");

        return $path ? $basePath . '/' . $path : $basePath;
    }
}
