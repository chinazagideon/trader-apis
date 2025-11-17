<?php

namespace App\Modules\Client\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\User\Enums\RolesEnum;

class ClientActivateClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'api_key' => 'required|string|max:255|exists:clients,api_key',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'api_key.required' => 'The API key field is required.',
            'api_key.exists' => 'The API key does not exist',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    public function prepareForValidation()
    {
        $this->merge([
            'api_key' => $this->header('X-Client-Api-Key', $this->input('api_key')),
        ]);
    }
}
