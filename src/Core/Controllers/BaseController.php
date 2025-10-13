<?php

namespace App\Core\Controllers;

use App\Core\Http\ServiceResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

abstract class BaseController
{
    /**
     * Success response
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = Response::HTTP_OK
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Error response
     */
    protected function errorResponse(
        string $message = 'Error',
        mixed $errors = null,
        int $statusCode = Response::HTTP_BAD_REQUEST
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Validation error response
     */
    protected function validationErrorResponse(
        array $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return $this->errorResponse($message, $errors, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Not found response
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, null, Response::HTTP_NOT_FOUND);
    }

    /**
     * Unauthorized response
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, null, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Forbidden response
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, null, Response::HTTP_FORBIDDEN);
    }

    /**
     * Throw unauthenticated exception
     */
    protected function throwUnauthenticated(string $message = 'Authentication required'): void
    {
        throw new \App\Core\Exceptions\UnauthenticatedException($message);
    }

    /**
     * Throw unauthorized exception
     */
    protected function throwUnauthorized(string $message = 'Insufficient permissions'): void
    {
        throw new \App\Core\Exceptions\UnauthorizedException($message);
    }

    /**
     * Validate request data
     */
    protected function validateRequest(Request $request, array $rules, array $messages = []): array
    {
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Get pagination parameters from request
     */
    protected function getPaginationParams(Request $request): array
    {
        return [
            'page' => (int) $request->get('page', 1),
            'per_page' => (int) $request->get('per_page', 15),
        ];
    }

    /**
     * Get search parameters from request
     */
    protected function getSearchParams(Request $request): array
    {
        return [
            'search' => $request->get('search', ''),
            'sort_by' => $request->get('sort_by', 'id'),
            'sort_direction' => $request->get('sort_direction', 'asc'),
        ];
    }

    /**
     * Handle ServiceResponse and convert to JsonResponse
     */
    protected function handleServiceResponse(ServiceResponse $response): JsonResponse
    {
        return $response->toJsonResponse();
    }

    /**
     * Execute service operation and handle response automatically
     */
    protected function executeServiceOperation(callable $operation, string $operationName = 'operation'): JsonResponse
    {
        // This method can be used by controllers to execute service operations
        // and automatically handle the response conversion
        $response = $operation();

        if ($response instanceof ServiceResponse) {
            return $this->handleServiceResponse($response);
        }

        // If it's not a ServiceResponse, wrap it in a success response
        return $this->successResponse($response, ucfirst($operationName) . ' completed successfully');
    }
}
