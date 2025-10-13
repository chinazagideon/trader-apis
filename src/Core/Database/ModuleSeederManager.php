<?php

namespace App\Core\Database;

use App\Core\ModuleManager;
use App\Core\Services\LoggingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ModuleSeederManager
{
    protected ModuleManager $moduleManager;
    protected LoggingService $logger;
    protected int $totalSeeded = 0;
    protected int $totalFailed = 0;
    protected array $seedingResults = [];

    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
        $this->logger = app(LoggingService::class);
    }

    /**
     * Run all module seeders
     */
    public function runModuleSeeders(): void
    {
        Log::info('[ModuleSeederManager] Starting seeding for all modules');

        $this->moduleManager->discoverModules();
        $modules = $this->moduleManager->getModules();

        Log::info('[ModuleSeederManager] Found ' . count($modules) . ' modules to seed');

        foreach ($modules as $module) {
            $this->runModuleSeeder($module);
        }

        $this->logSeedingSummary();
    }

    /**
     * Run seeder for a specific module
     */
    public function runModuleSeeder(array $module): void
    {
        $moduleName = $module['name'];
        $seederPath = $module['path'] . '/Database/Seeders';

        Log::info("[ModuleSeederManager] Processing module: {$moduleName}", [
            'module_name' => $moduleName,
            'seeder_path' => $seederPath,
            'path_exists' => is_dir($seederPath),
        ]);

        if (!is_dir($seederPath)) {
            Log::warning("[ModuleSeederManager] Seeder directory not found for module: {$moduleName}", [
                'expected_path' => $seederPath,
            ]);
            $this->seedingResults[$moduleName] = [
                'status' => 'skipped',
                'reason' => 'Seeder directory not found',
                'seeders_run' => 0,
            ];
            return;
        }

        $seeders = glob($seederPath . '/*Seeder.php');

        if (empty($seeders)) {
            Log::warning("[ModuleSeederManager] No seeder files found in: {$seederPath}", [
                'module' => $moduleName,
                'path' => $seederPath,
            ]);
            $this->seedingResults[$moduleName] = [
                'status' => 'skipped',
                'reason' => 'No seeder files found',
                'seeders_run' => 0,
            ];
            echo "⚠ Module {$moduleName}: No seeders found\n";
            return;
        }

        Log::info("[ModuleSeederManager] Found " . count($seeders) . " seeder(s) for {$moduleName}", [
            'module' => $moduleName,
            'seeders_found' => array_map('basename', $seeders),
        ]);

        $moduleSeededCount = 0;
        $moduleFailedCount = 0;

        foreach ($seeders as $seederFile) {
            $seederClass = $this->getSeederClassFromFile($seederFile, $module['namespace']);

            try {
                if (!class_exists($seederClass)) {
                    Log::error("[ModuleSeederManager] Seeder class not found: {$seederClass}", [
                        'file' => $seederFile,
                        'expected_class' => $seederClass,
                    ]);
                    $moduleFailedCount++;
                    continue;
                }

                Log::info("[ModuleSeederManager] Running seeder: {$seederClass}");

                $startTime = microtime(true);
                $beforeRecords = $this->getDatabaseRecordCount();

                $seeder = app($seederClass);
                $seeder->run();

                $afterRecords = $this->getDatabaseRecordCount();
                $duration = microtime(true) - $startTime;
                $recordsAdded = $afterRecords - $beforeRecords;

                Log::info("[ModuleSeederManager] Seeder completed successfully: {$seederClass}", [
                    'duration_ms' => round($duration * 1000, 2),
                    'records_before' => $beforeRecords,
                    'records_after' => $afterRecords,
                    'records_added' => $recordsAdded,
                ]);

                echo "✓ Seeded: {$seederClass} ({$recordsAdded} records added in " . round($duration * 1000, 2) . "ms)\n";

                $moduleSeededCount++;
                $this->totalSeeded++;

            } catch (\Exception $e) {
                $this->totalFailed++;
                $moduleFailedCount++;

                Log::error("[ModuleSeederManager] Seeder failed: {$seederClass}", [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);

                echo "✗ Failed: {$seederClass} - {$e->getMessage()}\n";
            }
        }

        $this->seedingResults[$moduleName] = [
            'status' => $moduleFailedCount === 0 ? 'success' : 'partial',
            'seeders_run' => $moduleSeededCount,
            'seeders_failed' => $moduleFailedCount,
            'total_seeders' => count($seeders),
        ];
    }

    /**
     * Get seeder class name from file path
     */
    protected function getSeederClassFromFile(string $filePath, string $namespace): string
    {
        $fileName = basename($filePath, '.php');
        return "{$namespace}\\Database\\Seeders\\{$fileName}";
    }

    /**
     * Run seeders for specific modules
     */
    public function runSpecificModuleSeeders(array $moduleNames): void
    {
        Log::info('[ModuleSeederManager] Starting seeding for specific modules', [
            'modules' => $moduleNames,
        ]);

        $this->moduleManager->discoverModules();

        foreach ($moduleNames as $moduleName) {
            $module = $this->moduleManager->getModule($moduleName);

            if ($module) {
                $this->runModuleSeeder($module);
            } else {
                Log::warning("[ModuleSeederManager] Module not found: {$moduleName}");
                echo "⚠ Module not found: {$moduleName}\n";
                $this->seedingResults[$moduleName] = [
                    'status' => 'not_found',
                    'reason' => 'Module does not exist',
                    'seeders_run' => 0,
                ];
            }
        }

        $this->logSeedingSummary();
    }

    /**
     * Get database record count (rough estimate)
     */
    protected function getDatabaseRecordCount(): int
    {
        try {
            $tables = DB::select('SHOW TABLES');
            $count = 0;

            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                $result = DB::select("SELECT COUNT(*) as count FROM `{$tableName}`");
                $count += $result[0]->count ?? 0;
            }

            return $count;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Log seeding summary
     */
    protected function logSeedingSummary(): void
    {
        Log::info('[ModuleSeederManager] Seeding completed', [
            'total_seeded' => $this->totalSeeded,
            'total_failed' => $this->totalFailed,
            'results' => $this->seedingResults,
        ]);

        echo "\n";
        echo "═══════════════════════════════════════════════════════\n";
        echo " Seeding Summary\n";
        echo "═══════════════════════════════════════════════════════\n";
        echo "Total Seeders Run: " . $this->totalSeeded . "\n";
        echo "Total Failed: " . $this->totalFailed . "\n";
        echo "\nModule Results:\n";

        foreach ($this->seedingResults as $module => $result) {
            $status = $result['status'];
            $icon = match($status) {
                'success' => '✓',
                'partial' => '⚠',
                'skipped' => '⊘',
                'not_found' => '✗',
                default => '?',
            };

            echo "  {$icon} {$module}: ";

            if ($status === 'success') {
                echo "{$result['seeders_run']} seeder(s) completed\n";
            } elseif ($status === 'partial') {
                echo "{$result['seeders_run']}/{$result['total_seeders']} completed ({$result['seeders_failed']} failed)\n";
            } elseif ($status === 'skipped') {
                echo "{$result['reason']}\n";
            } elseif ($status === 'not_found') {
                echo "Module not found\n";
            }
        }

        echo "═══════════════════════════════════════════════════════\n";
    }

    /**
     * Get seeding results
     */
    public function getSeedingResults(): array
    {
        return [
            'total_seeded' => $this->totalSeeded,
            'total_failed' => $this->totalFailed,
            'modules' => $this->seedingResults,
        ];
    }
}
