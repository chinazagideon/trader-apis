<?php

namespace App\Modules\Transaction\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Core\Rules\MorphExistsRule;

class TransactionCreateRequest extends FormRequest
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
        $allowedTypes = config('Transaction.allowed_types');

        return [
            'transactionable_id' => [
                'required',
                'integer',
                new MorphExistsRule('transactionable_type', $allowedTypes)
            ],
            "transactable_type" => ["required", "string",  'in:' . implode(',', array_keys($allowedTypes))],
            "narration" => "required|string|max:255",
            "entry_type" => "required|string|in:credit,debit",
            "total_amount" => "required|numeric|min:0.01",
            "status" => "required|string|in:pending,completed,failed",
            "transaction_category_id" => "required|integer|min:1|exists:transaction_categories,id",
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            "transactable_id.required" => "The transactable id is required.",
            "transactable_id.exists" => "The selected transactable does not exist.",
            "transactable_type.required" => "The transactable type is required.",
            "transactable_type.morph_exists" => "The selected transactable type is invalid.",
            "narration.required" => "The narration is required.",
            "narration.max" => "The narration may not be greater than 255 characters.",
            "entry_type.required" => "The entry type is required.",
        ];
    }
}
