<?php

namespace App\Modules\Auth\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordRule implements ValidationRule
{
    protected int $minLength;
    protected bool $requireUppercase;
    protected bool $requireLowercase;
    protected bool $requireNumbers;
    protected bool $requireSpecialChars;

    public function __construct(
        int $minLength = 8,
        bool $requireUppercase = true,
        bool $requireLowercase = true,
        bool $requireNumbers = true,
        bool $requireSpecialChars = true
    ) {
        $this->minLength = $minLength;
        $this->requireUppercase = $requireUppercase;
        $this->requireLowercase = $requireLowercase;
        $this->requireNumbers = $requireNumbers;
        $this->requireSpecialChars = $requireSpecialChars;
    }
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        $errors = [];

        // Minimum length
        if (strlen($value) < $this->minLength) {
            $fail("The password must be at least {$this->minLength} characters.");
            return;
        }

        // Maximum length (prevent DoS)
        if (strlen($value) > 128) {
            $fail('The password must not exceed 128 characters.');
            return;
        }

        // Check for uppercase (only if required)
        if ($this->requireUppercase && !preg_match('/[A-Z]/', $value)) {
            $errors[] = 'one uppercase letter';
        }

        // Check for lowercase (only if required)
        if ($this->requireLowercase && !preg_match('/[a-z]/', $value)) {
            $errors[] = 'one lowercase letter';
        }

        // Check for number (only if required)
        if ($this->requireNumbers && !preg_match('/[0-9]/', $value)) {
            $errors[] = 'one number';
        }

        // Check for special character (only if required)
        if ($this->requireSpecialChars && !preg_match('/[@$!%*?&]/', $value)) {
            $errors[] = 'one special character (@$!%*?&)';
        }

        // Check for common passwords
        if ($this->isCommonPassword($value)) {
            $fail('The password is too common. Please choose a more secure password.');
            return;
        }

        // Check for sequential patterns
        if ($this->hasSequentialPattern($value)) {
            $fail('The password contains sequential characters. Please use a more random password.');
            return;
        }

        // Provide specific feedback
        if (!empty($errors)) {
            $fail('The password must contain ' . implode(', ', $errors) . '.');
        }
    }
    /**
     * Check if password is in common passwords list
     */
    private function isCommonPassword(string $password): bool
    {
        $common = [
            'password',
            'password123',
            '12345678',
            'qwerty123',
            'admin123',
            'welcome123',
            'letmein',
            '123456789'
        ];

        return in_array(strtolower($password), $common);
    }

    /**
     * Check for sequential or repeating patterns
     */
    private function hasSequentialPattern(string $password): bool
    {
        // Check for sequential numbers (e.g., 12345678)
        if (preg_match('/012|123|234|345|456|567|678|789/', $password)) {
            return true;
        }

        // Check for sequential letters (e.g., abcdefgh)
        if (preg_match('/abc|bcd|cde|def|efg|fgh|ghi|hij|ijk|jkl|klm|lmn|mno|nop|opq|pqr|qrs|rst|stu|tuv|uvw|vwx|wxy|xyz/i', $password)) {
            return true;
        }

        // Check for repeating characters (e.g., aaaaaaaa)
        if (preg_match('/(.)\1{4,}/', $password)) {
            return true;
        }

        return false;
    }
}
