<?php

namespace App\Modules\User\Http\Requests;

use App\Modules\User\Database\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ShowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Uses the UserPolicy to check if user can view this specific user.
     */
    public function authorize(): bool
    {
        $userId = $this->route('id');

        if (!$userId) {
            return false;
        }

        $user = User::find($userId);

        if (!$user) {
            return false;
        }

        return $this->user()->can('view', $user);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // No validation rules needed for show operation
            // The ID is validated by the route parameter
        ];
    }
}
