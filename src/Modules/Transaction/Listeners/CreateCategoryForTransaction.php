<?php

namespace App\Modules\Transaction\Listeners;

use App\Modules\Transaction\Events\TransactionWasCreated;
use App\Modules\Transaction\Services\TransactionCategoryService;
use App\Core\Traits\ConfigurableListener;
use App\Core\Contracts\ConfigurableListenerInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class CreateCategoryForTransaction implements ConfigurableListenerInterface, ShouldQueue
{
    use ConfigurableListener;

    /**
     * Configuration keys for this listener
     */
    protected string $eventConfigKey = 'transaction_created';
    protected string $listenerConfigKey = 'create_category';

    public function __construct(
        private TransactionCategoryService $transactionCategoryService
    ) {}

    public function handle(TransactionWasCreated $event): void
    {
        // Extract category information from transaction metadata or context
        $transaction = $event->transaction;
        $metadata = $event->getMetadata();

        // Check if category information is available
        if (isset($metadata['category_id']) && $metadata['category_id']) {
            $categoryData = [
                'transaction_id' => $transaction->id,
                'category_id' => $metadata['category_id'],
            ];

            $this->transactionCategoryService->store($categoryData);
        } else {
            Log::warning('No category_id found in metadata', [
                'transaction_id' => $transaction->id,
                'metadata' => $metadata,
            ]);
        }
    }
}
