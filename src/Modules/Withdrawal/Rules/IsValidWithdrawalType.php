<?php

namespace App\Modules\Withdrawal\Rules;

use App\Modules\Withdrawal\Enums\WithrawalTypes;
use Illuminate\Contracts\Validation\ValidationRule;
use Closure;

class IsValidWithdrawalType implements ValidationRule {
    /**
     * The allowed withdrawal types.
     *
     * @var array
     */
    protected array $allowedTypes;

    public function __construct()
    {
        $this->allowedTypes = array_map(static function (WithrawalTypes $type): string {
            return $type->value;
        }, WithrawalTypes::cases());
    }

    /**
     * Validate the withdrawal type.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!in_array($value, $this->allowedTypes)) {
            $fail('The :attribute must be a valid withdrawal type: ' . implode(', ', $this->allowedTypes));
        }
    }

}
