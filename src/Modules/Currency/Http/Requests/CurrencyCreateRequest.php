<?php

namespace App\Modules\Currency\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CurrencyCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            "name" => "required|string|max:255",
            "symbol" => "required|string|max:10",
            "code" => "required|string|max:10",
            "type" => "required|string|in:fiat,crypto",
            "is_default" => "sometimes|boolean",
            "is_active" => "sometimes|boolean",
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            "name.required" => 'Currency name is required',
            "symbol.required" => 'Currency symbol is required',
            "code.required" => 'Currency code is required',
            "type.required" => 'Currency type is required',
            "type.string" => 'Currency type must be a string',
            "type.in" => 'Currency type must be either fiat or crypto',
            "is_default.required" => 'Currency is default is required',
            "is_active.required" => 'Currency is active is required',
            "name.string" => 'Currency name must be a string',
            "name.max" => 'Currency name must be less than 255 characters',
            "symbol.string" => 'Currency symbol must be a string',
            "symbol.max" => 'Currency symbol must be less than 10 characters',
            "code.string" => 'Currency code must be a string',
            "code.max" => 'Currency code must be less than 10 characters',
            "is_default.boolean" => 'Currency is default must be a boolean',
            "is_active.boolean" => 'Currency is active must be a boolean',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        if (!$this->has('is_default')) {
            $this->merge(['is_default' => false]);
        }

        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }
}
