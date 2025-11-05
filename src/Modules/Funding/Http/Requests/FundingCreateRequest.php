<?php

namespace App\Modules\Funding\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Funding\Rules\IsValidFundingType;
use App\Modules\Funding\Database\Models\Funding;
use App\Core\Rules\AuthUser;
class FundingCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Funding::class);
    }

    public function rules(): array
    {
        return [

            'fundable_type' => 'required|string|in:user,swap,withdrawal',
            'fundable_id' => 'required|integer|min:1',
            'type' => ['required', 'string', new IsValidFundingType()],
            'amount' => 'required|numeric|min:0.01',
            'user_id' => ['required', 'integer', 'min:1', 'exists:users,id', new AuthUser()],
            'currency_id' => 'required|integer|min:1|exists:currencies,id',
            'notes' => 'nullable|string|max:500',
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
                'fundable_type' => 'user',
                'fundable_id' => $this->user()->id,
            ]);

        }
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
            'fundable_type.required' => 'The fundable type is required.',
            'fundable_type.in' => 'The fundable type must be one of: user, swap, withdrawal.',
            'fundable_id.required' => 'The fundable id is required.',
            'fundable_id.integer' => 'The fundable id must be an integer.',
            'fundable_id.min' => 'The fundable id must be at least 1.',
        ];
    }
}
