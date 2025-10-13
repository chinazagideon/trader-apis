<?php

namespace App\Core\Console\Commands;

use App\Core\ModuleManager;
use Illuminate\Console\Command;

class ModuleListCommand extends Command
{
    protected $signature = 'module:list {--health : Show module health status}';
    protected $description = 'List all available modules';

    protected ModuleManager $moduleManager;

    public function __construct(ModuleManager $moduleManager)
    {
        parent::__construct();
        $this->moduleManager = $moduleManager;
    }

    public function handle(): int
    {
        $modules = $this->moduleManager->getModules();

        if ($modules->isEmpty()) {
            $this->info('No modules found.');
            return 0;
        }

        $this->info('Available Modules:');
        $this->line('');

        $headers = ['Name', 'Status', 'Provider'];

        if ($this->option('health')) {
            $headers[] = 'Health';
        }

        $rows = [];

        foreach ($modules as $name => $module) {
            $row = [
                $name,
                $this->moduleManager->isModuleRegistered($name) ? 'Registered' : 'Available',
                class_exists($module['provider']) ? '✓' : '✗',
            ];

            if ($this->option('health')) {
                $health = $this->moduleManager->getModuleHealth($name);
                $row[] = $health['status'];
            }

            $rows[] = $row;
        }

        $this->table($headers, $rows);

        return 0;
    }
}
