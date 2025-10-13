<?php

namespace App\Core\Contracts;

use App\Core\Http\ServiceResponse;

interface ServiceInterface
{
    /**
     * Get the service name
     */
    public function getServiceName(): string;

    /**
     * Check if service is available
     */
    public function isAvailable(): bool;

    /**
     * Execute a service operation with automatic exception handling
     */
    public function executeServiceOperation(callable $operation, string $operationName = 'operation'): ServiceResponse;

    /**
     * Get all resources with optional filters and pagination
     */
    public function index(array $filters = [], int $perPage = 15): ServiceResponse;

    /**
     * Get a specific resource by ID
     */
    public function show(int $id): ServiceResponse;

    /**
     * Create a new resource
     */
    public function store(array $data): ServiceResponse;

    /**
     * Update an existing resource
     */
    public function update(int $id, array $data): ServiceResponse;

    /**
     * Delete a resource
     */
    public function destroy(int $id): ServiceResponse;
}
