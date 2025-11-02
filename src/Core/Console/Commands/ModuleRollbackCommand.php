<?php

namespace App\Core\Console\Commands;

use App\Core\Database\ModuleMigrationManager;
use App\Core\ModuleManager;
use Illuminate\Console\Command;

class ModuleRollbackCommand extends Command
{
    protected $signature = 'module:rollback
                            {module : The module to rollback}
                            {--step=1 : The number of migrations to be rolled back}
                            {--force : Force the operation when in production}';

    protected $description = 'Rollback migrations for a specific module';

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
        $step = (int) $this->option('step');
        $force = $this->option('force');

        // Discover modules first
        $this->moduleManager->discoverModules();

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

        // Validate step count
        if ($step < 1) {
            $this->error("✗ Step count must be at least 1!");
            return 1;
        }

        $this->info("Rolling back {$step} migration(s) for module: {$module}");

        if ($force) {
            $this->warn("⚠ Force mode enabled - this will run in production!");
        }

        try {
            $result = $this->migrationManager->rollbackModule($module, $step);

            if ($result['status'] === 'completed') {
                $this->info("✓ {$module}: Rollback completed successfully");
                if (isset($result['migrations_rolled_back']) && is_array($result['migrations_rolled_back'])) {
                    $this->line("  Migrations rolled back: " . count($result['migrations_rolled_back']));
                }
                return 0;
            } elseif ($result['status'] === 'skipped') {
                $this->warn("⚠ {$module}: {$result['message']}");
                return 0;
            } else {
                $this->error("✗ {$module}: Rollback failed");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("✗ Failed to rollback module '{$module}': " . $e->getMessage());
            return 1;
        }
    }
}
