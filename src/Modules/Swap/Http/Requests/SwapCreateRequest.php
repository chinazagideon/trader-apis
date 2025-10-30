<?php

namespace App\Modules\Swap\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SwapCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|min:1|exists:users,id',
            'from_currency_code' => 'required|string|exists:currencies,code',
            'to_currency_code' => 'required|string|exists:currencies,code',
            'from_currency_type' => 'required|string|in:fiat,crypto',
            'to_currency_type' => 'required|string|in:fiat,crypto',
            'from_amount' => 'required|numeric|min:0.01',
            'status' => 'required|string|in:pending,cancelled,completed',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'The user id is required',
            'user_id.exists' => 'The selected user does not exist',
            'from_currency_code.required' => 'The from currency code is required',
            'from_currency_code.exists' => 'The selected from currency does not exist',
            'to_currency_code.required' => 'The to currency code is required',
            'to_currency_code.exists' => 'The selected to currency does not exist',
            'from_currency_type.required' => 'The from currency type is required',
            'from_currency_type.in' => 'The from currency type must be one of: fiat, crypto',
            'to_currency_type.required' => 'The to currency type is required',
            'to_currency_type.in' => 'The to currency type must be one of: fiat, crypto',
            'from_currency_id.exists' => 'The selected from currency does not exist',
            'to_currency_id.required' => 'The to currency id is required',
            'to_currency_id.exists' => 'The selected to currency does not exist',
            'from_amount.required' => 'The from amount is required',
            'from_amount.numeric' => 'The from amount must be a valid number',
            'from_amount.min' => 'The from amount must be at least 0.01',

        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
