<?php

namespace App\Modules\Client\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Core\Exceptions\AppException;
use App\Core\Traits\HasClientApp;
use App\Modules\Client\Contracts\ClientRepositoryContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class IdentifyClientMiddleware
{
    use HasClientApp;

    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Resolve client id (JWT first, then headers)
        $clientId = $this->resolveClientIdFromRequest($request);

        if (!$clientId) {
            throw new AppException(
                'Client identification required. Provide X-Client-Api-Key, X-Client-Slug header, or valid JWT token with client_id claim.',
                401,
                'UNAUTHORIZED'
            );
        }

        // Set context for this request
        $this->setClientContext($clientId);

        return $next($request);
    }

    /**
     * Resolve client ID from request (JWT first, then headers)
     *
     * @param Request $request
     * @return int|null
     */
    private function resolveClientIdFromRequest(Request $request): ?int
    {
        // Priority 1: Check JWT token for client_id claim
        if ($token = $request->bearerToken()) {
            try {
                $payload = \Tymon\JWTAuth\Facades\JWTAuth::setToken($token)->getPayload();
                $clientId = $payload->get('client_id');

                if ($clientId) {
                    // Verify client exists and is active
                    $client = $this->getClientById($clientId);
                    if ($client) {
                        return $clientId;
                    }
                }
            } catch (\Exception $e) {
                // JWT invalid or expired, fall through to header check
                Log::debug('JWT validation failed in IdentifyClientMiddleware', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Priority 2: Check headers (API Key or Slug)
        $apiKey = $this->extractApiKeyFromRequest($request);
        $slug = $this->extractSlugFromRequest($request);

        if (!$apiKey && !$slug) {
            // Try extracting slug from domain as fallback
            $slug = $this->extractSlugFromDomain($request);
        }

        if ($apiKey || $slug) {
            $repo = app(ClientRepositoryContract::class);

            $client = $apiKey
                ? $repo->findByApiKey($apiKey)
                : $repo->findBySlug($slug);

            // Check if this is an activate/deactivate route
            $isActivateDeactivateRoute = $this->isActivateOrDeactivateRoute(
                $request->getPathInfo() // or $request->getRequestUri()
            );

            // Check if client exists before accessing properties
            if ($client && ($client->is_active || $isActivateDeactivateRoute)) {
                return $client->id;
            }
        }

        return null;
    }

    /**
     * Get client by ID (with caching if needed)
     *
     * @param int $clientId
     * @return \App\Modules\Client\Database\Models\Client|null
     */
    private function getClientById(int $clientId): ?\App\Modules\Client\Database\Models\Client
    {
        $repo = app(ClientRepositoryContract::class);
        /** @var Model $repo = $model */
        return $repo->find($clientId);
    }

    /**
     * Extract API key from request header
     *
     * @param Request $request
     * @return string|null
     */
    private function extractApiKeyFromRequest(Request $request): ?string
    {
        $apiKey = $request->header('X-Client-Api-Key');
        return !empty($apiKey) ? $apiKey : null;
    }

    /**
     * Extract slug from request header
     *
     * @param Request $request
     * @return string|null
     */
    private function extractSlugFromRequest(Request $request): ?string
    {
        $slug = $request->header('X-Client-Slug');
        return !empty($slug) ? $slug : null;
    }

    /**
     * Extract slug from domain (subdomain)
     *
     * @param Request $request
     * @return string|null
     */
    private function extractSlugFromDomain(Request $request): ?string
    {
        $host = $request->getHost();
        $parts = explode('.', $host);

        // Skip 'localhost', '127.0.0.1', or IP addresses
        if (count($parts) <= 1 || filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }

        return $parts[0] ?? null;
    }

    /**
     * Validate if the request path is for activate or deactivate routes
     *
     * @param string $path The request path (e.g., from $request->getPathInfo() or $request->getRequestUri())
     * @return bool
     */
    private function isActivateOrDeactivateRoute(string $path): bool
    {
        // Remove query string if present
        $path = parse_url($path, PHP_URL_PATH);

        // Normalize path (remove trailing slash, ensure consistent format)
        $path = rtrim($path, '/');

        // Define the routes to check
        $activateRoute = config('Client.activate_route');
        $deactivateRoute = config('Client.deactivate_route');

        // Check if path matches either route
        return $path === $activateRoute || $path === $deactivateRoute;
    }
}
