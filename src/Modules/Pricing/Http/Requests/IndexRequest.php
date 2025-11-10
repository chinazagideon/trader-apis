<?php

namespace App\Modules\Pricing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
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
            "type" => 'sometimes|string|in:staking,trade,mining',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1',
        ];
    }
}
