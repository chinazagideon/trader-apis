<?php

namespace App\Core\Console\Commands;

use App\Core\ModuleManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ModuleCreateCommand extends Command
{
    protected $signature = 'module:create
                            {name : The name of the module}
                            {--description= : Module description}
                            {--author= : Module author}';

    protected $description = 'Create a new module';

    protected ModuleManager $moduleManager;

    public function __construct(ModuleManager $moduleManager)
    {
        parent::__construct();
        $this->moduleManager = $moduleManager;
    }

    public function handle(): int
    {
        $name = $this->argument('name');
        $description = $this->option('description') ?: "{$name} module";
        $author = $this->option('author') ?: 'Your Company';

        $this->info("Creating module: {$name}");

        try {
            $this->createModuleStructure($name, $description, $author);
            $this->info("✓ Module '{$name}' created successfully!");
            $this->line("Run 'php artisan module:list' to see the new module.");

            return 0;
        } catch (\Exception $e) {
            $this->error("✗ Failed to create module: " . $e->getMessage());
            return 1;
        }
    }

    protected function createModuleStructure(string $name, string $description, string $author): void
    {
        $modulePath = base_path("src/Modules/{$name}");

        if (File::exists($modulePath)) {
            throw new \Exception("Module '{$name}' already exists");
        }

        // Create directory structure
        $directories = [
            'Contracts',
            'Database/Migrations',
            'Database/Seeders',
            'Database/Models',
            'Http/Controllers',
            'Http/Requests',
            'Http/Resources',
            'Http/Middleware',
            'Providers',
            'Repositories',
            'Services',
            'routes',
            'config',
            'resources/views',
            'resources/lang',
            'resources/assets',
        ];

        foreach ($directories as $directory) {
            File::makeDirectory($modulePath . '/' . $directory, 0755, true);
        }

        // Create service provider
        $this->createServiceProvider($name, $description, $author);

        // Create routes
        $this->createRoutes($name);

        // Create config
        $this->createConfig($name, $description, $author);

        // Create sample controller
        $this->createSampleController($name);

        // Create sample service
        $this->createSampleService($name);

        // Create sample model
        $this->createSampleModel($name);
    }

    protected function createServiceProvider(string $name, string $description, string $author): void
    {
        $stub = $this->getServiceProviderStub();
        $content = str_replace([
            '{{ModuleName}}',
            '{{moduleName}}',
            '{{description}}',
            '{{author}}',
        ], [
            $name,
            strtolower($name),
            $description,
            $author,
        ], $stub);

        file_put_contents(
            base_path("src/Modules/{$name}/Providers/{$name}ServiceProvider.php"),
            $content
        );
    }

    protected function createRoutes(string $name): void
    {
        $apiStub = $this->getApiRoutesStub();
        $apiContent = str_replace('{{ModuleName}}', $name, $apiStub);

        file_put_contents(
            base_path("src/Modules/{$name}/routes/api.php"),
            $apiContent
        );

        $webStub = $this->getWebRoutesStub();
        $webContent = str_replace('{{ModuleName}}', $name, $webStub);

        file_put_contents(
            base_path("src/Modules/{$name}/routes/web.php"),
            $webContent
        );
    }

    protected function createConfig(string $name, string $description, string $author): void
    {
        $stub = $this->getConfigStub();
        $content = str_replace([
            '{{ModuleName}}',
            '{{moduleName}}',
            '{{description}}',
            '{{author}}',
        ], [
            $name,
            strtolower($name),
            $description,
            $author,
        ], $stub);

        file_put_contents(
            base_path("src/Modules/{$name}/config/{strtolower($name)}.php"),
            $content
        );
    }

    protected function createSampleController(string $name): void
    {
        $stub = $this->getControllerStub();
        $content = str_replace([
            '{{ModuleName}}',
            '{{moduleName}}',
        ], [
            $name,
            strtolower($name),
        ], $stub);

        file_put_contents(
            base_path("src/Modules/{$name}/Http/Controllers/{$name}Controller.php"),
            $content
        );
    }

    protected function createSampleService(string $name): void
    {
        $stub = $this->getServiceStub();
        $content = str_replace([
            '{{ModuleName}}',
            '{{moduleName}}',
        ], [
            $name,
            strtolower($name),
        ], $stub);

        file_put_contents(
            base_path("src/Modules/{$name}/Services/{$name}Service.php"),
            $content
        );
    }

    protected function createSampleModel(string $name): void
    {
        $stub = $this->getModelStub();
        $content = str_replace([
            '{{ModuleName}}',
            '{{moduleName}}',
        ], [
            $name,
            strtolower($name),
        ], $stub);

        file_put_contents(
            base_path("src/Modules/{$name}/Database/Models/{$name}.php"),
            $content
        );
    }

    protected function getServiceProviderStub(): string
    {
        return '<?php

namespace App\Modules\{{ModuleName}}\Providers;

use App\Modules\{{ModuleName}}\Services\{{ModuleName}}Service;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class {{ModuleName}}ServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = \'App\\\\Modules\\\\{{ModuleName}}\';
    protected string $moduleName = \'{{ModuleName}}\';

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
        $configPath = $this->getModulePath(\'config\');

        if (is_dir($configPath)) {
            $this->publishes([
                $configPath => config_path("modules/{{moduleName}}"),
            ], "{{moduleName}}-config");
        }
    }

    protected function registerMigrations(): void
    {
        $migrationPath = $this->getModulePath(\'database/migrations\');

        if (is_dir($migrationPath)) {
            $this->loadMigrationsFrom($migrationPath);
        }
    }

    protected function registerServices(): void
    {
        $this->app->bind({{ModuleName}}Service::class, {{ModuleName}}Service::class);
    }

    protected function loadRoutes(): void
    {
        $routesPath = $this->getModulePath(\'routes\');

        if (is_dir($routesPath)) {
            if (file_exists($routesPath . \'/api.php\')) {
                Route::prefix(\'api/v1\')
                    ->middleware([\'api\'])
                    ->group($routesPath . \'/api.php\');
            }

            if (file_exists($routesPath . \'/web.php\')) {
                Route::middleware([\'web\'])
                    ->group($routesPath . \'/web.php\');
            }
        }
    }

    protected function loadViews(): void
    {
        $viewsPath = $this->getModulePath(\'resources/views\');

        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, strtolower($this->moduleName));
        }
    }

    protected function loadTranslations(): void
    {
        $langPath = $this->getModulePath(\'resources/lang\');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, strtolower($this->moduleName));
        }
    }

    protected function getModulePath(string $path = \'\'): string
    {
        $basePath = base_path("src/Modules/{{ModuleName}}");

        return $path ? $basePath . \'/\' . $path : $basePath;
    }
}';
    }

    protected function getApiRoutesStub(): string
    {
        return '<?php

use App\Modules\{{ModuleName}}\Http\Controllers\{{ModuleName}}Controller;
use Illuminate\Support\Facades\Route;

Route::prefix(\'{{moduleName}}\')->name(\'{{moduleName}}.\')->group(function () {
    Route::get(\'/health\', [{{ModuleName}}Controller::class, \'health\'])->name(\'health\');

    Route::middleware([\'auth:sanctum\'])->group(function () {
        Route::get(\'/\', [{{ModuleName}}Controller::class, \'index\'])->name(\'index\');
        Route::post(\'/\', [{{ModuleName}}Controller::class, \'store\'])->name(\'store\');
        Route::get(\'/{id}\', [{{ModuleName}}Controller::class, \'show\'])->name(\'show\');
        Route::put(\'/{id}\', [{{ModuleName}}Controller::class, \'update\'])->name(\'update\');
        Route::delete(\'/{id}\', [{{ModuleName}}Controller::class, \'destroy\'])->name(\'destroy\');
    });
});';
    }

    protected function getWebRoutesStub(): string
    {
        return '<?php

use App\Modules\{{ModuleName}}\Http\Controllers\{{ModuleName}}Controller;
use Illuminate\Support\Facades\Route;

Route::prefix(\'{{moduleName}}\')->name(\'{{moduleName}}.\')->group(function () {
    Route::get(\'/\', [{{ModuleName}}Controller::class, \'hello\'])->name(\'hello\');
});';
    }

    protected function getConfigStub(): string
    {
        return '<?php

return [
    \'module\' => [
        \'name\' => \'{{ModuleName}}\',
        \'version\' => \'1.0.0\',
        \'description\' => \'{{description}}\',
        \'author\' => \'{{author}}\',
    ],

    \'database\' => [
        \'connection\' => env(\'{{MODULE_NAME}}_DB_CONNECTION\', \'default\'),
        \'prefix\' => env(\'{{MODULE_NAME}}_DB_PREFIX\', \'\'),
    ],

    \'api\' => [
        \'version\' => \'v1\',
        \'prefix\' => \'{{moduleName}}\',
        \'middleware\' => [\'api\', \'auth:sanctum\'],
    ],
];';
    }

    protected function getControllerStub(): string
    {
        return '<?php

namespace App\Modules\{{ModuleName}}\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\{{ModuleName}}\Services\{{ModuleName}}Service;
use App\Core\Http\ServiceResponse;
use Illuminate\Http\JsonResponse;

class {{ModuleName}}Controller extends CrudController
{
    public function __construct(
        private {{ModuleName}}Service ${{moduleName}}Service
    ) {
        parent::__construct(${{moduleName}}Service);
    }

    public function hello(): JsonResponse
    {
        return $this->successResponse([], \'Hello from {{ModuleName}} module\');
    }
}';
    }

    protected function getServiceStub(): string
    {
        return '<?php

namespace App\Modules\{{ModuleName}}\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\{{ModuleName}}\Repositories\{{ModuleName}}Repository;

class {{ModuleName}}Service extends BaseService
{
    protected string $serviceName = \'{{ModuleName}}Service\';

    public function __construct(
        private {{ModuleName}}Repository ${{ModuleName}}Repository
    )
    {
        parent::__construct(${{ModuleName}}Repository);
    }

}';
    }

    protected function getModelStub(): string
    {
        return '<?php

namespace App\Modules\{{ModuleName}}\Database\Models;

use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class {{ModuleName}} extends Model
{
    use HasTimestamps, HasUuid;

    protected $fillable = [
        \'name\',
        \'description\',
    ];

    protected function casts(): array
    {
        return [
            \'created_at\' => \'datetime\',
            \'updated_at\' => \'datetime\',
        ];
    }
}';
    }
}
