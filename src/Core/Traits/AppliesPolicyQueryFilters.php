<?php

namespace App\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

/**
 * Trait for applying policy-based query filtering
 *
 * This trait automatically applies viewAnyQuery() from policies to filter
 * queries based on ownership and permissions. It integrates seamlessly with
 * BasePolicy and BaseRepository.
 */
trait AppliesPolicyQueryFilters
{
    /**
     * Apply policy-based query filtering for index operations
     *
     * @param Builder $query
     * @param bool $loadRelationships
     * @return Builder
     */
    protected function applyPolicyQueryFilters(Builder $query, bool $loadRelationships = true): Builder
    {
        $user = Auth::user();

        // If no user is authenticated, return empty query
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // Get the model instance from query
        $model = $query->getModel();


        // Get the policy for this model
        $policy = Gate::getPolicyFor($model);


        // If policy exists and has viewAnyQuery method, apply it
        if ($policy && method_exists($policy, 'viewAnyQuery')) {
            $policy->viewAnyQuery($user, $query);
        }


        // STEP 2: Load relationships AFTER policy filtering (more efficient)
        // This ensures we only load relationships for records that will actually be returned
        if ($loadRelationships && method_exists($this, 'withRelationships')) {
            $query = $this->withRelationships($query);
        }

        // If no policy, return query as-is (backward compatibility)
        return $query;
    }

    /**
     * Get query with policy filtering applied
     * Use this in your repository methods instead of query()
     */
    protected function queryWithPolicyFilter(): Builder
    {
        $query = $this->query();
        return $this->applyPolicyQueryFilters($query);
    }
}
