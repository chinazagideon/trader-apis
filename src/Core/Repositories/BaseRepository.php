<?php

namespace App\Core\Repositories;

use App\Core\Contracts\RepositoryInterface;
use App\Core\Traits\LoadsRelationships;
use App\Core\Traits\AppliesOwnershipFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Core\Exceptions\NotFoundException;
use Illuminate\Validation\ValidationException;


abstract class BaseRepository implements RepositoryInterface
{
    use LoadsRelationships, AppliesOwnershipFilters;

    protected Model $model;
    /**
     * Constructor
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records
     */
    public function all(array $columns = ['*']): Collection
    {
        $query = $this->query();
        $query = $this->applyOwnershipFilters($query, 'view');

        return $query->get($columns);
    }

    /**
     * Find record by ID
     */
    public function find(int $id, array $columns = ['*']): ?Model
    {
        $query = $this->query();
        $query = $this->applyOwnershipFilters($query, 'view');

        $model = $query->find($id, $columns);

        if ($model && $this->usesLoadsRelationshipsTrait()) {
            $model = $this->loadRelationships($model);
        }

        return $model;
    }

    /**
     * Find record by field
     */
    public function findBy(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        $model = $this->model->where($field, $value)->first($columns);
        if ($this->usesLoadsRelationshipsTrait()) {
            $model = $this->loadRelationships($model);
        }

        return $model;
    }

    /**
     * Find records by field
     */
    public function findAllBy(string $field, mixed $value, array $columns = ['*']): Collection
    {
        $model = $this->model->where($field, $value)->get($columns);
        if ($this->usesLoadsRelationshipsTrait()) {
            $model = $this->loadRelationships($model);
        }

        return $model;
    }

    /**
     * Create new record
     * Automatically uses LoadsRelationships trait if available
     */
    public function create(array $data): Model
    {
        // Validate ownership
        $data = $this->validateCreateOwnership($data);

        $model = $this->model->create($data);

        // Check if this repository uses LoadsRelationships trait
        if ($this->usesLoadsRelationshipsTrait()) {
            $model = $this->loadRelationships($model);
        }

        return $model;
    }

    /**
     * Update record
     * Automatically uses LoadsRelationships trait if available
     */
    public function update(int $id, array $data): ?Model
    {
        $model = $this->find($id);

        if (!$model) {
            throw NotFoundException::resource($this->model->getTable());
        }
        // Validate ownership
        $data = $this->validateUpdateOwnership($model, $data);

        $model->update($data);
        $model = $model->fresh(); // Get fresh instance with updated data

        // Check if this repository uses LoadsRelationships trait
        if ($this->usesLoadsRelationshipsTrait()) {
            $model = $this->loadRelationships($model);
        }

        return $model;
    }

    /**
     * Delete record
     */
    public function delete(int $id): ?Model
    {
        $query = $this->query();
        $model = $query->find($id);
        $query = $this->applyOwnershipFilters($query, 'delete');

        if (!$query->exists() || $model->isDeleted()) {
            return null;
        }

        return $query->delete();
    }

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {

        $query = $this->query();
        $query = $this->applyOwnershipFilters($query, 'view');

        return $query->paginate($perPage, $columns);
    }

    /**
     * Count records
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Check if record exists
     */
    public function exists(int $id): bool
    {
        return $this->model->where('id', $id)->exists();
    }

    /**
     * Get query builder
     */
    protected function query(): Builder
    {
        $query = $this->model->newQuery();
        return $this->applyOwnershipFilters($query, 'view');
    }

    /**
     * Get query builder without ownership filtering (for admin operations)
     */
    protected function queryUnfiltered(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Apply filters to query
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $value) {
            if ($value !== null && $value !== '') {
                $query->where($field, $value);
            }
        }

        return $query;
    }

    /**
     * Apply search to query
     */
    protected function applySearch(Builder $query, string $search, array $searchableFields): Builder
    {
        if (empty($search) || empty($searchableFields)) {
            throw new ValidationException('Search and searchable fields are required');
        }
        $query = $this->applyOwnershipFilters($query, 'view');

        return $query->where(function ($q) use ($search, $searchableFields) {
            foreach ($searchableFields as $field) {
                $q->orWhere($field, 'LIKE', "%{$search}%");
            }
        });
    }

    /**
     * Check if this repository uses the LoadsRelationships trait
     */
    protected function usesLoadsRelationshipsTrait(): bool
    {
        return in_array(\App\Core\Traits\LoadsRelationships::class, class_uses_recursive($this));
    }

      /**
     * Apply business filters to the query
     */
    protected function applyBusinessFilters($query, $filters)
    {
        // Apply status filter
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply date filters
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);
    }
}
