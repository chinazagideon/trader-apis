<?php

namespace App\Core\Controllers;

use App\Core\Http\ServiceResponse;
use App\Core\Services\LoggingService;
use App\Core\Traits\EnhancedLogging;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

abstract class CrudController extends BaseController
{
    use EnhancedLogging;

    /**
     * The service instance for CRUD operations
     */
    protected $service;

    /**
     * The resource name for validation and error messages
     */
    protected string $resourceName;

    /**
     * Fields to exclude from mass assignment
     */
    protected array $excludeFromMassAssignment = ['id', 'created_at', 'updated_at'];

    /**
     * Logging service instance
     */
    protected LoggingService $logger;

    /**
     * Constructor - inject service and set up resource name
     */
    public function __construct($service)
    {
        $this->service = $service;
        $this->resourceName = $this->getResourceName();
        $this->logger = app(LoggingService::class);
    }

    /**
     * Enhanced Request class resolution with module-aware fallbacks
     * Maintains backward compatibility while adding module-specific resolution
     */
    protected function resolveRequestClass(string $operation): ?string
    {
        $module = $this->getModuleName();

        // Map CRUD operations to Request naming (keep original case)
        $requestMap = [
            'index' => 'IndexRequest',
            'show' => 'ShowRequest',
            'store' => 'CreateRequest',
            'update' => 'UpdateRequest',
            'destroy' => 'DestroyRequest',
        ];

        $className = $requestMap[$operation] ?? null;
        if (!$className) {
            return null;
        }

        // Try multiple resolution strategies in order of preference
        $subResource = $this->getSubResourceName();
        $resolutionStrategies = $this->getRequestResolutionStrategies($module, $className, $subResource);

        foreach ($resolutionStrategies as $strategy) {
            if (class_exists($strategy)) {
                $this->logger->logBusinessLogic(
                    $this->getServiceName(),
                    'request_resolution',
                    "Request class resolved using strategy: {$strategy}",
                    [
                        'operation' => $operation,
                        'module' => $module,
                        'strategy_used' => $strategy,
                        'resolution_order' => array_search($strategy, $resolutionStrategies),
                    ]
                );
                return $strategy;
            }
        }

        $this->logger->logBusinessLogic(
            $this->getServiceName(),
            'request_resolution',
            "No request class found for operation: {$operation}",
            [
                'operation' => $operation,
                'module' => $module,
                'attempted_strategies' => $resolutionStrategies,
            ]
        );

        return null;
    }

    /**
     * Get request resolution strategies in order of preference
     * This maintains backward compatibility while adding module-aware and sub-resource resolution
     */
    protected function getRequestResolutionStrategies(string $module, string $className, ?string $subResource = null): array
    {
        $config = config('app.enhanced_resolution', []);
        $strategies = [];

        // If sub-resource exists, prioritize sub-resource specific strategies
        if ($subResource) {
            // Strategy 1: Sub-resource specific request (e.g., TransactionCategoryCreateRequest)
            if ($config['fallback_strategies']['sub_resource_specific'] ?? true) {
                $strategies[] = "App\\Modules\\{$module}\\Http\\Requests\\{$module}{$subResource}{$className}";
            }

            // Strategy 2: Sub-resource conventional request (e.g., CategoryCreateRequest)
            if ($config['fallback_strategies']['sub_resource_conventional'] ?? true) {
                $strategies[] = "App\\Modules\\{$module}\\Http\\Requests\\{$subResource}{$className}";
            }
        }

        // Strategy 3: Module-specific request (e.g., TransactionCreateRequest)
        if ($config['fallback_strategies']['module_specific'] ?? true) {
            $strategies[] = "App\\Modules\\{$module}\\Http\\Requests\\{$module}{$className}";
        }

        // Strategy 4: Conventional request (e.g., CreateRequest) - BACKWARD COMPATIBILITY
        if ($config['fallback_strategies']['conventional'] ?? true) {
            $strategies[] = "App\\Modules\\{$module}\\Http\\Requests\\{$className}";
        }

        // Strategy 5: Generic request with module prefix (e.g., TransactionRequest)
        if ($config['fallback_strategies']['generic_module'] ?? true) {
            $strategies[] = "App\\Modules\\{$module}\\Http\\Requests\\{$module}Request";
        }

        // Strategy 6: Fallback to generic request
        if ($config['fallback_strategies']['generic_fallback'] ?? true) {
            $strategies[] = "App\\Modules\\{$module}\\Http\\Requests\\Request";
        }

        return $strategies;
    }

