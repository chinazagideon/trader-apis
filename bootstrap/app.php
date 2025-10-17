<?php

use App\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \App\Core\Http\Middleware\RequestIdMiddleware::class,
        ]);
        // Register your custom middleware
        // $middleware->alias([
        //     'enforce.ownership' => \App\Core\Http\Middleware\EnforceOwnership::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle custom application exceptions
        $exceptions->render(function (\App\Core\Exceptions\AppException $e, $request) {
            // Log the exception with detailed context
            \Illuminate\Support\Facades\Log::error('[AppException] ' . $e->getMessage(), [
                'exception_class' => get_class($e),
                'error_code' => $e->getErrorCode(),
                'http_status' => $e->getHttpStatusCode(),
                'context' => $e->getContext(),
                'request_id' => $request->header('X-Request-ID'),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => auth()->check() ? auth()->id() : null,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_data' => $request->all(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            // Always return JSON for API routes, or when JSON is expected
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error_code' => $e->getErrorCode(),
                    'errors' => $e instanceof \App\Core\Exceptions\ValidationException ? $e->getErrors() : null,
                ], $e->getHttpStatusCode());
            }
        });

        // Handle authentication exceptions
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            // Log authentication failures
            \Illuminate\Support\Facades\Log::warning('[AuthenticationException] Authentication required', [
                'request_id' => $request->header('X-Request-ID'),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'guards' => $e->guards(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Always return JSON for API routes, or when JSON is expected
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required',
                    'error_code' => 'UNAUTHENTICATED',
                    'errors' => null,
                ], 401);
            }
        });

        // Handle authorization exceptions
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            // Log authorization failures
            \Illuminate\Support\Facades\Log::warning('[AuthorizationException] Insufficient permissions', [
                'request_id' => $request->header('X-Request-ID'),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => auth()->check() ? auth()->id() : null,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'ability' => method_exists($e, 'ability') ? $e->ability() : null,
                'arguments' => method_exists($e, 'arguments') ? $e->arguments() : null,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Always return JSON for API routes, or when JSON is expected
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient permissions',
                    'error_code' => 'UNAUTHORIZED',
                    'errors' => null,
                ], 403);
            }
        });

        // Handle validation exceptions
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            // Log validation failures with detailed context
            \Illuminate\Support\Facades\Log::warning('[ValidationException] Validation failed', [
                'request_id' => $request->header('X-Request-ID'),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => auth()->check() ? auth()->id() : null,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'validation_errors' => $e->errors(),
                'failed_fields' => array_keys($e->errors()),
                'error_count' => count($e->errors()),
                'request_data' => $request->all(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Always return JSON for API routes, or when JSON is expected
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'error_code' => 'VALIDATION_ERROR',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Handle general exceptions
        $exceptions->render(function (\Exception $e, $request) {
            // Log unexpected exceptions
            \Illuminate\Support\Facades\Log::error('[UnexpectedException] ' . $e->getMessage(), [
                'exception_class' => get_class($e),
                'error_code' => $e->getCode(),
                'request_id' => $request->header('X-Request-ID'),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => auth()->check() ? auth()->id() : null,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_data' => $request->all(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            // Always return JSON for API routes, or when JSON is expected
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred',
                    'error_code' => 'INTERNAL_ERROR',
                    'errors' => null,
                ], 500);
            }
        });
    })->create();
