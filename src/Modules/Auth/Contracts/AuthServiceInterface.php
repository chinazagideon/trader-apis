<?php

namespace App\Modules\Auth\Contracts;

use App\Core\Http\ServiceResponse;
use App\Modules\User\Database\Models\User;
use App\Modules\Auth\Http\Requests\RegisterRequest;

interface AuthServiceInterface
{
    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): ServiceResponse;

    /**
     * Login user with credentials
     */
    public function login(array $credentials, string $guard = 'sanctum'): ServiceResponse;

    /**
     * Logout user
     */
    public function logout(User $user, string $guard = 'sanctum'): ServiceResponse;

    /**
     * Refresh user token
     */
    public function refreshToken(User $user, string $guard = 'jwt'): ServiceResponse;

    /**
     * Get authenticated user
     */
    public function getAuthenticatedUser(string $guard = 'sanctum'): ServiceResponse;

    /**
     * Verify user email
     */
    public function verifyEmail(string $token): ServiceResponse;

    /**
     * Send password reset link
     */
    public function sendPasswordResetLink(string $email): ServiceResponse;

    /**
     * Reset user password
     */
    public function resetPassword(array $data): ServiceResponse;

    /**
     * Change user password
     */
    public function changePassword(User $user, array $data): ServiceResponse;
}
