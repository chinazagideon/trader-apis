<?php

namespace App\Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentGatewayBySlugRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => 'required|string|exists:payment_gateways,slug',
        ];
    }

    public function messages(): array
    {
        return [
            'slug.required' => 'The slug field is required.',
            'slug.exists' => 'The selected payment gateway slug does not exist.',
        ];
    }
}

