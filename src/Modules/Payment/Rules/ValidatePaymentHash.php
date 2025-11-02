<?php

namespace App\Modules\Payment\Rules;

use App\Modules\Payment\Database\Models\PaymentProcessor;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Modules\Payment\Enums\PaymentStatusEnum;

class ValidatePaymentHash implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $paymentProcessor = PaymentProcessor::where('payment_hash', $value)->first();

        if (!$paymentProcessor) {
            // $fail('The payment hash is invalid.');
            return;
        }

        if (PaymentProcessor::where('payment_hash', $value)->exists()) {
            $current_status = $paymentProcessor->status;
            $current_status_label = PaymentStatusEnum::from($current_status)->label();
            $current_status_label = strtolower($current_status_label);

            $fail('The payment with this payment hash is ' . $current_status_label . '.');
        }
    }
}
