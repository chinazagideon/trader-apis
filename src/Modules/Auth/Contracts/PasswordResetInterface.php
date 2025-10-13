<?php

namespace App\Modules\Auth\Contracts;

use App\Core\Http\ServiceResponse;

interface PasswordResetInterface
{
    /**
     * Send password reset email
     */
    public function sendResetLink(string $email): ServiceResponse;

    /**
     * Reset password with token
     */
    public function resetPassword(array $data): ServiceResponse;

    /**
     * Validate reset token
     */
    public function validateResetToken(string $token): ServiceResponse;

    /**
     * Clear expired tokens
     */
    public function clearExpiredTokens(): ServiceResponse;
}
