<?php

namespace App\Core\Contracts;

/**
 * Interface for entities that can provide transaction context
 */
interface TransactionContextInterface
{
    /**
     * Get transaction context for the entity
     */
    public function getTransactionContext(string $operation = 'create', array $request = []): array;

    /**
     * Get the entity type for transaction mapping
     */
    public function getTransactionEntityType(): string;

    /**
     * Get additional metadata for transaction creation
     */
    public function getTransactionMetadata(): array;
}
