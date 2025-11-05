<?php

namespace App\Modules\User\Http\Requests;

use App\Modules\User\Database\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UserIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Uses the UserPolicy to check if user can view any users.
     */
    public function authorize(): bool
    {
        // Ensure user is authenticated
        if (!$this->user()) {
            return false;
        }


        return $this->user()->can('viewAny', User::class);
    }

    public function rules(): array
    {
        return [
            'user_type' => 'sometimes|string|in:individual,business',
            'referral_code' => 'sometimes|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'user_type.in' => 'The user type must be one of: individual, business.',
            'referral_code.max' => 'The referral code may not be greater than 255 characters.',
        ];
    }
}
