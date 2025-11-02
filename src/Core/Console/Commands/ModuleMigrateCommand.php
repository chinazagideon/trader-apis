<?php

namespace App\Core\Console\Commands;

use App\Core\Database\ModuleMigrationManager;
use App\Core\ModuleManager;
use Illuminate\Console\Command;

class ModuleMigrateCommand extends Command
{
    protected $signature = 'module:migrate
                            {module? : The module to migrate (optional)}
                            {--force : Force the operation when in production}
                            {--step= : The number of migrations to be reverted & re-run}';

    protected $description = 'Run migrations for modules';

    protected ModuleManager $moduleManager;
    protected ModuleMigrationManager $migrationManager;

    public function __construct(ModuleManager $moduleManager, ModuleMigrationManager $migrationManager)
    {
        parent::__construct();
        $this->moduleManager = $moduleManager;
        $this->migrationManager = $migrationManager;
    }

    public function handle(): int
    {
        $module = $this->argument('module');
        $force = $this->option('force');
        $step = $this->option('step');

        // Discover modules first
        $this->moduleManager->discoverModules();

        $options = [
            'force' => $force,
        ];

        if ($step) {
            $options['step'] = (int) $step;
        }

        if ($module) {
            return $this->migrateSingleModule($module, $options);
        } else {
            return $this->migrateAllModules($options);
        }
    }

    protected function migrateSingleModule(string $module, array $options): int
    {
        // Validate module exists
        if (!$this->moduleManager->getModule($module)) {
            $this->error("✗ Module '{$module}' not found!");
            $this->line('');
            $this->info('Available modules:');
            foreach ($this->moduleManager->getModules() as $moduleName => $moduleData) {
                $this->line("  - {$moduleName}");
            }
            return 1;
        }

        $this->info("Migrating module: {$module}");

        try {
            $result = $this->migrationManager->migrateModule($module, $options);

            if ($result['status'] === 'completed') {
                $this->info("✓ {$module}: Migration completed successfully");
                if (isset($result['migrations_run']) && is_array($result['migrations_run'])) {
                    $this->line("  Migrations run: " . count($result['migrations_run']));
                }
                return 0;
            } elseif ($result['status'] === 'skipped') {
                $this->warn("⚠ {$module}: {$result['message']}");
                return 0;
            } else {
                $this->error("✗ {$module}: Migration failed");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("✗ Failed to migrate module '{$module}': " . $e->getMessage());
            return 1;
        }
    }

    protected function migrateAllModules(array $options): int
    {
        $this->info('Migrating all modules...');

        try {
            $results = $this->migrationManager->migrateAllModules($options);

            $successCount = 0;
            $failureCount = 0;
            $skippedCount = 0;

            foreach ($results as $module => $result) {
                if ($result['status'] === 'completed') {
                    $this->info("✓ {$module}: Migration completed");
                    $successCount++;
                } elseif ($result['status'] === 'skipped') {
                    $this->warn("⚠ {$module}: {$result['message']}");
                    $skippedCount++;
                } else {
                    $this->error("✗ {$module}: " . ($result['error'] ?? $result['message'] ?? 'Unknown error'));
                    $failureCount++;
                }
            }

            $this->line('');
            $this->info("Migration Summary:");
            $this->line("✓ Successful: {$successCount}");
            $this->line("⚠ Skipped: {$skippedCount}");
            $this->line("✗ Failed: {$failureCount}");

            return $failureCount > 0 ? 1 : 0;
        } catch (\Exception $e) {
            $this->error("✗ Failed to migrate modules: " . $e->getMessage());
            return 1;
        }
    }
}
