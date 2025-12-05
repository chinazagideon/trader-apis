<?php

namespace App\Modules\Withdrawal\Http\Requests;

use App\Core\Rules\AuthUser;
use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Withdrawal\Rules\IsValidWithdrawalType;
use App\Core\Rules\MorphExistsRule;
use App\Modules\Withdrawal\Database\Models\Withdrawal;

class WithdrawalCreateRequest extends FormRequest
{


    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Withdrawal::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $allowedTypes = config('Withdrawal.allowed_types');
        return [
            'user_id' => ['required', 'integer', 'min:1', 'exists:users,id', new AuthUser()],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'withdrawable_id' => ['required', 'integer', new MorphExistsRule('withdrawable_type', $allowedTypes)],
            "withdrawable_type" => ["required", "string",  'in:' . implode(',', array_keys($allowedTypes))],
            'currency_id' => ['required', 'integer', 'min:1', 'exists:currencies,id'],
            'fiat_currency_id' => ['required', 'integer', 'min:1', 'exists:currencies,id'],
            'notes' => ['nullable', 'string', 'max:500'],
            'type' => ['required', 'string', new IsValidWithdrawalType()],
            'method' => ['required', 'array'],
            'method.*' => ['required', 'string']
        ];
    }

    /**
     * Prepare the data for validation.
     *
     */
    protected function prepareForValidation(): void
    {
        if (!$this->user()->hasPermission('admin.all')) {
            $this->merge([
                'user_id' => $this->user()->id,
                'withdrawable_type' => 'user',
                'withdrawable_id' => $this->user()->id,

            ]);
        }
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
            'fiat_currency_id.required' => 'The fiat currency id is required.',
            'fiat_currency_id.exists' => 'The selected fiat currency does not exist.',
            'method.required' => 'The method is required.',
            'method.array' => 'The methods must be an array.',
            'method.*.required' => 'The method is required.',
            'method.*.string' => 'The method must be a string.',
            'method.type.required' => 'The method type is required.',
            'method.type.string' => 'The method type must be a string.',
            'method.type.in' => 'The method type must be one of: bank, crypto.',
        ];
    }
}
