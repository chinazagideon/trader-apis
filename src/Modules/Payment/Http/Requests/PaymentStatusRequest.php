<?php

namespace App\Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Payment\Enums\PaymentStatusEnum;
class PaymentStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to update the status of a payment.
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
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'status' => 'required|string|in:' . implode(',', array_column(PaymentStatusEnum::cases(), 'value')),
            'uuid' => 'required|string|max:255|exists:payments,uuid',
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
            'status.required' => 'The status is required.',
            'status.string' => 'The status must be a string.',
            'status.in' => 'The status must be one of: pending, completed, failed.',
        ];
    }
}