    /**
     * Enhanced Resource class resolution with module-aware fallbacks
     * Maintains backward compatibility while adding module-specific resolution
     */
    protected function resolveResourceClass(string $operation): ?string
    {
        $module = $this->getModuleName();

        // Map CRUD operations to Resource naming
        $resourceMap = [
            'index' => 'IndexResource',
            'show' => 'ShowResource',
            'store' => 'CreateResource',
            'update' => 'UpdateResource',
            'destroy' => 'DestroyResource',
        ];

        $className = $resourceMap[$operation] ?? null;
        if (!$className) {
            return null;
        }

        // Try multiple resolution strategies in order of preference
        $subResource = $this->getSubResourceName();
        $resolutionStrategies = $this->getResourceResolutionStrategies($module, $className, $subResource);

        foreach ($resolutionStrategies as $strategy) {
            if (class_exists($strategy)) {
                $this->logger->logBusinessLogic(
                    $this->getServiceName(),
                    'resource_resolution',
                    "Resource class resolved using strategy: {$strategy}",
                    [
                        'operation' => $operation,
                        'module' => $module,
                        'strategy_used' => $strategy,
                        'resolution_order' => array_search($strategy, $resolutionStrategies),
                    ]
                );
                return $strategy;
            }
        }

        $this->logger->logBusinessLogic(
            $this->getServiceName(),
            'resource_resolution',
            "No resource class found for operation: {$operation}",
            [
                'operation' => $operation,
                'module' => $module,
                'attempted_strategies' => $resolutionStrategies,
            ]
        );

        return null;
    }

    /**
     * Get resource resolution strategies in order of preference
     * This maintains backward compatibility while adding module-aware and sub-resource resolution
     */
    protected function getResourceResolutionStrategies(string $module, string $className, ?string $subResource = null): array
    {
        $config = config('app.enhanced_resolution', []);
        $strategies = [];

        // If sub-resource exists, prioritize sub-resource specific strategies
        if ($subResource) {
            // Strategy 1: Sub-resource specific resource (e.g., TransactionCategoryShowResource)
            if ($config['fallback_strategies']['sub_resource_specific'] ?? true) {
                $strategies[] = "App\\Modules\\{$module}\\Http\\Resources\\{$module}{$subResource}{$className}";
            }

            // Strategy 2: Sub-resource conventional resource (e.g., CategoryShowResource)
            if ($config['fallback_strategies']['sub_resource_conventional'] ?? true) {
                $strategies[] = "App\\Modules\\{$module}\\Http\\Resources\\{$subResource}{$className}";
            }
        }

        // Strategy 3: Module-specific resource (e.g., TransactionShowResource)
        if ($config['fallback_strategies']['module_specific'] ?? true) {
            $strategies[] = "App\\Modules\\{$module}\\Http\\Resources\\{$module}{$className}";
        }

        // Strategy 4: Conventional resource (e.g., ShowResource) - BACKWARD COMPATIBILITY
        if ($config['fallback_strategies']['conventional'] ?? true) {
            $strategies[] = "App\\Modules\\{$module}\\Http\\Resources\\{$className}";
        }

        // Strategy 5: Generic resource with module prefix (e.g., TransactionResource)
        if ($config['fallback_strategies']['generic_module'] ?? true) {
            $strategies[] = "App\\Modules\\{$module}\\Http\\Resources\\{$module}Resource";
        }

        // Strategy 6: Fallback to generic resource
        if ($config['fallback_strategies']['generic_fallback'] ?? true) {
            $strategies[] = "App\\Modules\\{$module}\\Http\\Resources\\Resource";
        }

        return $strategies;
    }

    public function smartResolveResourceClass(string $operation): ?string
    {
        $module = $this->getModuleName();
        $resourceClass = $this->resolveResourceClass($operation);
        $categoryResourceClass = $this->resolveResourceClass($operation . 'Category');
        if (!$resourceClass) {
            return null;
        }

        return $resourceClass;
    }

