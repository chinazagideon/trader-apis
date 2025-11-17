<?php

namespace App\Modules\Client\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use App\Modules\User\Enums\RolesEnum;
class ClientCreateRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'config' => 'required|array',
        ];
    }

    /**
     * generate api key and api secret for submission
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->name)
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
            'name.required' => 'The name field is required.',
            'name.string' => 'The name field must be a string.',
            'name.max' => 'The name field must be less than 255 characters.',
            'slug.required' => 'The slug field is required.',
            'slug.string' => 'The slug field must be a string.',
            'slug.max' => 'The slug field must be less than 255 characters.',
            'config.required' => 'configuration cannot be empty',
            'config.array' => 'configuration can only be an array, with atleast boolean values of (guest_view_enabled, auth_view_enabled)'
        ];
    }
}
