<?php

namespace App\Modules\Payment\Events;

use App\Modules\Payment\Database\Models\PaymentProcessor;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class PaymentWasInitialised
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Constructor
     * @param PaymentProcessor $paymentProcessor
     */
    public function __construct(
        public PaymentProcessor $paymentProcessor
    ) {
    }
}
