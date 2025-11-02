<?php

namespace App\Modules\Payment\Http\Requests;

use App\Modules\Payment\Rules\PaymentNotProcessedRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Payment\Rules\ValidatePaymentHash;

class PaymentProcessorInitiateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_reference' => ['required', 'string', 'max:255', 'exists:payments,uuid', new PaymentNotProcessedRule()],
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|max:255',
            'currency_id' => 'required|integer|min:1|exists:currencies,id',
            'payment_gateway_id' => 'required|integer|min:1|exists:payment_gateways,id',
            'payment_hash' => ['required', 'string', 'max:255', new ValidatePaymentHash()],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_reference.required' => 'The reference is required.',
            'payment_reference.exists' => 'The reference does not exist.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be at least 0.01.',
            'currency_id.required' => 'The currency id is required.',
            'currency_id.exists' => 'The selected currency does not exist.',
            'currency.required' => 'The currency is required.',
            'currency.string' => 'The currency must be a string.',
            'currency.max' => 'The currency may not be greater than 255 characters.',
            'payment_hash.required' => 'The payment hash is required.',
            'payment_hash.string' => 'The payment hash must be a string.',
            'payment_hash.exists' => 'The payment hash is invalid.',
        ];
    }
}
