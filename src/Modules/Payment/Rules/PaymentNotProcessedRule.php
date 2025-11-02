<?php

namespace App\Modules\Payment\Rules;

use App\Modules\Payment\Database\Models\Payment;
use App\Modules\Payment\Database\Models\PaymentProcessor;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;


class PaymentNotProcessedRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Find the payment by uuid
        $payment = Payment::where('uuid', $value)->first();

        if (!$payment) {
            // If payment doesn't exist, let the exists rule handle this
            return;
        }

        // Check if a payment processor already exists for this payment
        $processorExists = PaymentProcessor::where('payment_id', $payment->id)->exists();


        if ($processorExists) {
            $fail('The payment with this reference has already been processed.');
        }
    }
}

