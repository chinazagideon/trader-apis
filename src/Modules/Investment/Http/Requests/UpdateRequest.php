<?php

namespace App\Modules\Investment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Core\Rules\AuthUser;
class UpdateRequest extends FormRequest
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
            'amount' => 'required|numeric|min:0.01',
            'status' => 'sometimes|string|in:pending,cancelled,running,completed',
            'start_date' => 'sometimes|date',
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
            'user_id.invalid' => 'You are not authorized to update this resource.',
            'pricing_id.exists' => 'The selected pricing plan does not exist.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be at least 0.01.',
            'status.in' => 'The status must be one of: pending, cancelled, running, completed.',
            'end_date.after' => 'The end date must be after the start date.',
            'notes.max' => 'The notes may not be greater than 500 characters.',
            'type.string' => 'The type must be a string.',
            'risk.string' => 'The risk must be a string.',
            'name.string' => 'The name must be a string.',
        ];
    }
}

