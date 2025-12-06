<?php

namespace App\Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentGatewayDestroyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('admin.all');
    }

    public function rules(): array
    {
        return [];
    }

    public function messages(): array
    {
        return [];
    }
}
