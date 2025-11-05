<?php

namespace App\Core\Providers;

use App\Core\Contracts\ModuleServiceProviderInterface;
use App\Core\ModuleManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

/**
 * Base class for module service providers
 *
 * Provides common functionality and structure for all module service providers.
 * Modules should extend this class instead of Laravel's ServiceProvider directly.
 */
abstract class ModuleServiceProviderBase extends ServiceProvider implements ModuleServiceProviderInterface
{
    /**
     * The module name (e.g., 'Transaction', 'User', 'Investment')
     */
    protected string $moduleName;

    /**
     * The module namespace (e.g., 'App\\Modules\\Transaction')
     */
    protected string $moduleNamespace;

    /**
     * The module path (e.g., '/path/to/src/Modules/Transaction')
     */
    protected string $modulePath;

    /**
     * Constructor
     */
    public function __construct($app)
    {
        parent::__construct($app);

        // Auto-detect module name from class name
        $this->moduleName = $this->detectModuleName();
        $this->moduleNamespace = "App\\Modules\\{$this->moduleName}";
        $this->modulePath = base_path("src/Modules/{$this->moduleName}");
    }

    /**
     * Get the module name this provider belongs to
     */
    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    /**
     * Get the module namespace
     */
    public function getModuleNamespace(): string
    {
        return $this->moduleNamespace;
    }

    /**
     * Get the module path
     */
    public function getModulePath(): string
    {
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

    /**
     * Auto-detect module name from class name
     *
     * Examples:
     * - TransactionServiceProvider -> Transaction
     * - TransactionEventServiceProvider -> Transaction
     * - UserServiceProvider -> User
     */
    protected function detectModuleName(): string
    {
        $className = class_basename(static::class);

        $moduleName = Str::replaceLast('', '', $className);

        // Handle special cases like EventServiceProvider, AuthServiceProvider, etc.
        $specialSuffixes = ['Event', 'Auth', 'Config', 'Route'];
        foreach ($specialSuffixes as $suffix) {
            if (Str::endsWith($moduleName, $suffix)) {
                return Str::replaceLast($suffix, '', $moduleName);
            }
        }

        return $moduleName;
    }

    /**
     * Get a path within the module
     */
    protected function getModuleFilePath(string $path = ''): string
    {
        return $path ? $this->modulePath . '/' . $path : $this->modulePath;
    }

    /**
     * Check if a file exists within the module
     */
    protected function moduleFileExists(string $path): bool
    {
        return file_exists($this->getModuleFilePath($path));
    }

    /**
     * Check if a directory exists within the module
     */
    protected function moduleDirExists(string $path): bool
    {
        return is_dir($this->getModuleFilePath($path));
    }

    /**
     * Get module configuration
     */
    protected function getModuleConfig(): array
    {
        return ModuleManager::loadModuleConfigFile($this->modulePath);
    }

    /**
     * Register module configuration
     */
    protected function registerModuleConfig(): void
    {
        $config = $this->getModuleConfig();

        if (!empty($config)) {
            $this->app['config']->set($this->moduleName, $config);
            $this->app['config']->set("modules.{$this->moduleName}", $config);
        }
    }

    /**
     * Register module migrations
     */
    protected function registerModuleMigrations(): void
    {
        $migrationPath = $this->getModuleFilePath('database/migrations');

        if ($this->moduleDirExists('database/migrations')) {
            $this->loadMigrationsFrom($migrationPath);
        }
    }

    /**
     * Register module views
     */
    protected function registerModuleViews(): void
    {
        if ($this->moduleDirExists('resources/views')) {
            $viewsPath = $this->getModuleFilePath('resources/views');
            $this->loadViewsFrom($viewsPath, Str::lower($this->moduleName));
        }
    }

    /**
     * Register module translations
     */
    protected function registerModuleTranslations(): void
    {
        if ($this->moduleDirExists('resources/lang')) {
            $langPath = $this->getModuleFilePath('resources/lang');
            $this->loadTranslationsFrom($langPath, Str::lower($this->moduleName));
        }
    }

    /**
     * Load module routes
     */
    protected function loadModuleRoutes(): void
    {
        if ($this->moduleFileExists('routes/api.php')) {
            \Illuminate\Support\Facades\Route::prefix('api/v1')
                ->middleware(['api'])
                ->group($this->getModuleFilePath('routes/api.php'));
        }

        if ($this->moduleFileExists('routes/web.php')) {
            \Illuminate\Support\Facades\Route::middleware(['web'])
                ->group($this->getModuleFilePath('routes/web.php'));
        }
    }
}
