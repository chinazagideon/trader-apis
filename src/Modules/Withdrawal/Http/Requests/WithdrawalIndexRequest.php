<?php

namespace App\Modules\Withdrawal\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Withdrawal\Database\Models\Withdrawal;

class WithdrawalIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Withdrawal::class);
    }
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Get the validation messages that apply to the request.
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        $this->merge([]);
    }
}
