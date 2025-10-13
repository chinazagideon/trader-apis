<?php

namespace App\Modules\Transaction\Events;

use App\Core\Events\EntityTransactionEvent;
use App\Modules\Transaction\Database\Models\Transaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class TransactionWasCreated extends EntityTransactionEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Transaction $transaction,
        public array $metadata = []
    ) {
        parent::__construct($transaction, 'create', $metadata);
    }

      /**
     * Get additional metadata
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Get the channels the event should be broadcast on.
     */
    public function broadcastOn(): array
    {
        return [];
    }
}
