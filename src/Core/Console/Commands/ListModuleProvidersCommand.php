<?php

namespace App\Core\Console\Commands;

use App\Core\ModuleManager;
use Illuminate\Console\Command;

class ListModuleProvidersCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'module:providers {--module= : Show providers for a specific module}';

    /**
     * The console command description.
     */
    protected $description = 'List all discovered module service providers';

    /**
     * Execute the console command.
     */
    public function handle(ModuleManager $moduleManager): int
    {
        $moduleManager->discoverModules();

        $specificModule = $this->option('module');

        if ($specificModule) {
            $this->showModuleProviders($moduleManager, $specificModule);
        } else {
            $this->showAllProviders($moduleManager);
        }

        return 0;
    }

    /**
     * Show providers for all modules
     */
    protected function showAllProviders(ModuleManager $moduleManager): void
    {
        $this->info('Module Service Providers Discovery');
        $this->line('');

        $providersByModule = $moduleManager->getServiceProvidersByModule();
        $totalProviders = 0;

        foreach ($providersByModule as $moduleName => $providers) {
            $this->line("<comment>Module: {$moduleName}</comment>");
            $this->line("Providers (" . count($providers) . "):");

            foreach ($providers as $provider) {
                $this->line("  ✓ {$provider}");
                $totalProviders++;
            }

            $this->line('');
        }

        $this->info("Total providers discovered: {$totalProviders}");
        $this->info("Modules processed: " . count($providersByModule));
    }

    /**
     * Show providers for a specific module
     */
    protected function showModuleProviders(ModuleManager $moduleManager, string $moduleName): void
    {
        $providers = $moduleManager->getModuleServiceProviders($moduleName);

        if (empty($providers)) {
            $this->error("No providers found for module: {$moduleName}");
            return;
        }

        $this->info("Service Providers for Module: {$moduleName}");
        $this->line('');

        foreach ($providers as $provider) {
            $this->line("✓ {$provider}");
        }

        $this->line('');
        $this->info("Total providers: " . count($providers));
    }
}
