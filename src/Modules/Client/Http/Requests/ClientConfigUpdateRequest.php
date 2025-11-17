<?php

namespace App\Modules\Client\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\User\Enums\RolesEnum;

class ClientConfigUpdateRequest extends FormRequest
{
    /**
     * determine if the user is authorized to make this request
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole(RolesEnum::SUPER_ADMIN->value);
    }

    /**
     * get validation rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'api_key' => 'sometimes|string',
            'config' => 'required|array',
        ];
    }



    /**
     * prepare for validation
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'api_key' => $this->header('X-Client-Api-Key') ?? $this->input('api_key'),
        ]);
    }

    /**
     * get validation messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'api_key.required' => 'The api key field is required.',
            'api_key.string' => 'The api key field must be a string.',
            'config.required' => 'The config field is required.',
            'config.array' => 'The config field must be an array.',
        ];
    }
}
