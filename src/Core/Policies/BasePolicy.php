<?php

namespace App\Core\Policies;

use App\Modules\User\Database\Models\User;
use App\Core\Contracts\OwnershipBased;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Base Policy class for ownership-based authorization
 *
 * This class provides a foundation for policies that need to handle
 * ownership-based access control, following Laravel's recommended patterns.
 *
 * Usage:
 * - Extend this class in your module policies
 * - Override methods as needed for custom authorization logic
 * - Use viewAnyQuery() for automatic query filtering in index operations
 */
abstract class BasePolicy
{
    /**
     * Permission prefix for the module (e.g., 'investment', 'user', 'funding')
     * Should be overridden in child classes
     */
    protected string $permissionPrefix = '';

    /**
     * Column name used for ownership (default: 'user_id')
     * Can be overridden in child classes if needed
     */
    protected string $ownershipColumn = 'user_id';

    /**
     * Determine if the user can view any models.
     *
     * By default, allows access if user has view_any permission or is admin.
     * The actual filtering is done in viewAnyQuery() method.
     */
    public function viewAny(User $user): bool
    {
        // Admins can always view any
        if ($user->hasPermission('admin.all')) {
            return true;
        }

        // Check if user has permission to view any
        if ($this->permissionPrefix && $user->hasPermission($this->permissionPrefix . '.view_any')) {
            return true;
        }

        // For ownership-based resources, allow access (will be filtered in query)
        return true;
    }

    /**
     * Determine if the user can view the model.
     */
    public function view(User $user, Model $model): bool
    {
        // Admins can always view
        if ($user->hasPermission('admin.all')) {
            return true;
        }

        // Check ownership if model implements OwnershipBased
        if ($model instanceof OwnershipBased && $this->isOwner($user, $model)) {
            return true;
        }

        // Check if user has permission to view any
        if ($this->permissionPrefix && $user->hasPermission($this->permissionPrefix . '.view_any')) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can create models.
     */
    public function create(User $user): bool
    {
        // Admins can always create
        if ($user->hasPermission('admin.all')) {
            return true;
        }

        // Check if user has create permission
        if ($this->permissionPrefix && $user->hasPermission($this->permissionPrefix . '.create')) {
            return true;
        }

        // By default, authenticated users can create their own resources
        return true;
    }

    /**
     * Determine if the user can update the model.
     */
    public function update(User $user, Model $model): bool
    {
        // Admins can always update
        if ($user->hasPermission('admin.all')) {
            return true;
        }

        // Check ownership if model implements OwnershipBased
        if ($model instanceof OwnershipBased && $this->isOwner($user, $model)) {
            return true;
        }

        // Check if user has permission to update any
        if ($this->permissionPrefix && $user->hasPermission($this->permissionPrefix . '.update_any')) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can delete the model.
     */
    public function delete(User $user, Model $model): bool
    {
        // Admins can always delete
        if ($user->hasPermission('admin.all')) {
            return true;
        }

        // Check ownership if model implements OwnershipBased
        if ($model instanceof OwnershipBased && $this->isOwner($user, $model)) {
            return true;
        }

        // Check if user has permission to delete any
        if ($this->permissionPrefix && $user->hasPermission($this->permissionPrefix . '.delete_any')) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can restore the model.
     */
    public function restore(User $user, Model $model): bool
    {
        return $this->delete($user, $model);
    }

    /**
     * Determine if the user can permanently delete the model.
     */
    public function forceDelete(User $user, Model $model): bool
    {
        // Only admins can force delete
        return $user->hasPermission('admin.all');
    }

    /**
     * Apply ownership-based filtering to a query for index operations.
     *
     * This method is called from repositories/services to automatically
     * filter queries based on ownership when using policies.
     *
     * @param User $user
     * @param Builder $query
     * @return Builder
     */
    public function viewAnyQuery(User $user, Builder $query): Builder
    {
        // Admins see all
        if ($user->hasPermission('admin.all')) {
            return $query;
        }

        // For ownership-based models, filter by ownership column
        $modelInstance = $query->getModel();

        if ($modelInstance instanceof OwnershipBased) {
            // Get ownership column (check if model has getOwnershipColumn method)
            $column = method_exists($modelInstance, 'getOwnershipColumn')
                ? $modelInstance->getOwnershipColumn()
                : $this->ownershipColumn;

            return $query->where($column, $user->id);
        }

        // For non-ownership models, check if user has view_any permission
        if ($this->permissionPrefix && $user->hasPermission($this->permissionPrefix . '.view_any')) {
            return $query;
        }

        // Default: return empty query for non-admins without permissions
        return $query->whereRaw('1 = 0');
    }

    /**
     * Check if user is owner of the model.
     */
    protected function isOwner(User $user, Model $model): bool
    {
        // Use the isOwnerOf method if available
        if (method_exists($user, 'isOwnerOf')) {
            return $user->isOwnerOf($model);
        }

        // Fallback: check ownership column directly
        $column = method_exists($model, 'getOwnershipColumn')
            ? $model->getOwnershipColumn()
            : $this->ownershipColumn;

        if (!isset($model->{$column})) {
            return false;
        }

        return $user->id === $model->{$column};
    }

    /**
     * Get the permission prefix for this policy.
     */
    protected function getPermissionPrefix(): string
    {
        return $this->permissionPrefix;
    }

    /**
     * Get the ownership column name.
     */
    protected function getOwnershipColumn(): string
    {
        return $this->ownershipColumn;
    }
}

