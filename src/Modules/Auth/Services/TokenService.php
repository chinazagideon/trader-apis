<?php

namespace App\Modules\Auth\Services;

use App\Core\Exceptions\NotFoundException;
use App\Core\Http\ServiceResponse;
use App\Core\Services\BaseService;
use App\Modules\Auth\Contracts\TokenServiceInterface;
use App\Modules\Auth\Database\Models\AuthToken;
use App\Modules\User\Database\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TokenService extends BaseService implements TokenServiceInterface
{
    protected string $serviceName = 'TokenService';

    /**
     * Generate access token
     *
     * @param User $user
     * @param string $guard
     * @return ServiceResponse
     */
    public function generateAccessToken(User $user, string $guard = 'sanctum'): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($user, $guard) {
            if ($guard === 'sanctum') {
                // Use Sanctum's built-in token creation
                $token = $user->createToken('auth-token');

                return ServiceResponse::success([
                    'token' => $token->plainTextToken,
                    'type' => 'Bearer',
                    'expires_at' => $token->accessToken->expires_at,
                ], 'Access token generated successfully');
            }

            // Fallback to custom token creation
            $token = $this->createToken($user, 'access', $guard);

            return ServiceResponse::success([
                'token' => $token->token_hash,
                'type' => 'Bearer',
                'expires_at' => $token->expires_at,
            ], 'Access token generated successfully');
        }, 'generate access token');
    }

    /**
     * Generate refresh token
     *
     * @param User $user
     * @return ServiceResponse
     */
    public function generateRefreshToken(User $user): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($user) {
            $token = $this->createToken($user, 'refresh', 'jwt', now()->addDays(30));

            return ServiceResponse::success([
                'refresh_token' => $token->token_hash,
                'expires_at' => $token->expires_at,
            ], 'Refresh token generated successfully');
        }, 'generate refresh token');
    }

    /**
     * Revoke all tokens
     *
     * @param User $user
     * @return ServiceResponse
     */
    public function revokeAllTokens(User $user): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($user) {
            // Revoke Sanctum tokens
            $sanctumTokens = $user->tokens();
            $sanctumCount = $sanctumTokens->count();
            $sanctumTokens->delete();

            // Revoke custom AuthToken tokens
            $authTokenCount = AuthToken::where('user_id', $user->id)
                ->where('expires_at', '>', now())
                ->update(['expires_at' => now()->subMinute()]);

            $totalRevoked = $sanctumCount + $authTokenCount;

            $this->log('All tokens revoked', [
                'user_id' => $user->id,
                'sanctum_count' => $sanctumCount,
                'auth_token_count' => $authTokenCount,
                'total' => $totalRevoked
            ]);

            return ServiceResponse::success(['revoked_count' => $totalRevoked], 'All tokens revoked successfully');
        }, 'revoke all tokens');
    }

    /**
     * Revoke specific token
     *
     * @param string $token
     * @return ServiceResponse
     */
    public function revokeToken(string $token): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($token) {
            $authToken = AuthToken::where('token_hash', $token)->first();

            if (!$authToken) {
                throw new NotFoundException('Token not found');
            }

            $authToken->update(['expires_at' => now()->subMinute()]);

            $this->log('Token revoked', ['token_id' => $authToken->id]);

            return ServiceResponse::success(null, 'Token revoked successfully');
        }, 'revoke token');
    }

    /**
     * Validate token
     *
     * @param string $token
     * @param string $guard
     * @return ServiceResponse
     */
    public function validateToken(string $token, string $guard = 'sanctum'): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($token, $guard) {
            $authToken = AuthToken::where('token_hash', $token)
                ->where('token_type', $guard === 'sanctum' ? 'sanctum' : 'access')
                ->first();

            if (!$authToken) {
                throw new NotFoundException('Token not found');
            }

            if (!$authToken->isValid()) {
                throw new \App\Core\Exceptions\BusinessLogicException('Token is expired or invalid');
            }

            // Update last used timestamp
            $authToken->updateLastUsed();

            return ServiceResponse::success([
                'user' => $authToken->user,
                'token_info' => [
                    'type' => $authToken->token_type,
                    'expires_at' => $authToken->expires_at,
                    'last_used_at' => $authToken->last_used_at,
                ],
            ], 'Token is valid');
        }, 'validate token');
    }

    /**
     * Get token info
     *
     * @param string $token
     * @return ServiceResponse
     */
    public function getTokenInfo(string $token): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($token) {
            $authToken = AuthToken::where('token_hash', $token)->first();

            if (!$authToken) {
                throw new NotFoundException('Token not found');
            }

            return ServiceResponse::success([
                'id' => $authToken->id,
                'type' => $authToken->token_type,
                'expires_at' => $authToken->expires_at,
                'last_used_at' => $authToken->last_used_at,
                'device_info' => $authToken->device_info,
                'ip_address' => $authToken->ip_address,
                'is_valid' => $authToken->isValid(),
                'is_expired' => $authToken->isExpired(),
            ], 'Token information retrieved successfully');
        }, 'get token info');
    }

    /**
     * Create token
     *
     * @param User $user
     * @param string $type
     * @param string $guard
     * @param mixed $expiresAt
     * @return AuthToken
     */
    private function createToken(User $user, string $type, string $guard, $expiresAt = null): AuthToken
    {
        $tokenValue = Str::random(64);
        $expiresAt = $expiresAt ?? now()->addHours(24);

        return AuthToken::create([
            'user_id' => $user->id,
            'token_type' => $type,
            'token_hash' => Hash::make($tokenValue),
            'expires_at' => $expiresAt,
            'device_info' => request()->header('User-Agent'),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
    }
}
