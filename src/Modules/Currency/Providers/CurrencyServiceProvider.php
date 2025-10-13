<?php

namespace App\Modules\Currency\Providers;

use App\Modules\Currency\Services\CurrencyService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class CurrencyServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'App\\Modules\\Currency';
    protected string $moduleName = 'Currency';

    public function register(): void
    {
        $this->registerConfig();
        $this->registerMigrations();
        $this->registerServices();
    }

    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadViews();
        $this->loadTranslations();
    }

    protected function registerConfig(): void
    {
        $configPath = $this->getModulePath('config');

        if (is_dir($configPath)) {
            $this->publishes([
                $configPath => config_path("modules/currency"),
            ], "currency-config");
        }
    }

    protected function registerMigrations(): void
    {
        $migrationPath = $this->getModulePath('database/migrations');

        if (is_dir($migrationPath)) {
            $this->loadMigrationsFrom($migrationPath);
        }
    }

    protected function registerServices(): void
    {
        $this->app->bind(CurrencyService::class, CurrencyService::class);
    }

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

    protected function loadViews(): void
    {
        $viewsPath = $this->getModulePath('resources/views');

        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, strtolower($this->moduleName));
        }
    }

    protected function loadTranslations(): void
    {
        $langPath = $this->getModulePath('resources/lang');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, strtolower($this->moduleName));
        }
    }

    protected function getModulePath(string $path = ''): string
    {
        $basePath = base_path("src/Modules/Currency");

        return $path ? $basePath . '/' . $path : $basePath;
    }
}