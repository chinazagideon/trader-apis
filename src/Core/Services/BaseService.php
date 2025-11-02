<?php

namespace App\Core\Services;

use App\Core\Contracts\ServiceInterface;
use App\Core\Http\ServiceResponse;
use App\Core\Traits\ServiceResponseHandler;
use App\Core\Traits\EnhancedLogging;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class BaseService implements ServiceInterface
{
    use ServiceResponseHandler, EnhancedLogging;

    protected string $serviceName;
    protected bool $available = true;
    protected $repository;

    public function __construct($repository = null)
    {
        $this->serviceName = $this->getServiceName();
        $this->repository = $repository;
    }

    public function getServiceName(): string
    {
        return $this->serviceName ?? class_basename(static::class);
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * Log service activity
     */
    protected function log(string $message, array $context = []): void
    {
        Log::info("[{$this->getServiceName()}] {$message}", $context);
    }

    /**
     * Log service errors
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error("[{$this->getServiceName()}] {$message}", $context);
    }

    /**
     * Handle service exceptions (deprecated - use executeServiceOperation instead)
     * @deprecated Use executeServiceOperation for automatic exception handling
     */
    protected function handleException(\Exception $e, string $operation = 'operation'): void
    {
        $this->logError("Failed to {$operation}: " . $e->getMessage(), [
            'exception' => $e,
            'operation' => $operation
        ]);
    }

    /**
     * Default implementation for index operation
     * Delegates to repository with convention-based method resolution
     */
    public function index(array $filters = [], int $perPage = 15): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($filters, $perPage) {
            if (!$this->repository) {
                throw new \BadMethodCallException(
                    'Repository not injected in ' . static::class . '. Override index() method or inject repository.'
                );
            }

            $result = null;

            // Try convention-based method first: get{Module}s()
            $method = $this->getRepositoryMethod('get', 's');
            if (method_exists($this->repository, $method)) {
                $result = $this->repository->$method($filters, $perPage);
            }
            // Fallback to standard repository methods
            elseif (method_exists($this->repository, 'paginate')) {
                $result = $this->repository->paginate($perPage);
            }
            elseif (method_exists($this->repository, 'all')) {
                $result = $this->repository->all();
            }
            else {
                throw new \BadMethodCallException(
                    'No suitable repository method found for index operation in ' . static::class
                );
            }

            // If result is already a ServiceResponse, return it directly
            if ($result instanceof ServiceResponse) {
                return $result;
            }

            // If result is a LengthAwarePaginator, convert it to ServiceResponse with pagination metadata
            if ($result instanceof LengthAwarePaginator) {
                return $this->createPaginatedResponse($result, 'Data retrieved successfully');
            }

            // Otherwise, wrap in a success response (for collections, etc.)
            return ServiceResponse::success($result, 'Data retrieved successfully');
        }, 'index');
    }

    /**
     * Default implementation for show operation
     * Delegates to repository find method
     */
    public function show(int $id): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($id) {
            if (!$this->repository) {
                throw new \BadMethodCallException(
                    'Repository not injected in ' . static::class . '. Override show() method or inject repository.'
                );
            }

            if (method_exists($this->repository, 'find')) {
                return $this->repository->find($id);
            }

            throw new \BadMethodCallException(
                'Repository find() method not found in ' . static::class
            );
        }, 'show');
    }

    /**
     * Default implementation for store operation
     * Delegates to repository create method
     */
    public function store(array $data): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($data) {
            if (!$this->repository) {
                throw new \BadMethodCallException(
                    'Repository not injected in ' . static::class . '. Override store() method or inject repository.'
                );
            }

            $result = null;
            if (method_exists($this->repository, 'create')) {
                $result = $this->repository->create($data);
            } else {
                throw new \BadMethodCallException(
                    'Repository create() method not found in ' . static::class
                );
            }

            // Wrap result in ServiceResponse
            $response = $result instanceof ServiceResponse
                ? $result
                : ServiceResponse::success($result, 'Resource created successfully', Response::HTTP_CREATED);

            // Trigger completed hook if operation was successful and model exists
            if ($response->isSuccess() && $response->getData() instanceof Model) {
                $this->triggerCompleted('store', $data, $response->getData());
            }

            return $response;
        }, 'store');
    }

    /**
     * Default implementation for update operation
     * Delegates to repository update method
     */
    public function update(int $id, array $data): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($id, $data) {
            if (!$this->repository) {
                throw new \BadMethodCallException(
                    'Repository not injected in ' . static::class . '. Override update() method or inject repository.'
                );
            }

            $result = null;
            if (method_exists($this->repository, 'update')) {
                $result = $this->repository->update($id, $data);
            } else {
                throw new \BadMethodCallException(
                    'Repository update() method not found in ' . static::class
                );
            }

            // Wrap result in ServiceResponse
            $response = $result instanceof ServiceResponse
                ? $result
                : ServiceResponse::success($result, 'Resource updated successfully');

            // Trigger completed hook if operation was successful and model exists
            if ($response->isSuccess() && $response->getData() instanceof Model) {
                $this->triggerCompleted('update', array_merge($data, ['id' => $id]), $response->getData());
            }

            return $response;
        }, 'update');
    }

    /**
     * Default implementation for destroy operation
     * Delegates to repository delete method
     */
    public function destroy(int $id): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($id) {
            if (!$this->repository) {
                throw new \BadMethodCallException(
                    'Repository not injected in ' . static::class . '. Override destroy() method or inject repository.'
                );
            }

            // Get model before deletion if we want to pass it to completed hook
            $model = null;
            if (method_exists($this->repository, 'find')) {
                $model = $this->repository->find($id);
            }

            $result = null;
            if (method_exists($this->repository, 'delete')) {
                $result = $this->repository->delete($id);
            } else {
                throw new \BadMethodCallException(
                    'Repository delete() method not found in ' . static::class
                );
            }

            // Wrap result in ServiceResponse
            $response = $result instanceof ServiceResponse
                ? $result
                : ServiceResponse::success($result, 'Resource deleted successfully');

            // Trigger completed hook if operation was successful
            // Note: Model may be null for destroy operations
            if ($response->isSuccess()) {
                $this->triggerCompleted('destroy', ['id' => $id], $model);
            }

            return $response;
        }, 'destroy');
    }

    /**
     * Get repository method name based on convention
     * Example: getInvestments() for Investment module
     */
    protected function getRepositoryMethod(string $prefix, string $suffix = ''): string
    {
        $module = $this->getModuleName();
        return $prefix . $module . $suffix;
    }

    /**
     * Get module name from service class namespace
     */
    protected function getModuleName(): string
    {
        $reflection = new \ReflectionClass($this);
        $namespace = $reflection->getNamespaceName();

        // Extract module name from namespace: App\Modules\{Module}\Services
        preg_match('/App\\\\Modules\\\\([^\\\\]+)\\\\/', $namespace, $matches);

        return $matches[1] ?? 'Unknown';
    }

    // src/Core/Services/BaseService.php

    /**
     * create paginated response
     *
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator
     * @param string $message
     * @return ServiceResponse
     */
    protected function createPaginatedResponse(
        \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator,
        string $message = 'Data retrieved successfully'
    ): ServiceResponse {
        $pagination = [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];

        return ServiceResponse::success($paginator->items(), $message, Response::HTTP_OK, $pagination);
    }

    /**
     * Trigger the completed hook method if it's overridden
     * This method centralizes the logic for calling completed() and ensures
     * consistent behavior across all CRUD operations
     *
     * @param string $operation The operation that was performed (store, update, destroy)
     * @param array $data The original data used for the operation
     * @param Model|null $model The model instance (may be null for destroy operations)
     * @return void
     */
    protected function triggerCompleted(string $operation, array $data, ?Model $model): void
    {
        // Only trigger if model exists and method is actually overridden
        // This prevents unnecessary calls when child classes don't override completed()
        if ($model instanceof Model) {
            // Check if the method is overridden by comparing reflection
            $reflection = new \ReflectionClass($this);
            $baseMethod = $reflection->getParentClass()?->getMethod('completed');
            $currentMethod = $reflection->getMethod('completed');

            // Only call if method is actually overridden (not using base implementation)
            if (!$baseMethod || $currentMethod->getDeclaringClass()->getName() !== $baseMethod->getDeclaringClass()->getName()) {
                try {
                    $this->completed($data, $model, $operation);
                } catch (\Exception $e) {
                    // Log but don't fail the operation if completed hook fails
                    $this->logError("completed hook failed for {$operation}", [
                        'operation' => $operation,
                        'model_id' => $model->id ?? null,
                        'error' => $e->getMessage(),
                        'exception' => $e
                    ]);
                }
            }
        }
    }

    /**
     * Hook method called after successful CRUD operations
     * Override this method in child classes to perform post-operation actions
     * such as sending notifications, triggering events, updating related records, etc.
     *
     * @param array $data The original data used for the operation
     * @param Model $model The model instance that was created/updated/deleted
     * @param string $operation The operation that was performed (store, update, destroy)
     * @return void
     */
    protected function completed(array $data, Model $model, string $operation = 'store|update|destroy'): void
    {
        // Empty implementation - override in child classes
    }
}