    /**
     * Get module name from controller class
     */
    protected function getModuleName(): string
    {
        $reflection = new \ReflectionClass($this);
        $namespace = $reflection->getNamespaceName();

        // Extract module name from namespace: App\Modules\{Module}\Http\Controllers
        preg_match('/App\\\\Modules\\\\([^\\\\]+)\\\\Http/', $namespace, $matches);

        $module = $matches[1] ?? 'Unknown';

        \Illuminate\Support\Facades\Log::debug("Module name extracted", [
            'module' => $module,
            'matches' => $matches
        ]);

        return $module;
    }

    /**
     * Get sub-resource name from controller class
     * Detects sub-resources like TransactionCategory from TransactionCategoryController
     */
    protected function getSubResourceName(): ?string
    {
        $reflection = new \ReflectionClass($this);
        $className = $reflection->getShortName();

        // Extract sub-resource name from controller class name
        // Examples: TransactionCategoryController -> TransactionCategory
        //           TransactionController -> null (no sub-resource)
        if (preg_match('/^(.+)Controller$/', $className, $matches)) {
            $controllerName = $matches[1];
            $module = $this->getModuleName();

            // If controller name contains module name + additional text, it's a sub-resource
            if (strpos($controllerName, $module) === 0 && strlen($controllerName) > strlen($module)) {
                $subResource = substr($controllerName, strlen($module));

                \Illuminate\Support\Facades\Log::debug("Sub-resource detected", [
                    'controller_class' => $className,
                    'module' => $module,
                    'sub_resource' => $subResource
                ]);

                return $subResource;
            }
        }

        return null;
    }

    /**
     * Magic method to handle CRUD operations automatically
     */
    public function __call(string $method, array $arguments): JsonResponse
    {
        // Map HTTP methods to CRUD operations
        $crudMap = [
            'index' => 'index',
            'show' => 'show',
            'store' => 'store',
            'update' => 'update',
            'destroy' => 'destroy',
        ];

        if (isset($crudMap[$method])) {
            return $this->handleCrudOperation($crudMap[$method], $arguments);
        }

        // If method doesn't exist, throw error
        throw new \BadMethodCallException(
            "Method {$method} not found in " . static::class
        );
    }

