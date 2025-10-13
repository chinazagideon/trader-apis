<?php

namespace App\Core\Contracts;

use Illuminate\Support\ServiceProvider;

/**
 * Interface for module service providers
 *
 * This interface ensures consistent structure across all module service providers
 * and provides a way to identify module-specific providers.
 */
interface ModuleServiceProviderInterface
{
    /**
     * Get the module name this provider belongs to
     */
    public function getModuleName(): string;

    /**
     * Get the module namespace
     */
    public function getModuleNamespace(): string;

    /**
     * Get the module path
     */
    public function getModulePath(): string;

    /**
     * Get the priority for this provider (higher = earlier registration)
     * Default is 0, negative values register later
     */
    public function getPriority(): int;

    /**
     * Check if this provider should be registered
     * Useful for conditional registration based on environment or config
     */
    public function shouldRegister(): bool;
}
