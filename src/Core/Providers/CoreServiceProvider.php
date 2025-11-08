<?php

namespace App\Core\Providers;

use App\Core\Console\Commands\ModuleCreateCommand;
use App\Core\Console\Commands\ModuleListCommand;
use App\Core\Console\Commands\ModuleMakeMigration;
use App\Core\Console\Commands\ModuleMigrateCommand;
use App\Core\Console\Commands\ModuleMigrationStatusCommand;
use App\Core\Console\Commands\ModuleRollbackCommand;
use App\Core\Console\Commands\ModuleSeedCommand;
use App\Core\Console\Commands\ListModuleProvidersCommand;
use App\Core\Console\Commands\CacheModuleProvidersCommand;
use App\Core\Console\Commands\ProcessScheduledEventsCommand;
use App\Core\Console\Commands\CleanupScheduledEventsCommand;
use App\Core\Contracts\RepositoryInterface;
use App\Core\Database\ModuleMigrationManager;
use App\Core\Http\Controllers\ApiGatewayController;
use App\Core\Http\Middleware\ApiGatewayMiddleware;
use App\Core\ModuleManager;
use App\Core\Repositories\BaseRepository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Relations\Relation;
use Fruitcake\Cors\CorsService;
use App\Core\Services\SubModuleServiceRegistry;
use App\Core\Contracts\SubModuleServiceContract;
use App\Core\Services\ModuleMigrationRegistrar;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register core config file
        $this->mergeConfigFrom(
            base_path('src/Core/Config/core.php'),
            'core'
        );

        $this->app->singleton(CorsService::class, function ($app) {
            return new CorsService(config('cors'));
        });
        // Register core services immediately
        $this->app->singleton(ModuleManager::class);
        $this->app->singleton(ModuleMigrationManager::class);

        // Register repositories
        $this->app->bind(RepositoryInterface::class, BaseRepository::class);

        // Register console commands
        $this->registerConsoleCommands();

        // Register module migration registrar service
        $this->app->singleton(ModuleMigrationRegistrar::class, function ($app) {
            return new ModuleMigrationRegistrar(
                $app->make(ModuleManager::class)
            );
        });

        // Register module migrations via Migrator extension
        // This ensures paths are registered when Migrator is instantiated
        $this->app->extend('migrator', function ($migrator, $app) {
            $registrar = $app->make(ModuleMigrationRegistrar::class);
            $paths = $registrar->getMigrationPaths();

            foreach ($paths as $path) {
                $migrator->path($path);
            }

            return $migrator;
        });
    }


    /**
     * Register module service providers
     */
    private function registerModuleProviders(ModuleManager $moduleManager): void
    {
        // Only discover modules when actually needed
        if (!$moduleManager->isModulesDiscovered()) {
            $moduleManager->discoverModules();
        }

        $providers = $moduleManager->getServiceProviders();

        foreach ($providers as $provider) {
            if (class_exists($provider)) {
                $this->app->register($provider);
            }
        }
    }

    /**
     * Register console commands
     */
    private function registerConsoleCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ModuleListCommand::class,
                ModuleCreateCommand::class,
                ModuleMakeMigration::class,
                ModuleMigrateCommand::class,
                ModuleMigrationStatusCommand::class,
                ModuleRollbackCommand::class,
                ModuleSeedCommand::class,
                ListModuleProvidersCommand::class,
                CacheModuleProvidersCommand::class,
                ProcessScheduledEventsCommand::class,
                CleanupScheduledEventsCommand::class,
            ]);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

        // Register morph maps FIRST (before routes that might use them)
        $this->registerMorphMaps();

        // Load API Gateway routes directly
        $this->loadApiGatewayRoutes();

        // Register middleware
        $this->registerMiddleware();

        // Register sub module services
        $this->registerSubModuleServices();
    }

    /**
     * Load API Gateway routes
     */
    private function loadApiGatewayRoutes(): void
    {
        Route::prefix('api/gateway')->group(function () {
            Route::get('/status', [ApiGatewayController::class, 'status'])->name('gateway.status');
            Route::get('/health', [ApiGatewayController::class, 'health'])->name('gateway.health');
            Route::get('/modules', [ApiGatewayController::class, 'modulesInfo'])->name('gateway.modules');
            Route::get('/modules/{module}', [ApiGatewayController::class, 'moduleInfo'])->name('gateway.module.info');
            Route::post('/modules/{module}/register', [ApiGatewayController::class, 'registerModule'])->name('gateway.module.register');
        });
    }

    /**
     * Register middleware
     */
    private function registerMiddleware(): void
    {
        $this->app->singleton(ApiGatewayMiddleware::class);
    }


    /**
     * Register morph maps dynamically based on discovered modules
     * This allows polymorphic relationships to use clean aliases like 'user', 'transaction'
     * instead of full class names in the database
     */
    protected function registerMorphMaps(): void
    {
        // Define base morph map - models that are commonly used in polymorphic relationships
        $morphMap = config('core.morph_maps', []);

        // Register additional morph maps from module configs (for extensibility)
        $additionalMorphs = $this->discoverModuleMorphMaps();
        $morphMap = array_merge($morphMap, $additionalMorphs);

        // Use morphMap (not enforceMorphMap) to allow fallback to full class names if needed
        Relation::morphMap($morphMap);
    }

    /**
     * Discover additional morph maps from module configurations
     * Allows modules to register their own morphable models via config
     */
    protected function discoverModuleMorphMaps(): array
    {
        $morphs = [];

        // Get all module configs
        $moduleConfigs = config('modules', []);

        foreach ($moduleConfigs as $moduleName => $config) {
            // Check if module has morph_map configuration
            if (isset($config['morph_map']) && is_array($config['morph_map'])) {
                $morphs = array_merge($morphs, $config['morph_map']);
            }
        }

        return $morphs;
    }

    /**
     * Register sub module services
     */
    protected function registerSubModuleServices(): void
    {
        // Register registry as singleton (cached across requests in long-lived processes)
        $this->app->singleton(SubModuleServiceRegistry::class);

        // Auto-register on resolution
        $this->app->afterResolving(function ($object, $app) {
            if ($object instanceof SubModuleServiceContract) {
                $registry = $app->make(SubModuleServiceRegistry::class);
                $registry->register($object); // Stores class name only
            }
        });
    }
}
