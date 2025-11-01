<?php

namespace App\Modules\Funding\Rules;

use App\Modules\Funding\Enums\FundingType;
use Illuminate\Contracts\Validation\ValidationRule;
use Closure;

class IsValidFundingType implements ValidationRule {
    /**
     * The allowed funding types.
     *
     * @var array
     */
    protected array $allowedTypes;

    public function __construct()
    {
        $this->allowedTypes = array_map(static function (FundingType $type): string {
            return $type->value;
        }, FundingType::cases());
    }

    /**
     * Validate the funding type.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!in_array($value, $this->allowedTypes)) {
            $fail('The :attribute must be a valid funding type: ' . implode(', ', $this->allowedTypes));
        }
    }

}
