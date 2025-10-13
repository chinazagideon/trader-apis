<?php

namespace App\Core\Console\Commands;

use App\Core\Database\ModuleMigrationManager;
use App\Core\ModuleManager;
use Illuminate\Console\Command;

class ModuleMigrateCommand extends Command
{
    protected $signature = 'module:migrate
                            {module? : The module to migrate}
                            {--all : Migrate all modules}
                            {--force : Force the operation to run in production}';

    protected $description = 'Run migrations for modules';

    protected ModuleManager $moduleManager;
    protected ModuleMigrationManager $migrationManager;

    public function __construct(ModuleManager $moduleManager)
    {
        parent::__construct();
        $this->moduleManager = $moduleManager;
        $this->migrationManager = app(ModuleMigrationManager::class);
    }

    public function handle(): int
    {
        if ($this->option('all')) {
            return $this->migrateAllModules();
        }

        $module = $this->argument('module');

        if (!$module) {
            $this->error('Please specify a module name or use --all option');
            return 1;
        }

        return $this->migrateModule($module);
    }

    protected function migrateModule(string $module): int
    {
        $this->info("Migrating module: {$module}");

        try {
            $result = $this->migrationManager->migrateModule($module, [
                'force' => $this->option('force'),
            ]);

            $message = $result['message'] ?? 'Migration completed';
            $this->info("✓ {$result['status']}: {$message}");

            if (isset($result['migrations_run'])) {
                $this->line("Migrations run: " . implode(', ', $result['migrations_run']));
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("✗ Failed to migrate module '{$module}': " . $e->getMessage());
            return 1;
        }
    }

    protected function migrateAllModules(): int
    {
        $this->info('Migrating all modules...');

        $results = $this->migrationManager->migrateAllModules([
            'force' => $this->option('force'),
        ]);

        $successCount = 0;
        $failureCount = 0;

        foreach ($results as $module => $result) {
            if ($result['status'] === 'completed') {
                $this->info("✓ {$module}: {$result['status']}");
                $successCount++;
            } else {
                $this->error("✗ {$module}: {$result['status']} - " . ($result['error'] ?? $result['message'] ?? 'Unknown error'));
                $failureCount++;
            }
        }

        $this->line('');
        $this->info("Migration Summary:");
        $this->line("✓ Successful: {$successCount}");
        $this->line("✗ Failed: {$failureCount}");

        return $failureCount > 0 ? 1 : 0;
    }
}
