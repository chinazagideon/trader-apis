<?php

namespace App\Modules\Funding\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Funding\Rules\IsValidFundingType;
class FundingCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fundable_id' => 'required|integer|min:1',
            'fundable_type' => 'required|string',
            'user_id' => 'required|integer|min:1|exists:users,id',
            'type' => ['required', 'string', new IsValidFundingType()],
            'amount' => 'required|numeric|min:0.01',
            'currency_id' => 'required|integer|min:1|exists:currencies,id',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'fundable_id.required' => 'The fundable id is required.',
            'fundable_id.exists' => 'The selected fundable does not exist.',
            'fundable_type.required' => 'The fundable type is required.',
            'fundable_type.in' => 'The fundable type must be one of: user, swap, withdrawal.',
            'user_id.required' => 'The user id is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'uuid.string' => 'The uuid must be a string.',
            'uuid.max' => 'The uuid may not be greater than 255 characters.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be at least 0.01.',
            'currency_id.required' => 'The currency id is required.',
            'currency_id.exists' => 'The selected currency does not exist.',
            'notes.max' => 'The notes may not be greater than 500 characters.',
            'type.required' => 'The type of funding is required.',
        ];
    }
}
