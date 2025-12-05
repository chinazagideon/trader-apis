<?php

namespace App\Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use App\Modules\Payment\Database\Models\PaymentGateway;
use App\Modules\Payment\Policies\PaymentGatewayPolicy;
use App\Modules\User\Enums\RolesEnum;

class PaymentGatewayCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', $this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'sometimes|string|nullable',
            'mode' => 'sometimes|string|in:live,test',
            'type' => 'sometimes|string|in:crypto,fiat',
            'is_traditional' => 'sometimes|boolean',
            'payment_address' => 'sometimes|string',
            'wallet_network' => 'sometimes|string',
            'instructions' => 'required|array|nullable',
            'supported_currencies' => 'sometimes|array|nullable',
            'credentials' => 'sometimes|array|nullable',
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'mode' => 'test',
        ]);
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'slug.string' => 'The slug must be a string.',
            'slug.max' => 'The slug may not be greater than 255 characters.',
            'slug.unique' => 'The slug has already been taken.',
            'mode.required' => 'The mode field is required.',
            'mode.in' => 'The mode must be either live or test.',
            'type.required' => 'The type field is required.',
            'type.in' => 'The type must be either crypto or fiat.',
        ];
    }
}