    /**
     * Handle CRUD operations with automatic validation and response formatting
     */
    protected function handleCrudOperation(string $operation, array $arguments): JsonResponse
    {
        $request = request();
        $tracking = $this->logOperationStart($operation, [
            'controller' => class_basename(static::class),
            'resource' => $this->resourceName,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            $this->logger->logBusinessLogic(
                $this->getServiceName(),
                $operation,
                "Starting {$operation} operation",
                [
                    'controller' => class_basename(static::class),
                    'resource' => $this->resourceName,
                    'request_data' => $this->sanitizeRequestData($request->all()),
                ]
            );

            $response = match ($operation) {
                'index' => $this->handleIndex($request),
                'show' => $this->handleShow($request->route('id') ?? $this->extractIdFromArguments($arguments)),
                'store' => $this->handleStore($request),
                'update' => $this->handleUpdate($request->route('id') ?? $this->extractIdFromArguments($arguments), $request),
                'destroy' => $this->handleDestroy($request->route('id') ?? $this->extractIdFromArguments($arguments)),
                default => throw new \InvalidArgumentException("Unknown CRUD operation: {$operation}")
            };

            $this->logOperationSuccess($operation, $tracking, [
                'response_status' => $response->getStatusCode(),
                'response_success' => $response->getData(true)['success'] ?? false,
            ]);

            return $response;

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->logOperationError($operation, $e, $tracking, [
                'validation_errors' => $e->errors(),
                'failed_fields' => array_keys($e->errors()),
            ]);
            return $this->handleValidationException($e);
        } catch (\Exception $e) {
            $this->logOperationError($operation, $e, $tracking, [
                'exception_type' => get_class($e),
                'error_code' => $e->getCode(),
            ]);
            return $this->handleException($e, $operation);
        }
    }

    /**
     * Extract Request object from arguments array
     */
    protected function extractRequestFromArguments(array $arguments, int $offset = 0): ?Request
    {
        foreach ($arguments as $index => $argument) {
            if ($argument instanceof Request) {
                return $argument;
            }
        }

        return null;
    }

    /**
     * Extract ID from arguments array
     */
    protected function extractIdFromArguments(array $arguments): ?int
    {
        foreach ($arguments as $argument) {
            if (is_numeric($argument)) {
                return (int) $argument;
            }
        }

        return null;
    }

    /**
     * Handle index operation (GET /resource)
     */
    protected function handleIndex(Request $request = null): JsonResponse
    {
        // For index, we typically don't need strict validation, just extract filters
        $validatedData = $this->extractFilters($request);
        $perPage = $this->extractPerPage($request);

        $response = $this->service->index($validatedData, $perPage);

        $resourceClass = $this->resolveResourceClass('index');
        if ($resourceClass && $response->isSuccess()) {
            $response->setData($resourceClass::collection($response->getData()));
        }

        return $this->handleServiceResponse($response);
    }

    /**
     * Handle show operation (GET /resource/{id})
     */
    protected function handleShow($id): JsonResponse
    {
        if (!$id) {
            return $this->errorResponse('ID is required', null, Response::HTTP_BAD_REQUEST);
        }

        $response = $this->service->show($id);

        $resourceClass = $this->resolveResourceClass('show');
        if ($resourceClass && $response->isSuccess()) {
            $response->setData(new $resourceClass($response->getData()));
        }

        return $this->handleServiceResponse($response);
    }

    /**
     * Handle store operation (POST /resource)
     */
    protected function handleStore(Request $request): JsonResponse
    {
        // Let Laravel handle Form Request validation automatically
        $validatedData = $this->validateFormRequest($request, 'store');

        $processedData = $this->beforeStore($validatedData, $request);
        $response = $this->service->store($processedData);

        $resourceClass = $this->resolveResourceClass('store');
        if ($resourceClass && $response->isSuccess()) {
            $response->setData(new $resourceClass($response->getData()));
        }

        return $this->handleServiceResponse($response);
    }

    /**
     * Validate request using Laravel's Form Request pattern
     */
    protected function validateFormRequest(Request $request, string $operation): array
    {
        $requestClass = $this->resolveRequestClass($operation);

        $this->logger->logBusinessLogic(
            $this->getServiceName(),
            $operation,
            "Starting validation",
            [
                'request_class' => $requestClass,
                'operation' => $operation,
                'data_keys' => array_keys($request->all()),
                'data_count' => count($request->all()),
            ]
        );

        if ($requestClass && class_exists($requestClass)) {
            try {
                // Create instance of Form Request and validate
                $formRequest = app($requestClass);

                // Set the request data
                $formRequest->replace($request->all());

                // Validate and return validated data
                $validatedData = $formRequest->validated();

                $this->logger->logBusinessLogic(
                    $this->getServiceName(),
                    $operation,
                    "Validation successful",
                    [
                        'validated_fields' => array_keys($validatedData),
                        'validated_count' => count($validatedData),
                    ]
                );

                return $validatedData;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->logger->logError(
                    $this->getServiceName(),
                    $operation,
                    $e,
                    [
                        'validation_errors' => $e->errors(),
                        'failed_fields' => array_keys($e->errors()),
                        'request_class' => $requestClass,
                    ]
                );
                throw $e;
            }
        }

        // Fallback to manual validation
        $this->logger->logBusinessLogic(
            $this->getServiceName(),
            $operation,
            "Using fallback validation",
            [
                'reason' => 'No Form Request class found',
                'request_class' => $requestClass,
            ]
        );

        return $this->validateStoreData($request);
    }

    /**
     * Handle update operation (PUT/PATCH /resource/{id})
     */
    protected function handleUpdate($id, Request $request): JsonResponse
    {
        if (!$id) {
            return $this->errorResponse('ID is required', null, Response::HTTP_BAD_REQUEST);
        }

        // Let Laravel handle Form Request validation automatically
        $validatedData = $this->validateFormRequest($request, 'update');

        $processedData = $this->beforeUpdate($validatedData, $request, $id);
        $response = $this->service->update($id, $processedData);

        $resourceClass = $this->resolveResourceClass('update');
        if ($resourceClass && $response->isSuccess()) {
            $response->setData(new $resourceClass($response->getData()));
        }

        return $this->handleServiceResponse($response);
    }

    /**
     * Handle destroy operation (DELETE /resource/{id})
     */
    protected function handleDestroy($id): JsonResponse
    {
        if (!$id) {
            return $this->errorResponse('ID is required', null, Response::HTTP_BAD_REQUEST);
        }

        $response = $this->service->destroy($id);

        $resourceClass = $this->resolveResourceClass('destroy');
        if ($resourceClass && $response->isSuccess() && $response->getData()) {
            $response->setData(new $resourceClass($response->getData()));
        }

        return $this->handleServiceResponse($response);
    }

    /**
     * Validate store data (fallback when no Form Request is used)
     */
    protected function validateStoreData(Request $request): array
    {
        return $request->all();
    }

    /**
     * Validate update data (fallback when no Form Request is used)
     */
    protected function validateUpdateData(Request $request): array
    {
        return $request->all();
    }

    /**
     * Extract filters from request
     */
    protected function extractFilters(Request $request = null): array
    {
        if (!$request) {
            return [];
        }

        return $request->only([
            'search', 'sort_by', 'sort_direction', 'status', 'type', 'date_from', 'date_to'
        ]);
    }

    /**
     * Extract per page parameter from request
     */
    protected function extractPerPage(Request $request = null): int
    {
        if (!$request) {
            return 15;
        }

        return (int) $request->get('per_page', 15);
    }

    /**
     * Get resource name for error messages
     */
    protected function getResourceName(): string
    {
        if (isset($this->resourceName)) {
            return $this->resourceName;
        }

        // Extract from class name (e.g., InvestmentController -> Investment)
        $className = class_basename(static::class);
        return str_replace('Controller', '', $className);
    }

    /**
     * Hook method called before store operation
     * Override in child classes for custom processing
     */
    protected function beforeStore(array $data, Request $request): array
    {
        return $this->excludeMassAssignmentFields($data);
    }

    /**
     * Hook method called before update operation
     * Override in child classes for custom processing
     */
    protected function beforeUpdate(array $data, Request $request, $id): array
    {
        return $this->excludeMassAssignmentFields($data);
    }

    /**
     * Exclude fields from mass assignment
     */
    protected function excludeMassAssignmentFields(array $data): array
    {
        return array_diff_key($data, array_flip($this->excludeFromMassAssignment));
    }

    /**
     * Handle validation exceptions with custom messages
     */
    protected function handleValidationException(\Illuminate\Validation\ValidationException $e): JsonResponse
    {
        $errors = $e->errors();
        $message = 'Validation failed';

        // Get the first error message as the main message
        if (!empty($errors)) {
            $firstField = array_key_first($errors);
            $firstError = $errors[$firstField][0] ?? $message;
            $message = $firstError;
        }

        $this->logger->logBusinessLogic(
            $this->getServiceName(),
            'validation_failed',
            "Validation failed with detailed errors",
            [
                'validation_errors' => $errors,
                'failed_fields' => array_keys($errors),
                'error_count' => count($errors),
                'first_error_field' => array_key_first($errors),
                'first_error_message' => $firstError,
            ]
        );

        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'status_code' => 422
        ], 422);
    }

    /**
     * Handle exceptions and convert to appropriate responses
     */
    protected function handleException(\Exception $e, string $operation): JsonResponse
    {
        $this->logger->logError(
            $this->getServiceName(),
            $operation,
            $e,
            [
                'controller' => class_basename(static::class),
                'resource' => $this->resourceName,
                'operation' => $operation,
            ]
        );

        if ($e instanceof ValidationException) {
            return $this->validationErrorResponse($e->errors());
        }

        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFoundResponse("{$this->resourceName} not found");
        }

        return $this->errorResponse(
            "Failed to {$operation} {$this->resourceName}",
            null,
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**
     * Sanitize request data for logging (remove sensitive information)
     */
    protected function sanitizeRequestData(array $data): array
    {
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'secret', 'key', 'api_key'];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }

    /**
     * Get service name for logging
     */
    protected function getServiceName(): string
    {
        return class_basename(static::class) . 'Controller';
    }

    /**
     * Health check endpoint
     */
    public function health(): JsonResponse
    {
        return $this->successResponse([
            'status' => 'healthy',
            'module' => $this->getModuleName(),
            'timestamp' => now(),
        ], $this->getModuleName() . ' module health check');
    }
}
