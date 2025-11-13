<?php

namespace App\Core\Events;

use App\Core\Contracts\TransactionContextInterface;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;
/**
 * Generic event for entity transaction operations
 */
class EntityTransactionEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public TransactionContextInterface $entity,
        public string $operation = 'create',
        public array $metadata = []
    ) {}

    /**
     * Get the entity type
     */
    public function getEntityType(): string
    {
        return $this->entity->getTransactionEntityType();
    }

    /**
     * Get the entity ID
     */
    public function getEntityId(): ?string
    {
        return null;
    }

    /**
     * Get transaction context from the entity
     */
    public function getTransactionContext(): array
    {
        return $this->entity->getTransactionContext($this->operation);
    }

    /**
     * Get additional metadata
     */
    public function getMetadata(): array
    {
        return array_merge(
            $this->entity->getTransactionMetadata(),
            $this->metadata
        );
    }
}
