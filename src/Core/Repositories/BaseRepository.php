<?php

namespace App\Core\Repositories;

use App\Core\Contracts\RepositoryInterface;
use App\Core\Traits\LoadsRelationships;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements RepositoryInterface
{
    use LoadsRelationships;

    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->all($columns);
    }

    /**
     * Find record by ID
     */
    public function find(int $id, array $columns = ['*']): ?Model
    {
        $model = $this->model->find($id, $columns);
        if ($this->usesLoadsRelationshipsTrait()) {
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
            return null;
        }

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
        $model = $this->find($id);

        if (!$model || $model->isDeleted()) {
            return null;
        }

        return $model->delete();
    }

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->paginate($perPage, $columns);
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
            return $query;
        }

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
}
