<?php

namespace App\Modules\Market\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarketIndexRequest extends FormRequest
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
