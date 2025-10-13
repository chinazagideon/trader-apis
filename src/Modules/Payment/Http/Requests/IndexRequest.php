<?php

namespace App\Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Core\Rules\MorphExistsRule;
use App\Modules\Payment\Config\Payment as ConfigPayment;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return false;
    }

    public function rules(): array
    {
        return [
            // 'id' => 'required|integer|exists:payments,id',
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
