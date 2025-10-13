<?php

namespace App\Modules\User\Providers;

use App\Modules\User\Contracts\UserServiceInterface;
use App\Modules\User\Database\Models\User;
use App\Modules\User\Repositories\UserRepository;
use App\Modules\User\Services\UserService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class UserServiceProvider extends ServiceProvider
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
     * Register services.
     */
    public function register(): void
    {
        $this->registerConfig();
        $this->registerMigrations();
        $this->registerRepositories();
        $this->registerServices();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadViews();
        $this->loadTranslations();
        $this->publishAssets();
    }

    /**
     * Register module configuration
     */
    protected function registerConfig(): void
    {
        $configPath = $this->getModulePath('config');

        if (is_dir($configPath)) {
            $this->publishes([
                $configPath => config_path("modules/{$this->moduleName}"),
            ], "{$this->moduleName}-config");
        }
    }

    /**
     * Register module migrations
     */
    protected function registerMigrations(): void
    {
        $migrationPath = $this->getModulePath('database/migrations');

        if (is_dir($migrationPath)) {
            $this->loadMigrationsFrom($migrationPath);
        }
    }

    /**
     * Register repositories
     */
    protected function registerRepositories(): void
    {
        $this->app->bind(UserRepository::class, function ($app) {
            return new UserRepository($app->make(User::class));
        });
    }

    /**
     * Register services
     */
    protected function registerServices(): void
    {
        $this->app->bind(UserServiceInterface::class, UserService::class);
    }

    /**
     * Load module routes
     */
    protected function loadRoutes(): void
    {
        $routesPath = $this->getModulePath('routes');

        if (is_dir($routesPath)) {
            // Load API routes
            if (file_exists($routesPath . '/api.php')) {
                Route::prefix('api/v1')
                    ->middleware(['api'])
                    ->group($routesPath . '/api.php');
            }

            // Load web routes
            if (file_exists($routesPath . '/web.php')) {
                Route::middleware(['web'])
                    ->group($routesPath . '/web.php');
            }
        }
    }

    /**
     * Load module views
     */
    protected function loadViews(): void
    {
        $viewsPath = $this->getModulePath('resources/views');

        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, strtolower($this->moduleName));
        }
    }

    /**
     * Load module translations
     */
    protected function loadTranslations(): void
    {
        $langPath = $this->getModulePath('resources/lang');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, strtolower($this->moduleName));
        }
    }

    /**
     * Publish module assets
     */
    protected function publishAssets(): void
    {
        $assetsPath = $this->getModulePath('resources/assets');

        if (is_dir($assetsPath)) {
            $this->publishes([
                $assetsPath => public_path("modules/{$this->moduleName}"),
            ], "{$this->moduleName}-assets");
        }
    }

    /**
     * Get module path
     */
    protected function getModulePath(string $path = ''): string
    {
        $basePath = base_path("src/Modules/{$this->moduleName}");

        return $path ? $basePath . '/' . $path : $basePath;
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
