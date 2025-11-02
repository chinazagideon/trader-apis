<?php

namespace App\Modules\Withdrawal\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Withdrawal\Rules\IsValidWithdrawalType;
use App\Core\Rules\MorphExistsRule;
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
        $allowedTypes = config('Withdrawal.allowed_types');
        return [
            'user_id' => 'required|integer|min:1|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'withdrawable_id' => [
                'required',
                'integer',
                new MorphExistsRule('withdrawable_type', $allowedTypes)
            ],
            "withdrawable_type" => ["required", "string",  'in:' . implode(',', array_keys($allowedTypes))],

            'currency_id' => 'required|integer|min:1|exists:currencies,id',
            'notes' => 'nullable|string|max:500',
            'type' => ['required', 'string', new IsValidWithdrawalType()],
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
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be at least 0.01.',
            'type.required' => 'The type is required.',
            'type.string' => 'The type must be a string.',
        ];
    }
}
