<?php

namespace App\Modules\Payment\Http\Requests;

use App\Modules\Payment\Database\Models\PaymentGateway;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentGatewayUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('isAdmin', $this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $gatewayId = $this->route('id');

        return [
            'name' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'exists:payment_gateways,slug',
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

    /**
     * Get the validation messages that apply to the request.
     */
    public function messages(): array
    {
        return [
            'slug.unique' => 'The slug has already been taken.',
            'mode.in' => 'The mode must be either live or test.',
            'type.in' => 'The type must be either crypto or fiat.',
        ];
    }
}

