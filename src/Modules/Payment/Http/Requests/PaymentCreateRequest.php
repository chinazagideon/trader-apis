<?php

namespace App\Modules\Payment\Http\Requests;

use App\Core\Rules\MorphExistsRule;
use Illuminate\Foundation\Http\FormRequest;

class PaymentCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {

        $allowedTypes = config('Payment.allowed_types');
        return [
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string|in:bank,card,crypto',
            'payable_id' => [
                'required',
                'integer',
                new MorphExistsRule('payable_type', $allowedTypes)
            ],
            "payable_type" => ["required", "string",  'in:' . implode(',', array_keys($allowedTypes))],
            'status' => 'required|string|in:pending,completed,failed',
            'currency_id' => 'required|integer|min:1|exists:currencies,id',
            'uuid' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'The amount field is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be at least 0.01.',
            'method.required' => 'The method field is required.',
            'method.string' => 'The method must be a string.',
            'method.in' => 'The method must be one of: bank, card, mobile_money, paypal, stripe.',
            'payable_id.required' => 'The payable id field is required.',
            'payable_id.integer' => 'The payable id must be an integer.',
            'payable_id.min' => 'The payable id must be at least 1.',
            'payable_id.exists' => 'The selected payable does not exist.',
            'payable_type.required' => 'The payable type field is required.',
            'payable_type.string' => 'The payable type must be a string.',
            'payable_type.in' => 'The payable type must be one of: user, investment, transaction.',
            'status.required' => 'The status field is required.',
            'status.string' => 'The status must be a string.',
            'status.in' => 'The status must be one of: pending, completed, failed.',
            'currency_id.required' => 'The currency id field is required.',
            'currency_id.integer' => 'The currency id must be an integer.',
            'currency_id.min' => 'The currency id must be at least 1.',
            'currency_id.exists' => 'The selected currency does not exist.',
        ];
    }
}
