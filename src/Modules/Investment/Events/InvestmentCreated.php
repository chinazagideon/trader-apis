<?php

namespace App\Modules\Investment\Events;

use App\Core\Events\EntityTransactionEvent;
use App\Modules\Investment\Database\Models\Investment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvestmentCreated extends EntityTransactionEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Investment $investment,
        public array $metadata = []
    ) {
        parent::__construct($investment, 'create', $metadata);
    }

    /**
     * Get the channels the event should be broadcast on.
     */
    public function broadcastOn(): array
    {
        return [];
    }
}
