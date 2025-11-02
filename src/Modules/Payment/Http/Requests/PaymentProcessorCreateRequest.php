<?php

namespace App\Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Payment\Enums\PaymentProcessorStatus;

class PaymentProcessorCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_gateway_id' => 'required|integer|min:1|exists:payment_gateways,id',
            'payment_uuid' => 'required|string|max:255|exists:payments,uuid',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|max:255',
            'status' => 'required|string|in:' . implode(',', array_column(PaymentProcessorStatus::cases(), 'value')),
        ];
    }

    public function messages(): array
    {
        return [
            'payment_gateway_id.required' => 'The payment gateway id is required.',
            'payment_gateway_id.exists' => 'The selected payment gateway does not exist.',
            'payment_uuid.required' => 'The payment uuid is required.',
            'payment_uuid.exists' => 'The selected payment does not exist.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be at least 0.01.',
        ];
    }
}

