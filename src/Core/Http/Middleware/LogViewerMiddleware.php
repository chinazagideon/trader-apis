<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogViewerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if we're in an allowed environment
        $allowedEnvironments = config('logviewer.allowed_environments', ['local', 'development', 'dev']);

        if (!in_array(app()->environment(), $allowedEnvironments)) {
            Log::warning('[LogViewer] Access denied - Environment not allowed', [
                'environment' => app()->environment(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
            ]);

            abort(403, 'Log viewer is not available in this environment.');
        }

        // Log access attempts for security monitoring
        Log::info('[LogViewer] Access attempt', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => auth()->check() ? auth()->id() : null,
            'timestamp' => now()->toISOString(),
        ]);

        return $next($request);
    }
}
