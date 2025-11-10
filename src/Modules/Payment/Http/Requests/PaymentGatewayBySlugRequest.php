<?php

namespace App\Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentGatewayBySlugRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules for the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'slug' => 'required|string|exists:payment_gateways,slug',
            'filters' => 'sometimes|array|nullable',
        ];
    }

    /**
     * Prepare the data for validation.
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->user() && !$this->user()->can('isAdmin')) {
            $this->merge([
                'filters' => [
                    'is_active' => true,
                ],
            ]);
        }
    }

    /**
     * Get the validation messages for the request.
     * @return array
     */
    public function messages(): array
    {
        return [
            'slug.required' => 'The slug field is required.',
            'slug.exists' => 'The selected payment gateway slug does not exist.',
        ];
    }
}
