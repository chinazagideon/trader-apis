<?php

namespace App\Core\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Closure;
use Illuminate\Support\Facades\Auth;
use App\Modules\User\Enums\RolesEnum;

class AuthUser implements ValidationRule
{
    protected string $message = 'You are not authorized to access this resource. invalid :attribute.';
    /**
     * validate authenticated user
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Auth::user();
        if($user->role_id == RolesEnum::ADMIN->value) {
            return;
        }

        if ($value !== $user->id) {
            $fail(str_replace(':attribute', $attribute, $this->message));
        }

    }
}
