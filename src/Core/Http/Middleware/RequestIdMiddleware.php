<?php

namespace App\Core\Http\Middleware;

use App\Core\Services\LoggingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to add request ID for log correlation and log request/response details
 */
class RequestIdMiddleware
{
    protected LoggingService $logger;

    public function __construct(LoggingService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate or use existing request ID
        $requestId = $request->header('X-Request-ID') ?: Str::uuid()->toString();

        // Add request ID to request headers
        $request->headers->set('X-Request-ID', $requestId);

        // Log incoming request
        $this->logRequest($request, $requestId);

        // Process the request
        $response = $next($request);

        // Add request ID to response headers
        $response->headers->set('X-Request-ID', $requestId);

        // Log response
        $this->logResponse($request, $response, $requestId);

        return $response;
    }

    /**
     * Log incoming request details
     */
    protected function logRequest(Request $request, string $requestId): void
    {
        if (!$this->logger->isEnabled('request_context')) {
            return;
        }

        $this->logger->logBusinessLogic(
            'RequestMiddleware',
            'incoming_request',
            'Incoming HTTP request',
            [
                'request_id' => $requestId,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'path' => $request->path(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'content_type' => $request->header('Content-Type'),
                'content_length' => $request->header('Content-Length'),
                'has_data' => !empty($request->all()),
                'data_keys' => array_keys($request->all()),
                'route_name' => $request->route()?->getName(),
                'route_parameters' => $request->route()?->parameters(),
            ]
        );
    }

    /**
     * Log response details
     */
    protected function logResponse(Request $request, Response $response, string $requestId): void
    {
        if (!$this->logger->isEnabled('request_context')) {
            return;
        }

        $isError = $response->getStatusCode() >= 400;
        $logLevel = $isError ? 'error' : 'info';

        $this->logger->logBusinessLogic(
            'RequestMiddleware',
            'outgoing_response',
            $isError ? 'HTTP request failed' : 'HTTP request completed',
            [
                'request_id' => $requestId,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'status_code' => $response->getStatusCode(),
                'is_error' => $isError,
                'response_size' => strlen($response->getContent()),
                'execution_time' => microtime(true) - LARAVEL_START,
            ]
        );
    }
}
