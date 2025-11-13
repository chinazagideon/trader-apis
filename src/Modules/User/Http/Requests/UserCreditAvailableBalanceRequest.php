<?php

namespace App\Modules\User\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use App\Modules\User\Enums\RolesEnum;

class UserCreditAvailableBalanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     */
    public function authorize(): bool
    {
        if($this->user()->role_id === RolesEnum::ADMIN->value){
            return true;
        }
        return false;
    }

    /**
     * Get the validation rules.
     * @return array
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
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
