<?php

namespace App\Modules\Transaction\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionCategoryIndexRequest extends FormRequest
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

