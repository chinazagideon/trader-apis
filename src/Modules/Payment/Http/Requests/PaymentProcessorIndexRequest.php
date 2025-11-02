<?php

namespace App\Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentProcessorIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_gateway_id' => 'sometimes|integer|min:1|exists:payment_gateways,id',
            'payable_id' => 'sometimes|integer|min:1|exists:payments,id',
            'status' => 'sometimes|string|in:pending,verified,completed,failed',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_gateway_id.exists' => 'The selected payment gateway does not exist.',
            'payable_id.exists' => 'The selected payable does not exist.',
            'status.in' => 'The status must be one of: pending, verified, completed, failed.',
        ];
    }
}
