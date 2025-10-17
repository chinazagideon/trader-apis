<?php

namespace App\Modules\Currency\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CurrencyIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
