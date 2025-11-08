<?php

namespace App\Core\Services;

use App\Core\Contracts\SubModuleServiceContract;

class SubModuleServiceRegistry
{
    /**
     * Services by sub module name
     */
    protected array $services = [];

    /**
     * Services by class name
     */
    protected array $servicesByClassName = [];

    /**
     * Register a sub module service
     */
    public function register(
        SubModuleServiceContract $service,
        ?string $className = null,
        ?string $subModuleName = null
    ): void {
        $className = $className ?? get_class($service);
        $subModuleName = $subModuleName ?? $service->getDefaultSubModuleName();

        // Store by sub-module name (primary lookup)
        $this->services[$subModuleName] = $className;

        // Store reverse lookup (optional)
        $this->servicesByClassName[$className] = $subModuleName;
    }

    /**
     * Get a sub module service by name
     */
    public function get(string $name): ?SubModuleServiceContract
    {
        if (!isset($this->services[$name])) {
            return null;
        }
        return app($this->services[$name]);
    }

    /**
     * Get a sub module service by class name
     */
    public function getByClassName(string $className): ?SubModuleServiceContract
    {
        if (!isset($this->servicesByClassName[$className])) {
            return null;
        }
        $subModuleName = $this->servicesByClassName[$className];
        return $this->get($subModuleName);
    }
}
