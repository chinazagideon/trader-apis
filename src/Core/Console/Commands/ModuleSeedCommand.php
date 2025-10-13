<?php

namespace App\Core\Console\Commands;

use App\Core\Database\ModuleSeederManager;
use App\Core\ModuleManager;
use Illuminate\Console\Command;

class ModuleSeedCommand extends Command
{
    protected $signature = 'module:seed
                            {module? : Specific module to seed}
                            {--all : Seed all modules}
                            {--fresh : Run migrations fresh before seeding}';

    protected $description = 'Seed database for modules';

    public function handle(ModuleManager $moduleManager): int
    {
        $seederManager = new ModuleSeederManager($moduleManager);

        if ($this->option('fresh')) {
            $this->info('Running fresh migrations...');
            $this->call('migrate:fresh');
        }

        if ($this->option('all') || !$this->argument('module')) {
            $this->info('Seeding all modules...');
            $seederManager->runModuleSeeders();
        } else {
            $moduleName = $this->argument('module');
            $this->info("Seeding module: {$moduleName}");
            $seederManager->runSpecificModuleSeeders([$moduleName]);
        }

        $this->info('✓ Module seeding completed!');
        return 0;
    }
}
