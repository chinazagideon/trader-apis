<?php

namespace App\Core\Exceptions;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionHandler
{
    /**
     * Register all exception handlers
     */
    public static function register(Exceptions $exceptions): void
    {
        static::handleAppExceptions($exceptions);
        static::handleAuthenticationExceptions($exceptions);
        static::handleAuthorizationExceptions($exceptions);
        static::handleHttpExceptions($exceptions);
        static::handleValidationExceptions($exceptions);
        static::handleGeneralExceptions($exceptions);
    }

    /**
     * Handle custom application exceptions
     */
    protected static function handleAppExceptions(Exceptions $exceptions): void
    {
        $exceptions->render(function (AppException $e, Request $request) {
            // Log the exception with detailed context
            Log::error('[AppException] ' . $e->getMessage(), [
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
                    'errors' => $e instanceof ValidationException ? $e->getErrors() : null,
                ], $e->getHttpStatusCode());
            }
        });
    }

    /**
     * Handle authentication exceptions
     */
    protected static function handleAuthenticationExceptions(Exceptions $exceptions): void
    {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            // Log authentication failures
            Log::warning('[AuthenticationException] Authentication required', [
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
    }

    /**
     * Handle authorization exceptions
     */
    protected static function handleAuthorizationExceptions(Exceptions $exceptions): void
    {
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, Request $request) {
            // Get the actual exception message
            $message = $e->getMessage() ?: 'Insufficient permissions';

            // Log authorization failures
            Log::warning('[AuthorizationException] ' . $message, [
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
                    'message' => $message,
                    'error_code' => 'UNAUTHORIZED',
                    'errors' => null,
                ], 403);
            }
        });
    }

    /**
     * Handle HTTP exceptions (AccessDeniedHttpException, NotFoundHttpException, etc.)
     */
    protected static function handleHttpExceptions(Exceptions $exceptions): void
    {
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            $statusCode = $e->getStatusCode();
            $message = $e->getMessage();

            // Map status codes to default messages if message is empty or generic
            $defaultMessages = [
                401 => 'Authentication required',
                403 => 'This action is unauthorized',
                404 => 'Resource not found',
                405 => 'Method not allowed',
                429 => 'Too many requests',
                500 => 'Internal server error',
            ];

            // Use actual message if available and meaningful, otherwise use default based on status code
            if (empty($message) || trim($message) === '') {
                $message = $defaultMessages[$statusCode] ?? 'An error occurred';
            }

            // Map status codes to error codes
            $errorCodes = [
                401 => 'UNAUTHENTICATED',
                403 => 'FORBIDDEN',
                404 => 'NOT_FOUND',
                405 => 'METHOD_NOT_ALLOWED',
                429 => 'TOO_MANY_REQUESTS',
                500 => 'INTERNAL_ERROR',
            ];

            $errorCode = $errorCodes[$statusCode] ?? 'HTTP_ERROR';

            // Log HTTP exceptions
            Log::warning("[HttpException] {$message}", [
                'exception_class' => get_class($e),
                'status_code' => $statusCode,
                'error_code' => $errorCode,
                'request_id' => $request->header('X-Request-ID'),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => auth()->check() ? auth()->id() : null,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Always return JSON for API routes, or when JSON is expected
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error_code' => $errorCode,
                    'errors' => null,
                ], $statusCode);
            }
        });
    }

    /**
     * Handle validation exceptions
     */
    protected static function handleValidationExceptions(Exceptions $exceptions): void
    {
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            // Log validation failures with detailed context
            Log::warning('[ValidationException] Validation failed', [
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
                $errors = $e->errors();
                $message = 'Validation failed';
                if (!empty($errors)) {
                    $firstField = array_key_first($errors);
                    $message = $errors[$firstField][0] ?? $message;
                }

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error_code' => 'VALIDATION_ERROR',
                    'errors' => $errors,
                ], 422);
            }
        });
    }

    /**
     * Handle general exceptions
     */
    protected static function handleGeneralExceptions(Exceptions $exceptions): void
    {
        $exceptions->render(function (\Exception $e, Request $request) {
            // Skip if already handled by a more specific handler
            if ($e instanceof AppException ||
                $e instanceof \Illuminate\Auth\AuthenticationException ||
                $e instanceof \Illuminate\Auth\Access\AuthorizationException ||
                $e instanceof \Illuminate\Validation\ValidationException ||
                $e instanceof HttpExceptionInterface) {
                return null; // Let Laravel continue to the next handler
            }

            // Get the actual exception message
            $message = $e->getMessage();

            // Log unexpected exceptions
            Log::error('[UnexpectedException] ' . ($message ?: 'Unknown error'), [
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
                // Use the exception message if it's meaningful and not empty
                if (empty($message) || trim($message) === '') {
                    // Provide context-aware messages for specific exception types
                    if ($e instanceof \Error || $e instanceof \ParseError) {
                        $message = 'A runtime error occurred';
                    } elseif ($e instanceof \TypeError) {
                        $message = 'A type error occurred';
                    } else {
                        $message = 'An unexpected error occurred';
                    }
                }

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error_code' => 'INTERNAL_ERROR',
                    'errors' => null,
                ], 500);
            }
        });
    }
}

