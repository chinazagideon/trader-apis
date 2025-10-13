<?php

namespace App\Core\Services;

use App\Core\Contracts\ServiceInterface;
use App\Core\Http\ServiceResponse;
use App\Core\Traits\ServiceResponseHandler;
use App\Core\Traits\EnhancedLogging;
use Illuminate\Support\Facades\Log;

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

            // Try convention-based method first: get{Module}s()
            $method = $this->getRepositoryMethod('get', 's');
            if (method_exists($this->repository, $method)) {
                return $this->repository->$method($filters, $perPage);
            }

            // Fallback to standard repository methods
            if (method_exists($this->repository, 'paginate')) {
                return $this->repository->paginate($perPage);
            }

            if (method_exists($this->repository, 'all')) {
                return $this->repository->all();
            }

            throw new \BadMethodCallException(
                'No suitable repository method found for index operation in ' . static::class
            );
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

            if (method_exists($this->repository, 'create')) {
                return $this->repository->create($data);
            }

            throw new \BadMethodCallException(
                'Repository create() method not found in ' . static::class
            );
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

            if (method_exists($this->repository, 'update')) {
                return $this->repository->update($id, $data);
            }

            throw new \BadMethodCallException(
                'Repository update() method not found in ' . static::class
            );
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

            if (method_exists($this->repository, 'delete')) {
                return $this->repository->delete($id);
            }

            throw new \BadMethodCallException(
                'Repository delete() method not found in ' . static::class
            );
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

}
