<?php

namespace App\Core\Console\Commands;

use App\Core\Database\ModuleMigrationManager;
use App\Core\ModuleManager;
use Illuminate\Console\Command;

class ModuleMigrationStatusCommand extends Command
{
    protected $signature = 'module:migration-status
                            {module? : The module to check status for (optional)}
                            {--format=table : Output format (table, json)}';

    protected $description = 'Show migration status for modules';

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
        $format = $this->option('format');

        // Discover modules first
        $this->moduleManager->discoverModules();

        if ($module) {
            return $this->showSingleModuleStatus($module, $format);
        } else {
            return $this->showAllModulesStatus($format);
        }
    }

    protected function showSingleModuleStatus(string $module, string $format): int
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

        try {
            $status = $this->migrationManager->getModuleMigrationStatus($module);

            if ($format === 'json') {
                $this->line(json_encode($status, JSON_PRETTY_PRINT));
                return 0;
            }

            $this->info("Migration Status for Module: {$module}");
            $this->line('');

            if ($status['status'] === 'no_migrations') {
                $this->warn("⚠ No migrations found for this module");
                return 0;
            }

            $this->table(
                ['Migration File', 'Status'],
                collect($status['migrations'])->map(function ($migration) {
                    return [
                        $migration['file'],
                        $migration['is_migrated'] ? '✓ Migrated' : '✗ Pending'
                    ];
                })->toArray()
            );

            $migratedCount = collect($status['migrations'])->where('is_migrated', true)->count();
            $totalCount = count($status['migrations']);

            $this->line('');
            $this->info("Summary: {$migratedCount}/{$totalCount} migrations completed");

            return 0;
        } catch (\Exception $e) {
            $this->error("✗ Failed to get migration status: " . $e->getMessage());
            return 1;
        }
    }

    protected function showAllModulesStatus(string $format): int
    {
        try {
            $allStatus = [];
            $modules = $this->moduleManager->getModules();

            foreach ($modules as $moduleName => $moduleData) {
                try {
                    $status = $this->migrationManager->getModuleMigrationStatus($moduleName);
                    $allStatus[$moduleName] = $status;
                } catch (\Exception $e) {
                    $allStatus[$moduleName] = [
                        'module' => $moduleName,
                        'status' => 'error',
                        'error' => $e->getMessage(),
                        'migrations' => []
                    ];
                }
            }

            if ($format === 'json') {
                $this->line(json_encode($allStatus, JSON_PRETTY_PRINT));
                return 0;
            }

            $this->info("Migration Status for All Modules");
            $this->line('');

            $tableData = [];
            foreach ($allStatus as $moduleName => $status) {
                if ($status['status'] === 'no_migrations') {
                    $tableData[] = [
                        $moduleName,
                        'No migrations',
                        '0/0',
                        'N/A'
                    ];
                } elseif ($status['status'] === 'error') {
                    $tableData[] = [
                        $moduleName,
                        'Error',
                        '0/0',
                        $status['error']
                    ];
                } else {
                    $migratedCount = collect($status['migrations'])->where('is_migrated', true)->count();
                    $totalCount = count($status['migrations']);
                    $tableData[] = [
                        $moduleName,
                        $migratedCount === $totalCount ? '✓ Complete' : '⚠ Pending',
                        "{$migratedCount}/{$totalCount}",
                        $migratedCount === $totalCount ? 'Up to date' : 'Needs migration'
                    ];
                }
            }

            $this->table(
                ['Module', 'Status', 'Migrations', 'Action'],
                $tableData
            );

            $completeCount = collect($allStatus)->where('status', 'has_migrations')->filter(function ($status) {
                $migratedCount = collect($status['migrations'])->where('is_migrated', true)->count();
                $totalCount = count($status['migrations']);
                return $migratedCount === $totalCount;
            })->count();

            $totalModules = count($allStatus);
            $this->line('');
            $this->info("Summary: {$completeCount}/{$totalModules} modules up to date");

            return 0;
        } catch (\Exception $e) {
            $this->error("✗ Failed to get migration status: " . $e->getMessage());
            return 1;
        }
    }
}
