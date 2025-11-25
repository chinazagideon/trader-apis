<?php

namespace App\Modules\Payment\Events;

use App\Core\Events\EntityTransactionEvent;
use App\Modules\Payment\Database\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use App\Modules\User\Database\Models\User;

class CreatePaymentTransactionEvent extends EntityTransactionEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public function __construct(
        public Payment $payment,
        public array $metadata = []
    ) {
        parent::__construct($payment, 'create', $metadata);
    }


    /**
     * Get the notifiable user
     * @return ?User
     */
    public function getNotifiable(): ?User
    {
        return $this->payment->user;
    }

    /**
     * Get the metadata for the event
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->payment->getTransactionMetadata($this->operation, $this->metadata);
    }

}
