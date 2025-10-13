<?php

namespace App\Core;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleManager
{
    protected Collection $modules;
    protected string $modulesPath;
    protected array $registeredModules = [];
    protected bool $modulesDiscovered = false;

    public function __construct()
    {
        $this->modulesPath = base_path('src/Modules');
        $this->modules = collect();
    }

    /**
     * Discover all available modules
     */
    public function discoverModules(): void
    {
        if ($this->modulesDiscovered) {
            return;
        }

        if (!File::exists($this->modulesPath)) {
            $this->modulesDiscovered = true;
            return;
        }

        $directories = File::directories($this->modulesPath);

        foreach ($directories as $directory) {
            $moduleName = basename($directory);
            $modulePath = $directory;

            if ($this->isValidModule($modulePath)) {
                $this->modules->put($moduleName, [
                    'name' => $moduleName,
                    'path' => $modulePath,
                    'namespace' => "App\\Modules\\{$moduleName}",
                    'provider' => "App\\Modules\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider",
                    'config' => $this->getModuleConfig($modulePath),
                ]);
            }
        }

        $this->modulesDiscovered = true;
    }

    /**
     * Check if a directory is a valid module
     */
    protected function isValidModule(string $path): bool
    {
        $requiredFiles = [
            'Providers',
            'Services',
            'Http/Controllers',
        ];

        foreach ($requiredFiles as $file) {
            if (!File::exists($path . '/' . $file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get module configuration
     */
    protected function getModuleConfig(string $modulePath): array
    {
        $configFile = $modulePath . '/config/' . strtolower(basename($modulePath)) . '.php';

        if (File::exists($configFile)) {
            return require $configFile;
        }

        return [];
    }

    /**
     * Check if modules have been discovered
     */
    public function isModulesDiscovered(): bool
    {
        return $this->modulesDiscovered;
    }

    /**
     * Get all discovered modules
     */
    public function getModules(): Collection
    {
        return $this->modules;
    }

    /**
     * Get a specific module
     */
    public function getModule(string $name): ?array
    {
        return $this->modules->get($name);
    }

    /**
     * Get module service providers
     */
    public function getServiceProviders(): array
    {
        $providers = [];

        foreach ($this->modules as $module) {
            // Get main service provider
            if (class_exists($module['provider'])) {
                $providers[] = $module['provider'];
            }

            // Discover all service providers in the module
            $moduleProviders = $this->discoverModuleProviders($module['path'], $module['namespace']);
            $providers = array_merge($providers, $moduleProviders);
        }

        return array_unique($providers);
    }

    /**
     * Discover all service providers within a module
     */
    protected function discoverModuleProviders(string $modulePath, string $moduleNamespace): array
    {
        $providers = [];
        $providersPath = $modulePath . '/Providers';

        if (!File::exists($providersPath)) {
            return $providers;
        }

        $files = File::files($providersPath);

        foreach ($files as $file) {
            $filename = $file->getFilenameWithoutExtension();
            $providerClass = $moduleNamespace . '\\Providers\\' . $filename;

            // Only include classes that extend ServiceProvider
            if (class_exists($providerClass)) {
                $reflection = new \ReflectionClass($providerClass);
                if ($reflection->isSubclassOf(\Illuminate\Support\ServiceProvider::class)) {
                    // Check if provider should be registered
                    $shouldRegister = true;
                    if ($reflection->implementsInterface(\App\Core\Contracts\ModuleServiceProviderInterface::class)) {
                        $instance = $reflection->newInstanceWithoutConstructor();
                        $shouldRegister = $instance->shouldRegister();
                    }

                    if ($shouldRegister) {
                        $providers[] = [
                            'class' => $providerClass,
                            'priority' => $this->getProviderPriority($providerClass),
                        ];
                    }
                }
            }
        }

        // Sort by priority (higher priority first)
        usort($providers, function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });

        // Return just the class names
        return array_column($providers, 'class');
    }

    /**
     * Get provider priority
     */
    protected function getProviderPriority(string $providerClass): int
    {
        if (class_exists($providerClass)) {
            $reflection = new \ReflectionClass($providerClass);
            if ($reflection->implementsInterface(\App\Core\Contracts\ModuleServiceProviderInterface::class)) {
                $instance = $reflection->newInstanceWithoutConstructor();
                return $instance->getPriority();
            }
        }

        return 0;
    }

    /**
     * Get service providers for a specific module
     */
    public function getModuleServiceProviders(string $moduleName): array
    {
        $module = $this->getModule($moduleName);

        if (!$module) {
            return [];
        }

        $providers = [];

        // Get main service provider
        if (class_exists($module['provider'])) {
            $providers[] = $module['provider'];
        }

        // Discover all service providers in the module
        $moduleProviders = $this->discoverModuleProviders($module['path'], $module['namespace']);
        $providers = array_merge($providers, $moduleProviders);

        return array_unique($providers);
    }

    /**
     * Get all service providers organized by module
     */
    public function getServiceProvidersByModule(): array
    {
        $providersByModule = [];

        foreach ($this->modules as $moduleName => $module) {
            $providersByModule[$moduleName] = $this->getModuleServiceProviders($moduleName);
        }

        return $providersByModule;
    }

    /**
     * Get module routes
     */
    public function getModuleRoutes(): array
    {
        $routes = [];

        foreach ($this->modules as $module) {
            $moduleRoutes = $this->getModuleRouteFiles($module['path']);
            $routes[$module['name']] = $moduleRoutes;
        }

        return $routes;
    }

    /**
     * Get module route files
     */
    protected function getModuleRouteFiles(string $modulePath): array
    {
        $routesPath = $modulePath . '/routes';
        $routes = [];

        if (File::exists($routesPath)) {
            $files = File::files($routesPath);

            foreach ($files as $file) {
                $type = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $routes[$type] = $file->getPathname();
            }
        }

        return $routes;
    }

    /**
     * Get module migrations
     */
    public function getModuleMigrations(): array
    {
        $migrations = [];

        foreach ($this->modules as $module) {
            $migrationPath = $module['path'] . '/Database/Migrations';

            if (File::exists($migrationPath)) {
                $files = File::files($migrationPath);

                foreach ($files as $file) {
                    $migrations[] = $file->getPathname();
                }
            }
        }

        return $migrations;
    }

    /**
     * Register a module
     */
    public function registerModule(string $name): bool
    {
        $module = $this->getModule($name);

        if (!$module) {
            return false;
        }

        $this->registeredModules[] = $name;
        return true;
    }

    /**
     * Check if a module is registered
     */
    public function isModuleRegistered(string $name): bool
    {
        return in_array($name, $this->registeredModules);
    }

    /**
     * Get registered modules
     */
    public function getRegisteredModules(): array
    {
        return $this->registeredModules;
    }

    /**
     * Get module health status
     */
    public function getModuleHealth(string $name): array
    {
        $module = $this->getModule($name);

        if (!$module) {
            return [
                'status' => 'not_found',
                'message' => 'Module not found',
            ];
        }

        $health = [
            'status' => 'healthy',
            'checks' => [],
        ];

        // Check if provider exists
        $providerExists = class_exists($module['provider']);
        $health['checks']['provider'] = $providerExists ? 'ok' : 'missing';

        // Check if routes exist
        $routesExist = File::exists($module['path'] . '/routes');
        $health['checks']['routes'] = $routesExist ? 'ok' : 'missing';

        // Check if services exist
        $servicesExist = File::exists($module['path'] . '/Services');
        $health['checks']['services'] = $servicesExist ? 'ok' : 'missing';

        // Check if controllers exist
        $controllersExist = File::exists($module['path'] . '/Http/Controllers');
        $health['checks']['controllers'] = $controllersExist ? 'ok' : 'missing';

        // Determine overall status
        $allChecksPass = collect($health['checks'])->every(fn($status) => $status === 'ok');
        $health['status'] = $allChecksPass ? 'healthy' : 'unhealthy';

        return $health;
    }

    /**
     * Get all modules health status
     */
    public function getAllModulesHealth(): array
    {
        $health = [];

        foreach ($this->modules as $name => $module) {
            $health[$name] = $this->getModuleHealth($name);
        }

        return $health;
    }
}
