<?php

namespace App\Modules\Auth\Services;

use App\Core\Exceptions\NotFoundException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\ServiceResponse;
use App\Core\Services\BaseService;
use App\Core\Services\EventDispatcher;
use App\Modules\Auth\Contracts\AuthServiceInterface;
use App\Modules\Auth\Contracts\PasswordResetInterface;
use App\Modules\Auth\Contracts\TokenServiceInterface;
use App\Modules\Auth\Http\Requests\RegisterRequest;
use App\Modules\User\Contracts\UserServiceInterface;
use App\Modules\User\Database\Models\User;
use App\Modules\User\Events\UserWasCreatedEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Modules\Auth\Notifications\VerificationEmail;

class AuthService extends BaseService implements AuthServiceInterface
{
    protected string $serviceName = 'AuthService';

    public function __construct(
        private UserServiceInterface $userService,
        private TokenServiceInterface $tokenService,
        private PasswordResetInterface $passwordResetService,
        private EventDispatcher $eventDispatcher,
    ) {
        parent::__construct($userService);
    }

    /**
     * Register a new user
     *
     * @param array $data
     * @return ServiceResponse
     */
    public function register(RegisterRequest $request): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($request) {
            // Validate registration data
            $validated = $this->validateRegistrationData($request->validated());

            // Store plain password before it gets hashed
            $plainPassword = $validated['password'];
            $name = $this->prepareName($validated);
            $validated['name'] = $name;

            // Create user
            $userResponse = $this->userService->create($validated);

            if (!$userResponse->isSuccess()) {
                throw new \App\Core\Exceptions\ServiceException('Failed to create user during registration');
            }

            $user = $userResponse->getData();

            // Generate email verification token
            // $verificationToken = Str::random(64);
            // $user->email_verification_token = $verificationToken;
            // $user->save();
            // Send verification email (placeholder)
            // $this->sendVerificationEmail($user, $verificationToken);


            // Automatically log in the user after registration
            $loginResponse = $this->login([
                'email' => $user->email,
                'password' => $plainPassword
            ], 'sanctum');

            if (!$loginResponse->isSuccess()) {
                throw new \App\Core\Exceptions\ServiceException('Failed to login user after registration');
            }

            // Return the login response (user + token)
            return $loginResponse;

        }, 'register user');
    }

    /**
     * Login user
     *
     * @param array $credentials
     * @param string $guard
     * @return ServiceResponse
     */
    public function login(array $credentials, string $guard = 'sanctum'): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($credentials, $guard) {
            // Validate login credentials
            $this->validateLoginCredentials($credentials);

            // Find user by email
            $user = User::where('email', $credentials['email'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                throw new \App\Core\Exceptions\BusinessLogicException('Invalid credentials');
            }

            if (!$user->is_active) {
                throw new \App\Core\Exceptions\BusinessLogicException('Account is deactivated');
            }

            // Generate tokens
            $tokenResponse = $this->tokenService->generateAccessToken($user, $guard);

            if (!$tokenResponse->isSuccess()) {
                throw new \App\Core\Exceptions\ServiceException('Failed to generate access token');
            }

            $this->log('User logged in successfully', ['user_id' => $user->id, 'guard' => $guard]);

            return ServiceResponse::success([
                'user' => $user,
                'token' => $tokenResponse->getData(),
            ], 'Login successful');
        }, 'login user');
    }

    /**
     * Logout user
     *
     * @param User $user
     * @param string $guard
     * @return ServiceResponse
     */
    public function logout(User $user, string $guard = 'sanctum'): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($user, $guard) {
            // Revoke all user tokens
            $this->tokenService->revokeAllTokens($user);

            $this->log('User logged out successfully', ['user_id' => $user->id, 'guard' => $guard]);

            return ServiceResponse::success(null, 'Logout successful');
        }, 'logout user');
    }

    /**
     * Refresh token
     *
     * @param User $user
     * @param string $guard
     * @return ServiceResponse
     */
    public function refreshToken(User $user, string $guard = 'jwt'): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($user, $guard) {
            // Revoke old tokens
            $this->tokenService->revokeAllTokens($user);

            // Generate new tokens
            $tokenResponse = $this->tokenService->generateAccessToken($user, $guard);

            if (!$tokenResponse->isSuccess()) {
                throw new \App\Core\Exceptions\ServiceException('Failed to refresh token');
            }

            return ServiceResponse::success($tokenResponse->getData(), 'Token refreshed successfully');
        }, 'refresh token');
    }

    /**
     * Get authenticated user
     *
     * @param string $guard
     * @return ServiceResponse
     */
    public function getAuthenticatedUser(string $guard = 'sanctum'): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($guard) {
            $user = Auth::guard($guard)->user();

            if (!$user) {
                throw new NotFoundException('User not authenticated');
            }

            return ServiceResponse::success($user, 'User retrieved successfully');
        }, 'get authenticated user');
    }

    /**
     * verify email
     *
     * @param string $token
     * @return ServiceResponse
     */
    public function verifyEmail(string $token): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($token) {
            $user = User::where('email_verification_token', $token)->first();

            if (!$user) {
                throw new NotFoundException('Invalid verification token');
            }

            if ($user->email_verified_at) {
                throw new \App\Core\Exceptions\BusinessLogicException('Email already verified');
            }

            $user->email_verified_at = now();
            $user->email_verification_token = null;
            $user->save();

            $this->log('Email verified successfully', ['user_id' => $user->id]);

            return ServiceResponse::success($user, 'Email verified successfully');
        }, 'verify email');
    }

    /**
     * Send password reset link
     *
     * @param string $email
     * @return ServiceResponse
     */
    public function sendPasswordResetLink(string $email): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($email) {
            $response = $this->passwordResetService->sendResetLink($email);

            if (!$response->isSuccess()) {
                throw new \App\Core\Exceptions\ServiceException('Failed to send password reset link');
            }

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
            $response = $this->passwordResetService->resetPassword($data);
            if (!$response->isSuccess()) {
                throw new \App\Core\Exceptions\ServiceException($response->getMessage());
            }

            return ServiceResponse::success(null, 'Password reset successfully');
        }, 'reset password');
    }

    /**
     * Change password
     *
     * @param User $user
     * @param array $data
     * @return ServiceResponse
     */
    public function changePassword(User $user, array $data): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($user, $data) {
            $this->validatePasswordChangeData($data);

            // Verify current password
            if (!Hash::check($data['current_password'], $user->password)) {
                throw new \App\Core\Exceptions\BusinessLogicException('Current password is incorrect');
            }

            // Update password
            $user->password = Hash::make($data['new_password']);
            $user->save();

            // Revoke all tokens for security
            $this->tokenService->revokeAllTokens($user);

            $this->log('Password changed successfully', ['user_id' => $user->id]);

            return ServiceResponse::success(null, 'Password changed successfully');
        }, 'change password');
    }

    /**
     * DEPRECATED: Validate registration data
     * Use Request Validator instead
     *
     * @param array $data
     * @return array
     */
    private function validateRegistrationData(array $data): array
    {
        $validated = $this->validateData($data, [
            'name' => 'nullable|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'referral_code' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
        ]);

        // Remove password_confirmation as it's only needed for validation
        unset($validated['password_confirmation']);

        return $validated;
    }

    /**
     * Validate login credentials
     *
     * @param array $credentials
     * @return void
     */
    private function validateLoginCredentials(array $credentials): void
    {
        if (empty($credentials['email']) || empty($credentials['password'])) {
            throw new ValidationException('Email and password are required');
        }
    }

    /**
     * Validate password change data
     *
     * @param array $data
     * @return void
     */
    private function validatePasswordChangeData(array $data): void
    {
        $this->validateData($data, [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);
    }

    /**
     * Send verification email
     *
     * @param User $user
     * @param string $token
     * @return void
     */
    private function sendVerificationEmail(User $user, string $token): void
    {
        // Placeholder for email sending logic
        // In a real implementation, you would use Laravel's Mail facade
        $this->log('Verification email sent', [
            'user_id' => $user->id,
            'email' => $user->email,
            'token' => $token,
        ]);

        // Mail::to($user->email)->send(new VerificationEmail($user, $token));
    }

    /**
     * Prepare name
     *
     * @param array $data
     * @return string
     */
    private function prepareName(array $data): string
    {
        return $data['first_name'] . ' ' . $data['last_name'];
    }

}
