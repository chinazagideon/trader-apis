<?php

namespace App\Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentGatewayUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $gatewayId = $this->route('id');

        return [
            'name' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('payment_gateways', 'slug')->ignore($gatewayId)
            ],
            'description' => 'sometimes|string|nullable',
            'mode' => 'sometimes|string|in:live,test',
            'type' => 'sometimes|string|in:crypto,fiat',
            'is_traditional' => 'sometimes|boolean',
            'instructions' => 'sometimes|array|nullable',
            'supported_currencies' => 'sometimes|array|nullable',
            'credentials' => 'sometimes|array|nullable',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'slug.unique' => 'The slug has already been taken.',
            'mode.in' => 'The mode must be either live or test.',
            'type.in' => 'The type must be either crypto or fiat.',
        ];
    }
}

