<?php

namespace App\Modules\Swap\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SwapRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|min:1|exists:users,id',
            'from_currency_id' => 'required|integer|min:1|exists:currencies,id',
            'to_currency_id' => 'required|integer|min:1|exists:currencies,id',
            'from_amount' => 'required|numeric|min:0.01',
            'to_amount' => 'nullable|numeric|min:0.01',
            'fee_amount' => 'nullable|numeric|min:0.01',
            'total_amount' => 'nullable|numeric|min:0.01',
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
            'from_currency_id.required' => 'The from currency id is required',
            'from_currency_id.exists' => 'The selected from currency does not exist',
            'to_currency_id.required' => 'The to currency id is required',
            'to_currency_id.exists' => 'The selected to currency does not exist',
            'from_amount.required' => 'The from amount is required',
            'from_amount.numeric' => 'The from amount must be a valid number',
            'from_amount.min' => 'The from amount must be at least 0.01',
            'to_amount.numeric' => 'The to amount must be a valid number',
            'to_amount.min' => 'The to amount must be at least 0.01',
            'fee_amount.numeric' => 'The fee amount must be a valid number',
            'fee_amount.min' => 'The fee amount must be at least 0.01',
            'total_amount.numeric' => 'The total amount must be a valid number',
            'total_amount.min' => 'The total amount must be at least 0.01',
            'status.required' => 'The status is required',
            'status.in' => 'The status must be one of: pending, cancelled, completed',
            'notes.max' => 'The notes may not be greater than 500 characters',
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
