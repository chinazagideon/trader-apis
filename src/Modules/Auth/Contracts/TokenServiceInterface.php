<?php

namespace App\Modules\Auth\Contracts;

use App\Core\Http\ServiceResponse;
use App\Modules\User\Database\Models\User;

interface TokenServiceInterface
{
    /**
     * Generate access token
     */
    public function generateAccessToken(User $user, string $guard = 'sanctum'): ServiceResponse;

    /**
     * Generate refresh token
     */
    public function generateRefreshToken(User $user): ServiceResponse;

    /**
     * Revoke all user tokens
     */
    public function revokeAllTokens(User $user): ServiceResponse;

    /**
     * Revoke specific token
     */
    public function revokeToken(string $token): ServiceResponse;

    /**
     * Validate token
     */
    public function validateToken(string $token, string $guard = 'sanctum'): ServiceResponse;

    /**
     * Get token info
     */
    public function getTokenInfo(string $token): ServiceResponse;
}
