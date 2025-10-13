<?php

namespace App\Core\Traits;

use App\Core\Services\LoggingService;

/**
 * Trait for enhanced logging in repositories
 */
trait RepositoryLogging
{
    protected LoggingService $logger;

    /**
     * Initialize logger
     */
    protected function initializeLogger(): void
    {
        if (!isset($this->logger)) {
            $this->logger = app(LoggingService::class);
        }
    }

    /**
     * Log repository operation
     */
    protected function logRepositoryOperation(string $operation, string $message, array $context = []): void
    {
        $this->initializeLogger();

        $this->logger->logRepository(
            $this->getRepositoryName(),
            $operation,
            $message,
            $context
        );
    }

    /**
     * Log query performance
     */
    protected function logQueryPerformance(string $operation, float $durationMs, array $context = []): void
    {
        $this->initializeLogger();

        $this->logger->logRepository(
            $this->getRepositoryName(),
            $operation,
            "Query completed in {$durationMs}ms",
            array_merge($context, [
                'duration_ms' => round($durationMs, 2),
                'is_slow' => $durationMs > config('logging.thresholds.slow_query_ms', 1000),
            ])
        );
    }

    /**
     * Log data operation
     */
    protected function logDataOperation(string $operation, int $affectedRows = null, array $context = []): void
    {
        $this->initializeLogger();

        $context = array_merge($context, [
            'affected_rows' => $affectedRows,
            'table' => $this->getModel()->getTable(),
        ]);

        $this->logger->logRepository(
            $this->getRepositoryName(),
            $operation,
            "Data operation completed",
            $context
        );
    }

    /**
     * Log relationship operations
     */
    protected function logRelationshipOperation(string $operation, string $relationship, array $context = []): void
    {
        $this->initializeLogger();

        $this->logger->logRepository(
            $this->getRepositoryName(),
            $operation,
            "Relationship operation: {$relationship}",
            array_merge($context, [
                'relationship' => $relationship,
            ])
        );
    }

    /**
     * Get repository name for logging
     */
    protected function getRepositoryName(): string
    {
        return class_basename(static::class);
    }

    /**
     * Track query execution time
     */
    protected function trackQueryTime(callable $query): mixed
    {
        $startTime = microtime(true);
        $result = $query();
        $duration = (microtime(true) - $startTime) * 1000;

        $this->logQueryPerformance('query_execution', $duration);

        return $result;
    }
}
