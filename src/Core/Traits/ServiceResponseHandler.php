<?php

namespace App\Core\Traits;

use App\Core\Exceptions\AppException;
use App\Core\Exceptions\BusinessLogicException;
use App\Core\Exceptions\NotFoundException;
use App\Core\Exceptions\ServiceException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\ServiceResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

trait ServiceResponseHandler
{
    /**
     * Execute a service operation with automatic exception handling and enhanced logging
     */
    public function executeServiceOperation(callable $operation, string $operationName = 'operation'): ServiceResponse
    {
        // Use enhanced logging if available
        if (method_exists($this, 'logOperationStart')) {
            $tracking = $this->logOperationStart($operationName);

            try {
                $result = $operation();

                // If the operation returns a ServiceResponse, return it directly
                if ($result instanceof ServiceResponse) {
                    $this->logOperationSuccess($operationName, $tracking);
                    return $result;
                }

                // Otherwise, wrap the result in a success response
                $this->logOperationSuccess($operationName, $tracking);
                return ServiceResponse::success($result, ucfirst($operationName) . ' completed successfully');

            } catch (AppException $e) {
                $this->logOperationError($operationName, $e, $tracking);
                return ServiceResponse::fromException($e);

            } catch (\Exception $e) {
                $this->logOperationError($operationName, $e, $tracking);

                // Convert generic exceptions to ServiceException
                $serviceException = new ServiceException(
                    'An unexpected error occurred: ' . $e->getMessage(),
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    'UNEXPECTED_ERROR',
                    ['original_exception' => $e->getMessage()],
                    is_int($e->getCode()) ? $e->getCode() : 0,
                    $e
                );

                return ServiceResponse::fromException($serviceException);
            }
        }

        // Fallback to original implementation if enhanced logging not available
        try {
            $result = $operation();

            // If the operation returns a ServiceResponse, return it directly
            if ($result instanceof ServiceResponse) {
                return $result;
            }

            // Otherwise, wrap the result in a success response
            return ServiceResponse::success($result, ucfirst($operationName) . ' completed successfully');

        } catch (AppException $e) {
            $this->logException($e, $operationName);
            return ServiceResponse::fromException($e);

        } catch (\Exception $e) {
            $this->logException($e, $operationName);

            // Convert generic exceptions to ServiceException
            $serviceException = new ServiceException(
                'An unexpected error occurred: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'UNEXPECTED_ERROR',
                ['original_exception' => $e->getMessage()],
                is_int($e->getCode()) ? $e->getCode() : 0,
                $e
            );

            return ServiceResponse::fromException($serviceException);
        }
    }

    /**
     * Validate data and throw ValidationException if invalid
     */
    protected function validateData(array $data, array $rules, array $messages = []): array
    {
        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException(
                'Validation failed',
                $validator->errors()->toArray(),
                \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY,
                'VALIDATION_ERROR'
            );
        }

        return $validator->validated();
    }

    /**
     * Throw NotFoundException if resource is null
     */
    protected function ensureResourceExists($resource, string $resourceName = 'Resource'): void
    {
        if ($resource === null) {
            throw NotFoundException::resource($resourceName);
        }
    }

    /**
     * Throw BusinessLogicException for business rule violations
     */
    protected function throwBusinessLogicException(string $message, array $context = []): void
    {
        throw new BusinessLogicException($message, Response::HTTP_CONFLICT, 'BUSINESS_LOGIC_ERROR', $context);
    }

    /**
     * Log exception with context
     */
    protected function logException(\Exception $e, string $operation): void
    {
        $context = [
            'operation' => $operation,
            'exception_class' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        if ($e instanceof AppException) {
            $context = array_merge($context, $e->getContext());
        }

        Log::error("[{$this->getServiceName()}] Failed to {$operation}: " . $e->getMessage(), $context);
    }

    /**
     * Get service name for logging
     */
    protected function getServiceName(): string
    {
        return class_basename(static::class);
    }
}
