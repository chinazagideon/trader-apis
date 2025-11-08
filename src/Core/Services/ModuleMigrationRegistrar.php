<?php

namespace App\Core\Services;

use App\Core\ModuleManager;

/**
 * Service responsible for discovering module migration paths
 */
class ModuleMigrationRegistrar
{
    public function __construct(
        protected ModuleManager $moduleManager
    ) {}

    /**
     * Get all module migration paths
     *
     * @return array Array of migration directory paths
     */
    public function getMigrationPaths(): array
    {
        if (!$this->moduleManager->isModulesDiscovered()) {
            $this->moduleManager->discoverModules();
        }

        $paths = [];

        foreach ($this->moduleManager->getModules() as $module) {
            $modulePaths = $this->getModuleMigrationPaths($module['path']);
            $paths = array_merge($paths, $modulePaths);
        }

        return array_unique(array_filter($paths, 'is_dir'));
    }

    /**
     * Get all possible migration paths for a module
     * Prefers /Database/Migrations over /database/migrations to avoid duplicates
     *
     * @param string $modulePath Base path of the module
     * @return array Array of potential migration paths (only existing ones)
     */
    protected function getModuleMigrationPaths(string $modulePath): array
    {
        $paths = [];

        // Prefer /Database/Migrations (PascalCase) over /database/migrations (lowercase)
        $preferredPath = $modulePath . '/Database/Migrations';
        $alternatePath = $modulePath . '/database/migrations';

        // Only add path if it exists and has migration files
        if (is_dir($preferredPath) && !empty(glob($preferredPath . '/*_*.php'))) {
            $paths[] = $preferredPath;
        } elseif (is_dir($alternatePath) && !empty(glob($alternatePath . '/*_*.php'))) {
            // Only use alternate if preferred doesn't exist
            $paths[] = $alternatePath;
        }

        return $paths;
    }
}
