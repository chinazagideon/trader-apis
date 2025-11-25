<?php

namespace App\Modules\Transaction\Listeners;

use App\Core\Events\EntityTransactionEvent;
use App\Modules\Transaction\Services\TransactionService;
use App\Core\Services\LoggingService;
use App\Core\Services\TransactionContextFactory;
use App\Core\Contracts\ConfigurableListenerInterface;
use App\Core\Traits\ConfigurableListener;
use App\Modules\Transaction\Events\TransactionWasCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Core\Exceptions\AppException;

class CreateTransactionForEntity implements ShouldQueue
{


    /**
     * The number of times the job may be attempted.
     */
    public int $tries;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public array|int $backoff;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout;

    /**
     * The name of the queue connection to use.
     */
    public ?string $connection;

    /**
     * The name of the queue to push the job to.
     */
    public ?string $queue;

    public function __construct(
        private TransactionService $transactionService,
        private LoggingService $logger,
        private TransactionContextFactory $contextFactory
    ) {
        // Initialize queue properties from configuration
        // $this->tries = $this->getTries();
        // $this->backoff = $this->getBackoff();
        // $this->timeout = $this->getTimeout();
        // $this->connection = $this->getQueueConnection();
        // $this->queue = $this->getQueue();
    }

    /**
     * Handle the event.
     */
    public function handle(EntityTransactionEvent $event): void
    {
        \Illuminate\Support\Facades\Log::info('CreateTransactionForEntity event received', [
            'event' => $event,
            'metadata' => $event->getMetadata(),
        ]);
        $tracking = $this->logger->startPerformanceTracking();

        try {
            // Create transaction data using context factory
            $transactionData = $this->contextFactory->createTransactionData(
                $event->entity,
                $event->operation,
                $event->getMetadata()
            );

            $strippedCategoryIdData = $this->transactionService->unsetCategoryId($transactionData);

            // Create transaction
            $response = $this->transactionService->store($strippedCategoryIdData);


            // Log event dispatch for debugging
            $this->logger->logOperation(
                'CreateTransactionForEntity',
                'handle',
                'success',
                "TransactionWasCreated event dispatched",
                [
                    'event' =>  json_encode($event),
                    'transaction_id' => $response->getData()->id ?? null,
                    'metadata' => $event->getMetadata(),
                ]
            );

            if (! $response->isSuccess()) {
                $this->logger->logOperation(
                    'CreateTransactionForEntity',
                    'handle',
                    'error',
                    "Failed to create transaction",
                    [
                        'event' =>  json_encode($event),
                        'error_message' => $response->getMessage(),
                        'transaction_data' => $strippedCategoryIdData,
                    ]
                );

                throw new AppException($response->getMessage());
            }

            // Dispatch TransactionWasCreated event
            // event(new TransactionWasCreated($response->getData(), $event->getMetadata()));
        } catch (\Exception $e) {
            $this->logger->logOperation(
                'CreateTransactionForEntity',
                'handle',
                'error',
                "Exception occurred while creating transaction",
                [
                    'event' =>  json_encode($event),
                    'transaction_data' => $strippedCategoryIdData ?? [],
                    'exception_message' => $e->getMessage(),
                ]
            );


            // Re-throw to mark job as failed
            throw $e;
        } finally {
            $this->logger->endPerformanceTracking(
                'CreateTransactionForEntity',
                'handle',
                $tracking,
                [
                    'event' =>  json_encode($event),
                ]
            );
        }
    }


    /**
     * Handle a job failure.
     */
    public function failed(EntityTransactionEvent $event, $exception): void
    {
        // Convert Error to Exception if needed
        $exceptionToLog = $exception instanceof \Exception ? $exception : new \Exception($exception->getMessage(), $exception->getCode(), $exception);

        $this->logger->logError(
            'CreateTransactionForEntity',
            'failed',
            $exceptionToLog,
            [
                'exception_class' => get_class($exception),
                'exception_message' => $exception->getMessage(),
                'exception_code' => $exception->getCode(),
            ]
        );
    }
}
