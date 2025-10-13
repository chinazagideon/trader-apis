<?php

namespace App\Modules\Auth\Services;

use App\Core\Exceptions\BusinessLogicException;
use App\Core\Exceptions\NotFoundException;
use App\Core\Http\ServiceResponse;
use App\Core\Services\BaseService;
use App\Modules\Auth\Contracts\PasswordResetInterface;
use App\Modules\Auth\Database\Models\PasswordReset;
use App\Modules\User\Database\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetService extends BaseService implements PasswordResetInterface
{
    protected string $serviceName = 'PasswordResetService';

    /**
     * Send password reset link
     *
     * @param string $email
     * @return ServiceResponse
     */
    public function sendResetLink(string $email): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($email) {
            $user = User::where('email', $email)->first();

            if (!$user) {
                // Don't reveal if email exists or not for security
                return ServiceResponse::success(null, 'If the email exists, a reset link has been sent');
            }

            // Clear existing reset tokens for this email
            PasswordReset::where('email', $email)->delete();

            // Create new reset token
            $token = Str::random(64);
            PasswordReset::create([
                'email' => $email,
                'token' => Hash::make($token),
                'expires_at' => now()->addHours(1), // 1 hour expiry
            ]);

            // Send reset email (placeholder)
            $this->sendResetEmail($user, $token);

            $this->log('Password reset link sent', ['email' => $email]);

            return ServiceResponse::success(null, 'Password reset link sent successfully');
        }, 'send password reset link');
    }

    /**
     * Reset password
     *
     * @param array $data
     * @return ServiceResponse
     */
    public function resetPassword(array $data): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($data) {
            $this->validateResetPasswordData($data);

            $passwordReset = PasswordReset::where('email', $data['email'])
                ->where('token', Hash::make($data['token']))
                ->valid()
                ->first();

            if (!$passwordReset) {
                throw new NotFoundException('Invalid or expired reset token');
            }

            // Find user
            $user = User::where('email', $data['email'])->first();
            if (!$user) {
                throw new NotFoundException('User not found');
            }

            // Update password
            $user->password = Hash::make($data['password']);
            $user->save();

            // Mark token as used
            $passwordReset->markAsUsed();

            // Revoke all user tokens for security
            $user->tokens()->delete();

            $this->log('Password reset successfully', ['user_id' => $user->id]);

            return ServiceResponse::success(null, 'Password reset successfully');
        }, 'reset password');
    }

    /**
     * Validate reset token
     *
     * @param string $token
     * @return ServiceResponse
     */
    public function validateResetToken(string $token): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($token) {
            $passwordReset = PasswordReset::where('token', Hash::make($token))
                ->valid()
                ->first();

            if (!$passwordReset) {
                throw new NotFoundException('Invalid or expired reset token');
            }

            return ServiceResponse::success([
                'email' => $passwordReset->email,
                'expires_at' => $passwordReset->expires_at,
            ], 'Reset token is valid');
        }, 'validate reset token');
    }

    /**
     * Clear expired tokens
     *
     * @return ServiceResponse
     */
    public function clearExpiredTokens(): ServiceResponse
    {
        return $this->executeServiceOperation(function () {
            $deletedCount = PasswordReset::expired()->delete();

            $this->log('Expired reset tokens cleared', ['count' => $deletedCount]);

            return ServiceResponse::success(['deleted_count' => $deletedCount], 'Expired tokens cleared successfully');
        }, 'clear expired tokens');
    }

    private function validateResetPasswordData(array $data): void
    {
        $this->validateData($data, [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);
    }

    /**
     * Send reset email
     *
     * @param User $user
     * @param string $token
     * @return void
     */
    private function sendResetEmail(User $user, string $token): void
    {
        // Placeholder for email sending logic
        // In a real implementation, you would use Laravel's Mail facade
        $this->log('Password reset email sent', [
            'user_id' => $user->id,
            'email' => $user->email,
            'token' => $token,
        ]);
    }
}
