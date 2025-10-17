<?php

namespace App\Modules\Swap\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SwapIndexRequest extends FormRequest
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
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1',
            'user_id' => 'sometimes|integer|min:1|exists:users,id',
            'from_currency_id' => 'sometimes|integer|min:1|exists:currencies,id',
            'to_currency_id' => 'sometimes|integer|min:1|exists:currencies,id',
            'status' => 'sometimes|string|in:pending,cancelled,completed',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'notes' => 'sometimes|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'page.integer' => 'Page must be an integer',
            'page.min' => 'Page must be at least 1',
            'per_page.integer' => 'Per page must be an integer',
            'per_page.min' => 'Per page must be at least 1',
            'user_id.exists' => 'The selected user does not exist',
            'from_currency_id.exists' => 'The selected from currency does not exist',
            'to_currency_id.exists' => 'The selected to currency does not exist',
            'status.in' => 'The status must be one of: pending, cancelled, completed',
            'start_date.date' => 'The start date must be a valid date',
            'end_date.date' => 'The end date must be a valid date',
            'notes.max' => 'The notes may not be greater than 500 characters',
        ];
    }
}
