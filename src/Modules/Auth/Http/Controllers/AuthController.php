<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Core\Controllers\BaseController;
use App\Modules\Auth\Contracts\AuthServiceInterface;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Auth\Http\Requests\RegisterRequest;
use App\Modules\Auth\Http\Requests\PasswordResetRequest;
use App\Modules\Auth\Http\Requests\ChangePasswordRequest;
use App\Modules\Auth\Http\Resources\AuthResource;
use App\Modules\Auth\Http\Resources\LoginResource;
use App\Modules\Auth\Http\Resources\RegisterResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{
    public function __construct(
        private AuthServiceInterface $authService
    ) {}

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $response = $this->authService->register($request);

        if ($response->isSuccess()) {
            // Use LoginResource to return the same format as login (user + token)
            $data = $response->getData();
            $response->setData(new LoginResource($data));
        }

        return $this->handleServiceResponse($response);
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $response = $this->authService->login(
            $request->validated(),
            $request->get('guard', 'sanctum')
        );

        if ($response->isSuccess()) {
            $data = $response->getData();
            $response->setData(new LoginResource($data));
        }

        return $this->handleServiceResponse($response);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();
        $guard = $request->get('guard', 'sanctum');

        $response = $this->authService->logout($user, $guard);
        return $this->handleServiceResponse($response);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        $guard = $request->get('guard', 'sanctum');
        $response = $this->authService->getAuthenticatedUser($guard);

        if ($response->isSuccess()) {
            $response->setData(new AuthResource($response->getData()));
        }

        return $this->handleServiceResponse($response);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = Auth::user();
        $guard = $request->get('guard', 'jwt');

        $response = $this->authService->refreshToken($user, $guard);
        return $this->handleServiceResponse($response);
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $response = $this->authService->verifyEmail($request->get('token'));

        if ($response->isSuccess()) {
            $response->setData(new AuthResource($response->getData()));
        }

        return $this->handleServiceResponse($response);
    }

    /**
     * Send password reset link
     */
    public function sendPasswordResetLink(Request $request): JsonResponse
    {
        $response = $this->authService->sendPasswordResetLink($request->get('email'));
        return $this->handleServiceResponse($response);
    }

    /**
     * Reset password
     */
    public function resetPassword(PasswordResetRequest $request): JsonResponse
    {
        $response = $this->authService->resetPassword($request->validated());
        return $this->handleServiceResponse($response);
    }

    /**
     * Change password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = Auth::user();
        $response = $this->authService->changePassword($user, $request->validated());
        return $this->handleServiceResponse($response);
    }

    /**
     * Test authentication
     */
    public function testAuth(Request $request): JsonResponse
    {
        $user = Auth::user();
        $sanctumUser = $request->user();
        $token = $request->bearerToken();

        // Try to find the token manually
        $personalAccessToken = null;
        if ($token) {
            $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        }

        return $this->successResponse([
            'authenticated' => $user !== null,
            'user' => $user,
            'sanctum_user' => $sanctumUser,
            'token' => $token,
            'personal_access_token' => $personalAccessToken ? $personalAccessToken->id : null,
        ], 'Authentication test');
    }

    /**
     * Health check
     */
    public function health(): JsonResponse
    {
        return $this->successResponse([
            'status' => 'healthy',
            'module' => 'Auth',
            'timestamp' => now(),
        ], 'Auth module health check');
    }
}
