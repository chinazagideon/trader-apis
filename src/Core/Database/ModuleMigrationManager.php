<?php

namespace App\Core\Database;

use App\Core\ModuleManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModuleMigrationManager
{
    protected ModuleManager $moduleManager;
    protected Migrator $migrator;
    protected MigrationRepositoryInterface $repository;

    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
        $this->migrator = app('migrator');
        $this->repository = app('migration.repository');
    }

    /**
     * Run migrations for a specific module
     */
    public function migrateModule(string $moduleName, array $options = []): array
    {
        $module = $this->moduleManager->getModule($moduleName);

        if (!$module) {
            throw new \Exception("Module '{$moduleName}' not found");
        }

        $migrationPath = $module['path'] . '/Database/Migrations';

        if (!is_dir($migrationPath)) {
            return [
                'status' => 'skipped',
                'message' => "No migrations found for module '{$moduleName}'",
            ];
        }

        // Set migration path
        $this->migrator->path($migrationPath);

        // Run migrations
        // $result = $this->migrator->run($options);
        $result = $this->migrator->run([$migrationPath], $options);

        // In rollbackModule(...)
        $result = $this->migrator->rollback([$migrationPath], ['step' => $options['step']]);

        return [
            'status' => 'completed',
            'module' => $moduleName,
            'migrations_run' => $result,
        ];
    }

    /**
     * Run migrations for all modules
     */
    public function migrateAllModules(array $options = []): array
    {
        $results = [];
        $modules = $this->moduleManager->getModules();

        foreach ($modules as $name => $module) {
            try {
                $results[$name] = $this->migrateModule($name, $options);
            } catch (\Exception $e) {
                $results[$name] = [
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Rollback migrations for a specific module
     */
    public function rollbackModule(string $moduleName, int $steps = 1): array
    {
        $module = $this->moduleManager->getModule($moduleName);

        if (!$module) {
            throw new \Exception("Module '{$moduleName}' not found");
        }

        $migrationPath = $module['path'] . '/Database/Migrations';

        if (!is_dir($migrationPath)) {
            return [
                'status' => 'skipped',
                'message' => "No migrations found for module '{$moduleName}'",
            ];
        }

        // Set migration path
        $this->migrator->path($migrationPath);

        // Rollback migrations
        $result = $this->migrator->rollback(['step' => $steps]);

        return [
            'status' => 'completed',
            'module' => $moduleName,
            'migrations_rolled_back' => $result,
        ];
    }

    /**
     * Get migration status for a module
     */
    public function getModuleMigrationStatus(string $moduleName): array
    {
        $module = $this->moduleManager->getModule($moduleName);

        if (!$module) {
            throw new \Exception("Module '{$moduleName}' not found");
        }

        $migrationPath = $module['path'] . '/Database/Migrations';

        if (!is_dir($migrationPath)) {
            return [
                'module' => $moduleName,
                'status' => 'no_migrations',
                'migrations' => [],
            ];
        }

        // Get migration files
        $migrationFiles = glob($migrationPath . '/*.php');
        $migrations = [];

        foreach ($migrationFiles as $file) {
            $filename = basename($file);
            $migrations[] = [
                'file' => $filename,
                'path' => $file,
                'is_migrated' => $this->isMigrationRun($filename),
            ];
        }

        return [
            'module' => $moduleName,
            'status' => 'has_migrations',
            'migrations' => $migrations,
        ];
    }

    /**
     * Check if a migration has been run
     */
    protected function isMigrationRun(string $migrationFile): bool
    {
        $migrationName = pathinfo($migrationFile, PATHINFO_FILENAME);

        // Get all ran migrations
        $ranMigrations = $this->repository->getRan();

        return in_array($migrationName, $ranMigrations);
    }

    /**
     * Create a new migration for a module
     */
    public function createModuleMigration(string $moduleName, string $migrationName): string
    {
        $module = $this->moduleManager->getModule($moduleName);

        if (!$module) {
            throw new \Exception("Module '{$moduleName}' not found");
        }

        $migrationPath = $module['path'] . '/Database/Migrations';

        if (!is_dir($migrationPath)) {
            mkdir($migrationPath, 0755, true);
        }

        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$migrationName}.php";
        $filepath = $migrationPath . '/' . $filename;

        // Create migration file
        $stub = $this->getMigrationStub();
        $content = str_replace(['{{migrationName}}', '{{className}}'], [
            $migrationName,
            \Illuminate\Support\Str::studly($migrationName),
        ], $stub);

        file_put_contents($filepath, $content);

        return $filepath;
    }

    /**
     * Get migration stub
     */
    protected function getMigrationStub(): string
    {
        return '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(\'{{migrationName}}\', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(\'{{migrationName}}\');
    }
};';
    }

    /**
     * Get database isolation strategy for a module
     */
    public function getModuleDatabaseStrategy(string $moduleName): array
    {
        $module = $this->moduleManager->getModule($moduleName);

        if (!$module) {
            throw new \Exception("Module '{$moduleName}' not found");
        }

        $config = $module['config'] ?? [];
        $databaseConfig = $config['database'] ?? [];

        return [
            'strategy' => $databaseConfig['isolation_strategy'] ?? 'shared',
            'connection' => $databaseConfig['connection'] ?? 'default',
            'prefix' => $databaseConfig['prefix'] ?? '',
            'schema' => $databaseConfig['schema'] ?? null,
        ];
    }
}
