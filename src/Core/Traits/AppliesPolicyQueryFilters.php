<?php

namespace App\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Modules\User\Database\Models\User;


/**
 * Trait for applying policy-based query filtering
 *
 * This trait automatically applies viewAnyQuery() from policies to filter
 * queries based on ownership and permissions. It integrates seamlessly with
 * BasePolicy and BaseRepository.
 */
trait AppliesPolicyQueryFilters
{
    use HasClientApp;

    /**
     * Cache for policy instances to avoid repeated resolution
     */
    protected static array $policyCache = [];

    /**
     * Apply policy-based query filtering for index operations
     *
     * @param Builder $query
     * @param bool $loadRelationships
     * @param array $filters
     * @return Builder
     */
    protected function applyPolicyQueryFilters(Builder $query, bool $loadRelationships = true, array $filters = []): Builder
    {
        $user = $this->getUserFromFilters($filters);
        $client = $this->getClient();
        $clientId = $client?->id;

        // STEP 1: Apply policy filtering FIRST (reduces dataset before applying other filters)
        // This is more efficient as it reduces the number of rows to filter
        if (!$user) {
            // Check if client context is set (for API key authentication)
            if ($clientId) {
                $model = $query->getModel();

                // Check if model uses HasClientScope trait
                if (in_array(\App\Core\Traits\HasClientScope::class, class_uses_recursive($model))) {
                    // Client scope will handle filtering, so allow the query
                    // Apply filters after client scope
                    $query = $this->applyQueryFilters($query, $filters);

                    if ($loadRelationships && method_exists($this, 'withRelationships')) {
                        $query = $this->withRelationships($query);
                    }
                    return $query;
                }
            }

            // No user and no client context - return empty query
            return $query->whereRaw('1 = 0');
        }

        // Get the model instance from query
        $model = $query->getModel();

        // Get the policy for this model (with caching)
        $policy = $this->getPolicyForModel($model);

        // Apply policy filtering FIRST (before query filters)
        // This reduces the dataset size before applying additional filters
        if ($policy && method_exists($policy, 'viewAnyQuery')) {
            $policy->viewAnyQuery($user, $query);
        }

        // STEP 2: Apply query filters AFTER policy filtering
        // This ensures we're only filtering the already-reduced dataset
        $query = $this->applyQueryFilters($query, $filters);

        // STEP 3: Load relationships AFTER all filtering (most efficient)
        // This ensures we only load relationships for records that will actually be returned
        if ($loadRelationships && method_exists($this, 'withRelationships')) {
            $query = $this->withRelationships($query);
        }

        return $query;
    }

    /**
     * Apply query filters with validation and optimization
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    protected function applyQueryFilters(Builder $query, array $filters): Builder
    {
        if (empty($filters)) {
            return $query;
        }

        $model = $query->getModel();
        $table = $model->getTable();
        $allowedFilters = $this->getAllowedFilterFields();

        foreach ($filters as $filter => $value) {
            // Skip null/empty values
            if ($value === null || $value === '') {
                continue;
            }

            // Validate filter field if allowed filters are defined
            if (!empty($allowedFilters) && !in_array($filter, $allowedFilters)) {
                continue; // Skip invalid filters
            }

            // Handle array values (for IN queries)
            if (is_array($value)) {
                $query->whereIn($filter, $value);
                continue;
            }

            // Handle range queries (e.g., 'price_from', 'price_to')
            if (str_ends_with($filter, '_from')) {
                $field = str_replace('_from', '', $filter);
                $query->where($field, '>=', $value);
                continue;
            }

            if (str_ends_with($filter, '_to')) {
                $field = str_replace('_to', '', $filter);
                $query->where($field, '<=', $value);
                continue;
            }

            // Handle LIKE queries (for search)
            if (str_ends_with($filter, '_like') || str_ends_with($filter, '_search')) {
                $field = str_replace(['_like', '_search'], '', $filter);
                $query->where($field, 'LIKE', "%{$value}%");
                continue;
            }

            // Handle relationship filters (e.g., 'user_id', 'category_id')
            // Check if column exists in the model's table
            if ($this->columnExists($table, $filter)) {
                $query->where($filter, $value);
            }
        }

        return $query;
    }

    /**
     * Get allowed filter fields for this repository
     * Override this method in child repositories to restrict filterable fields
     *
     * @return array Empty array means all fields are allowed (backward compatible)
     */
    protected function getAllowedFilterFields(): array
    {
        return []; // Empty = all fields allowed (backward compatible)
    }

    /**
     * Check if a column exists in the table
     * Uses Laravel's Schema facade (works with Laravel 5.5+)
     *
     * @param string $table
     * @param string $column
     * @return bool
     */
    protected function columnExists(string $table, string $column): bool
    {
        // Cache column existence checks per request
        static $columnCache = [];
        $cacheKey = "{$table}.{$column}";

        if (!isset($columnCache[$cacheKey])) {

            $columnCache[$cacheKey] = \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        }

        return $columnCache[$cacheKey];
    }

    /**
     * Get policy for model with caching
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return mixed
     */
    protected function getPolicyForModel($model)
    {
        $modelClass = get_class($model);

        if (!isset(self::$policyCache[$modelClass])) {
            self::$policyCache[$modelClass] = Gate::getPolicyFor($model);
        }

        return self::$policyCache[$modelClass];
    }

    /**
     * Get query with policy filtering applied
     * Use this in your repository methods instead of query()
     */
    protected function queryWithPolicyFilter(array $filters = [], bool $loadRelationships = true): Builder
    {
        $query = $this->query();
        return $this->applyPolicyQueryFilters($query, $loadRelationships, $filters);
    }

    /**
     * Get the user from the filters
     * @param array $filters
     * @return ?User
     */
    protected function getUserFromFilters(array $filters): ?User
    {
        $user = $filters['user'] ?? Auth::user();
        return $user;
    }
}
