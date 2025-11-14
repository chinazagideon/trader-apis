<?php

namespace App\Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Payment\Enums\PaymentStatusEnum;

class PaymentProcessorStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to update the status of a payment processor.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('admin.all');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'required|string|max:255|exists:payment_processors,uuid',
            'status' => 'required|string|in:' . implode(',', array_column(PaymentStatusEnum::cases(), 'value')),
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'uuid.required' => 'The uuid is required.',
            'uuid.string' => 'The uuid must be a string.',
            'uuid.max' => 'The uuid must be less than 255 characters.',
            'uuid.exists' => 'The uuid does not exist.',
        ];
    }
}
