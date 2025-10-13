<?php

namespace App\Modules\Transaction\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Sub-resource specific request for TransactionCategory create operations
 * This will be resolved with highest priority by the new sub-resource resolver
 * Pattern: TransactionCategoryCreateRequest
 */
class TransactionCategoryCreateRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:transactions_category,name'],
            'description' => ['sometimes', 'string', 'max:1000'],
            'transaction_id' => ['required', 'integer', 'exists:transactions,id'],
            'color' => ['sometimes', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['sometimes', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],

            // TransactionCategory-specific validation rules
            'parent_id' => ['sometimes', 'integer', 'exists:transactions_category,id'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:999'],
            'metadata' => ['sometimes', 'array'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'name.unique' => 'This category name already exists.',
            'transaction_id.required' => 'Transaction ID is required.',
            'transaction_id.exists' => 'The selected transaction does not exist.',
            'color.regex' => 'Color must be a valid hex color code (e.g., #FF5733).',
            'parent_id.exists' => 'The selected parent category does not exist.',
            'sort_order.max' => 'Sort order cannot exceed 999.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'category name',
            'parent_id' => 'parent category',
            'sort_order' => 'sort order',
            'transaction_id' => 'transaction ID',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }

        if (!$this->has('sort_order')) {
            $this->merge(['sort_order' => 0]);
        }

        // Ensure color is uppercase
        if ($this->has('color')) {
            $this->merge([
                'color' => strtoupper($this->input('color'))
            ]);
        }
    }
}
