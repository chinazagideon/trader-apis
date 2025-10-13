<?php

namespace App\Core\Traits;

use App\Core\Services\LoggingService;

/**
 * Trait for enhanced logging in services
 */
trait EnhancedLogging
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
     * Log operation start
     */
    protected function logOperationStart(string $operation, array $context = []): array
    {
        $this->initializeLogger();

        $tracking = $this->logger->startPerformanceTracking();

        $this->logger->logOperation(
            $this->getServiceName(),
            $operation,
            'start',
            'Operation started',
            $context
        );

        return $tracking;
    }

    /**
     * Log operation success
     */
    protected function logOperationSuccess(string $operation, array $tracking, array $context = []): void
    {
        $this->initializeLogger();

        $this->logger->logOperation(
            $this->getServiceName(),
            $operation,
            'success',
            'Operation completed successfully',
            $context
        );

        $this->logger->endPerformanceTracking(
            $this->getServiceName(),
            $operation,
            $tracking,
            $context
        );
    }

    /**
     * Log operation error
     */
    protected function logOperationError(string $operation, \Exception $exception, array $tracking, array $context = []): void
    {
        $this->initializeLogger();

        $this->logger->logOperation(
            $this->getServiceName(),
            $operation,
            'error',
            'Operation failed',
            $context
        );

        $this->logger->logError(
            $this->getServiceName(),
            $operation,
            $exception,
            $context
        );

        $this->logger->endPerformanceTracking(
            $this->getServiceName(),
            $operation,
            $tracking,
            $context
        );
    }

    /**
     * Log business logic event
     */
    protected function logBusinessLogic(string $message, array $context = []): void
    {
        $this->initializeLogger();

        $this->logger->logBusinessLogic(
            $this->getServiceName(),
            debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? 'unknown',
            $message,
            $context
        );
    }

    /**
     * Log validation event
     */
    protected function logValidation(string $operation, array $rules, array $data, array $context = []): void
    {
        $this->initializeLogger();

        $this->logger->logBusinessLogic(
            $this->getServiceName(),
            $operation,
            'Validation performed',
            array_merge($context, [
                'validation_rules_count' => count($rules),
                'data_fields_count' => count($data),
                'data_keys' => array_keys($data),
            ])
        );
    }

    /**
     * Log data transformation
     */
    protected function logDataTransformation(string $operation, string $transformation, array $context = []): void
    {
        $this->initializeLogger();

        $this->logger->logBusinessLogic(
            $this->getServiceName(),
            $operation,
            "Data transformation: {$transformation}",
            $context
        );
    }

    /**
     * Get service name for logging
     */
    protected function getServiceName(): string
    {
        return class_basename(static::class);
    }
}
