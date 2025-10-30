<?php

namespace App\Modules\Transaction\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
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
            'transactionable_id' => 'sometimes|integer|min:1|exists:transactions,id',
            'transactionable_type' => 'required|string|in:investment,swap,funding,withdrawal',
            'status' => 'sometimes|string|in:pending,completed,failed',
            'entry_type' => 'sometimes|string|in:credit,debit',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date',
            'sort_by' => 'sometimes|string|in:created_at,updated_at',
            'sort_direction' => 'sometimes|string|in:asc,desc',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'transactionable_type.required' => 'The transactionable type is required.',
            'transactionable_type.in' => 'The selected transactionable type is invalid.',
            'status.in' => 'The status must be one of: pending, completed, failed.',
        ];
    }
}
