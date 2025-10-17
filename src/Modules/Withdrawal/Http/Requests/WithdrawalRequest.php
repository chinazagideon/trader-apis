<?php

namespace App\Modules\Withdrawal\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
{


    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|min:1|exists:users,id',
            'payment_id' => 'required|integer|min:1|exists:payments,id',
            'amount' => 'required|numeric|min:0.01',
            'currency_id' => 'required|integer|min:1|exists:currencies,id',
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
            'user_id.required' => 'The user id is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'payment_id.required' => 'The payment id is required.',
            'payment_id.exists' => 'The selected payment does not exist.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be at least 0.01.',
        ];
    }
}
