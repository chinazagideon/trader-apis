<?php

namespace App\Core\Services;

use App\Core\Contracts\TransactionContextInterface;
use App\Core\Services\LoggingService;

/**
 * Factory for creating transaction data from entity context
 */
class TransactionContextFactory
{
    public function __construct(
        private LoggingService $logger
    ) {}

    /**
     * Create transaction data from entity context
     */
    public function createTransactionData(TransactionContextInterface $entity, string $operation = 'create', array $additionalContext = []): array
    {
        $entityType = $entity->getTransactionEntityType();
        $context = $entity->getTransactionContext($operation);
        $metadata = $entity->getTransactionMetadata();

        $this->logger->logOperation(
            'TransactionContextFactory',
            'createTransactionData',
            'start',
            "Creating transaction data for {$entityType}",
            [
                'entity' => json_encode($entity),
                'operation' => $operation,
            ]
        );

        try {
            $transactionData = $this->buildTransactionData($entityType, $context, $metadata, $additionalContext);

            $this->logger->logOperation(
                'TransactionContextFactory',
                'createTransactionData',
                'success',
                "Transaction data created",
                [
                    'entity' => json_encode($entity),
                    'operation' => $operation,
                    'transaction_data_keys' => array_keys($transactionData),
                ]
            );

            return $transactionData;
        } catch (\Exception $e) {
            $this->logger->logError(
                'TransactionContextFactory',
                'createTransactionData',
                $e,
                [
                    'entity' => json_encode($entity),
                    'operation' => $operation,
                ]
            );

            throw $e;
        }
    }

    /**
     * Build transaction data based on entity type and context
     */
    private function buildTransactionData(string $entityType, array $context, array $metadata, array $additionalContext): array
    {
        $baseData = [
            'transactable_type' => $entityType,
            'transactable_id' => $context['entity_id'] ?? null,
            'metadata' => array_merge($metadata, $additionalContext),
            'created_at' => now(),
        ];

        return match ($entityType) {
            'investment' => $this->buildInvestmentTransactionData($context, $baseData),
            'user' => $this->buildUserTransactionData($context, $baseData),
            default => $this->buildDefaultTransactionData($context, $baseData),
        };
    }

    /**
     * Build transaction data for investment entities
     */
    private function buildInvestmentTransactionData(array $context, array $baseData): array
    {

        // Extract category_id from metadata request or direct metadata
        $categoryId = $baseData['metadata']['request']['category_id'] ??
            $baseData['metadata']['category_id'] ??
            $context['category_id'] ?? 0;

        return array_merge($baseData, [
            'transaction_category_id' => $categoryId,
            'narration' => $context['narration'] ?? 'N/A',
            'entry_type' => $context['entry_type'],
            'total_amount' => $context['amount'] ?? 0,
            'status' => $context['status'],
            'metadata' => array_merge($baseData['metadata'], [
                'source' => 'investment_created_event',
                'investment_type' => $context['investment_type'] ?? 'unknown',
                'category_id' => $categoryId, // Ensure category_id is in metadata
            ]),
        ]);
    }

    /**
     * Build transaction data for user entities
     */
    private function buildUserTransactionData(array $context, array $baseData): array
    {

        return array_merge($baseData, [
            'transaction_category_id' => $context['category_id'],
            'narration' => $context['narration'] ?? 'N/A',
            'entry_type' => $context['entry_type'],
            'total_amount' => $context['amount'] ?? 0,
            'status' => $context['status'],
            'metadata' => array_merge($baseData['metadata'], [
                'source' => 'user_was_created_event',
                'user_action' => $context['action'] ?? 'unknown',
            ]),
        ]);
    }

    /**
     * Build default transaction data for unknown entity types
     */
    private function buildDefaultTransactionData(array $context, array $baseData): array
    {
        // For unknown entity types, require explicit category_id
        if (!isset($context['category_id'])) {
            throw new \InvalidArgumentException("category_id is required for unknown entity types");
        }

        return array_merge($baseData, [
            'transaction_category_id' => $context['category_id'],
            'narration' => $context['narration'] ?? 'N/A',
            'entry_type' => $context['entry_type'] ?? 'unknown',
            'total_amount' => $context['amount'] ?? 0,
            'status' => $context['status'] ?? 'unknown',
            'metadata' => array_merge($baseData['metadata'], [
                'source' => 'generic_event',
            ]),
        ]);
    }

    /**
     * Generate narration for investment transactions
     */
    private function generateInvestmentNarration(array $context): string
    {
        $type = $context['investment_type'] ?? 'investment';
        $amount = $context['amount'] ?? 0;
        return "Investment created: {$type} - $" . number_format($amount, 2);
    }

    /**
     * Generate narration for user transactions
     */
    private function generateUserNarration(array $context): string
    {
        $action = $context['action'] ?? 'registration';
        $amount = $context['amount'] ?? 0;
        return "User {$action}: $" . number_format($amount, 2);
    }
}
