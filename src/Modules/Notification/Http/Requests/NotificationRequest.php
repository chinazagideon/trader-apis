<?php

namespace App\Modules\Notification\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Notification\Rules\EntityTypeMorphRule;

class NotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $allowedTypes = config('Notification.allowed_types');

        return [
            'entity_type' => [
                'required',
                'string',
                'in:' . implode(',', array_keys($allowedTypes))
            ],
            'entity_id' => [
                'required',
                'integer',
                new EntityTypeMorphRule('entity_type', $allowedTypes)
            ],
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'entity_type.required' => 'The entity type is required.',
            'entity_type.in' => 'The selected entity type is invalid.',
            'entity_id.required' => 'The entity ID is required.',
            'entity_id.integer' => 'The entity ID must be an integer.',
            'per_page.integer' => 'The per page must be an integer.',
            'per_page.min' => 'The per page must be at least 1.',
            'per_page.max' => 'The per page may not be greater than 100.',
        ];
    }
}
