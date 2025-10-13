<?php

namespace App\Core\Http\Middleware;

use App\Core\ModuleManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ApiGatewayMiddleware
{
    protected ModuleManager $moduleManager;

    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Extract module from route
        $module = $this->extractModuleFromRequest($request);

        if ($module) {
            // Check if module is registered and healthy
            if (!$this->moduleManager->isModuleRegistered($module)) {
                return $this->moduleNotFoundResponse($module);
            }

            $health = $this->moduleManager->getModuleHealth($module);

            if ($health['status'] !== 'healthy') {
                return $this->moduleUnhealthyResponse($module, $health);
            }

            // Add module context to request
            $request->merge(['_module' => $module]);

            // Log API Gateway activity
            $this->logApiGatewayActivity($request, $module);
        }

        return $next($request);
    }

    /**
     * Extract module name from request path
     */
    protected function extractModuleFromRequest(Request $request): ?string
    {
        $path = $request->path();
        $segments = explode('/', $path);

        // Check for API version pattern: api/v1/module/...
        if (count($segments) >= 3 && $segments[0] === 'api' && $segments[1] === 'v1') {
            return ucfirst($segments[2]);
        }

        // Check for direct module pattern: module/...
        if (count($segments) >= 1) {
            $potentialModule = ucfirst($segments[0]);

            if ($this->moduleManager->getModule($potentialModule)) {
                return $potentialModule;
            }
        }

        return null;
    }

    /**
     * Return module not found response
     */
    protected function moduleNotFoundResponse(string $module): Response
    {
        return response()->json([
            'success' => false,
            'message' => "Module '{$module}' not found or not registered",
            'error_code' => 'MODULE_NOT_FOUND',
            'available_modules' => $this->moduleManager->getRegisteredModules(),
        ], 404);
    }

    /**
     * Return module unhealthy response
     */
    protected function moduleUnhealthyResponse(string $module, array $health): Response
    {
        return response()->json([
            'success' => false,
            'message' => "Module '{$module}' is unhealthy",
            'error_code' => 'MODULE_UNHEALTHY',
            'health_status' => $health,
        ], 503);
    }

    /**
     * Log API Gateway activity
     */
    protected function logApiGatewayActivity(Request $request, string $module): void
    {
        Log::info('API Gateway Request', [
            'module' => $module,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
        ]);
    }
}
