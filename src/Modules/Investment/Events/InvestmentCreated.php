<?php

namespace App\Modules\Investment\Events;

use App\Core\Events\EntityTransactionEvent;
use App\Modules\Investment\Database\Models\Investment;
use App\Modules\User\Database\Models\User;
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

    // In InvestmentCreated event
    public function getNotifiable(): ?User
    {
        // Option 1: Use relationship if available
        if ($this->investment->relationLoaded('user')) {
            return $this->investment->user;
        }

        return null;
    }
}
