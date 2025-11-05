<?php

namespace App\Modules\Funding\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Funding\Rules\IsValidFundingType;
use App\Modules\Funding\Database\Models\Funding;
class FundingCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Funding::class);
    }

    public function rules(): array
    {
        return [

            'type' => ['required', 'string', new IsValidFundingType()],
            'amount' => 'required|numeric|min:0.01',
            'currency_id' => 'required|integer|min:1|exists:currencies,id',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
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
