<?php

namespace App\Modules\Payment\Observer;

use App\Modules\Payment\Database\Models\Payment;
use App\Modules\Payment\Events\CreatePaymentTransactionEvent;
use App\Modules\Category\Enums\CategoryType;

class PaymentObserver
{
    protected $afterCommit = true;
    /**
     * Handle the payment created event
     * @param Payment $payment
     * @return void
     */
    public function created(Payment $payment): void
    {
        CreatePaymentTransactionEvent::dispatch(
            $payment,
            $payment->getTransactionContext('create', ['category_id' => CategoryType::Payment->value])
        );
    }
}
