<?php

namespace App\Modules\Funding\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FundingIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|integer|min:1|exists:users,id',
            'status' => 'sometimes|string|in:pending,cancelled,completed',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => 'The selected user does not exist.',
            'status.in' => 'The status must be one of: pending, cancelled, completed.',
            'page.integer' => 'The page must be an integer.',
            'per_page.integer' => 'The per page must be an integer.',
        ];
    }
}
