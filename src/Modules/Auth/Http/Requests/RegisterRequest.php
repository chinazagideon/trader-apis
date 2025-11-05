<?php

namespace App\Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Auth\Rules\PasswordRule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function getConfig(): array
    {
        $return = config('Auth');
        return $return['security'];
    }
    /**
     * set validation rules
     *
     * @return array
     */
    public function rules(): array
    {
        $config = $this->getConfig();

        return [
            'name' => 'nullable|string|max:255',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'referral_code' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'password' => [
                'required',
                new PasswordRule(
                    minLength: $config['password_min_length'],
                    requireUppercase: $config['password_require_uppercase'],
                    requireLowercase: $config['require_lowercase'],
                    requireNumbers: $config['password_require_numbers'],
                    requireSpecialChars: $config['password_require_special_chars']
                ),
                'confirmed',
            ],
        ];
    }

    /**
     * set validation messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'first_name.required' => 'The first name field is required.',
            'last_name.required' => 'The last name field is required.',
            'phone.required' => 'The phone field is required.',
            'phone.string' => 'The phone field must be a string.',
            'phone.max' => 'The phone field must be less than 20 characters.',
            'phone.unique' => 'The phone is already registered.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email is already registered.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
