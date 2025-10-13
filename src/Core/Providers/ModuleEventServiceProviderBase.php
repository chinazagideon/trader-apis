<?php

namespace App\Core\Providers;

use App\Core\Contracts\ModuleServiceProviderInterface;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as BaseEventServiceProvider;
use Illuminate\Support\Str;

/**
 * Base class for module event service providers
 *
 * Provides common functionality for event service providers within modules.
 * Modules should extend this class for event-related service providers.
 */
abstract class ModuleEventServiceProviderBase extends BaseEventServiceProvider implements ModuleServiceProviderInterface
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
     * - TransactionEventServiceProvider -> Transaction
     * - UserEventServiceProvider -> User
     * - InvestmentEventServiceProvider -> Investment
     */
    protected function detectModuleName(): string
    {
        $className = class_basename(static::class);

        // Remove 'EventServiceProvider' suffix
        $moduleName = Str::replaceLast('EventServiceProvider', '', $className);

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
        $configFile = $this->getModuleFilePath("config/" . Str::lower($this->moduleName) . ".php");

        if (file_exists($configFile)) {
            return require $configFile;
        }

        return [];
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
}
