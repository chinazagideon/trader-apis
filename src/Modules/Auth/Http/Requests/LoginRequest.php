<?php

namespace App\Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
            'guard' => 'sometimes|string|in:sanctum,jwt,api',
            'api_key' => 'sometimes|string|max:255|exists:clients,api_key',

        ];
    }


    /**
     * add api key from header or input
     *
     * @return void
     */
    public function prepareForValidation()
    {
        $this->merge([
            'api_key' => $this->header('X-Client-Api-Key', $this->input('api_key')),
        ]);
    }

    /**
     * validation messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'password.required' => 'The password field is required.',
            'guard.in' => 'The guard must be one of: sanctum, jwt, api.',
            'api_key.sometimes' => 'The API key field is required.',
            'api_key.exists' => 'The API key does not exist',
        ];
    }
}
