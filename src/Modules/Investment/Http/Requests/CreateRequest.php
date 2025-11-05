<?php

namespace App\Modules\Investment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Core\Rules\AuthUser;
class CreateRequest extends FormRequest
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
            'user_id' => ['required', 'integer', 'min:1', 'exists:users,id', new AuthUser()],
            'pricing_id' => 'required|integer|min:1|exists:pricings,id',
            'category_id' => 'required|integer|min:1|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'status' => 'sometimes|string|in:pending,cancelled,running,completed',
            'start_date' => 'sometimes|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'notes' => 'nullable|string|max:500',
            'type' => 'string|nullable',
            'risk' => 'string|nullable',
            'name' => 'string|nullable',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'The user field is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'pricing_id.required' => 'The pricing plan field is required.',
            'pricing_id.exists' => 'The selected pricing plan does not exist.',
            'category_id.required' => 'The category field is required.',
            'category_id.exists' => 'The selected category does not exist.',
            'amount.required' => 'The amount field is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be at least 0.01.',
            'status.in' => 'The status must be one of: pending, cancelled, running, completed.',
            'start_date.after_or_equal' => 'The start date must be today or in the future.',
            'end_date.after' => 'The end date must be after the start date.',
            'notes.max' => 'The notes may not be greater than 500 characters.',
            'type.string' => 'The type must be a string.',
            'risk.string' => 'The risk must be a string.',
            'name.string' => 'The name must be a string.',
        ];
    }
}
