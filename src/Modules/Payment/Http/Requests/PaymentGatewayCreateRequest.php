<?php

namespace App\Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentGatewayCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:payment_gateways,slug',
            'description' => 'sometimes|string|nullable',
            'mode' => 'required|string|in:live,test',
            'type' => 'required|string|in:crypto,fiat',
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
            'name.required' => 'The name field is required.',
            'slug.required' => 'The slug field is required.',
            'slug.unique' => 'The slug has already been taken.',
            'mode.required' => 'The mode field is required.',
            'mode.in' => 'The mode must be either live or test.',
            'type.required' => 'The type field is required.',
            'type.in' => 'The type must be either crypto or fiat.',
        ];
    }
}

