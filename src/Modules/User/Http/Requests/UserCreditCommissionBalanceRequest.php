<?php

namespace App\Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Modules\User\Enums\RolesEnum;

class UserCreditCommissionBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user()->role_id === RolesEnum::ADMIN->value) {
            return true;
        }
        return false;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
        ];
    }

    /**
     * Get the validation messages.
     * @return array
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'The user is required',
            'user_id.exists' => 'The user does not exist',
            'amount.required' => 'The amount is required',
            'amount.numeric' => 'The amount must be a number',
            'amount.min' => 'The amount must be greater than 0',
            'currency_id.required' => 'The currency is required',
            'currency_id.exists' => 'The currency does not exist',
        ];
    }
}
