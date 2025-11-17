<?php

namespace App\Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Payment\Enums\PaymentStatusEnum;

class PaymentProcessorUpdateRequest extends FormRequest
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
            'amount' => 'sometimes|numeric|min:0.01',
            'fee' => 'sometimes|numeric|min:0',
            'total_amount' => 'sometimes|numeric|min:0.01',
            'market_rate' => 'sometimes|numeric|min:0',
            'fiat_amount' => 'sometimes|numeric|min:0',
            'fiat_currency' => 'sometimes|string|max:255',
            'currency' => 'sometimes|string|max:255',
            'status' => 'sometimes|string|in:' . implode(',', array_column(PaymentStatusEnum::cases(), 'value')),
            'processor_data' => 'sometimes|array|nullable',
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

