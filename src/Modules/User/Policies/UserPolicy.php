<?php

namespace App\Modules\User\Policies;

use App\Core\Policies\BasePolicy;
use App\Modules\User\Database\Models\User;

/**
 * User Policy
 *
 * Handles authorization for User model operations.
 *
 * Special considerations for User module:
 * - Users can view their own profile
 * - Admins can view all users
 * - Creating users typically requires admin permissions
 * - Users can update their own profile (with restrictions)
 */
class UserPolicy extends BasePolicy
{
    /**
     * Permission prefix for User module
     */
    protected string $permissionPrefix = 'user';

    /**
     * Ownership column for User model is 'id' (users own themselves)
     */
    protected string $ownershipColumn = 'id';

    /**
     * Determine if the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        // Admins can view all users
        if ($user->hasPermission('admin.all')) {
            return true;
        }

        // Check if user has permission to view any users
        if ($user->hasPermission('user.view_any')) {
            return true;
        }

        // Regular users can access, but will only see themselves (filtered in query)
        return true;
    }

    /**
     * Determine if the user can view the user profile.
     */
    public function view(User $user, \Illuminate\Database\Eloquent\Model $model): bool
    {
        // Cast model to User for type safety
        /** @var User $model */
        $model = $model instanceof User ? $model : null;

        if (!$model) {
            return false;
        }

        // Admins can view any user
        if ($user->hasPermission('admin.all')) {
            return true;
        }

        // Users can always view their own profile
        if ($user->id === $model->id) {
            return true;
        }

        // Check if user has permission to view any users
        return $user->hasPermission('user.view_any');
    }

    /**
     * Determine if the user can create users.
     *
     * Typically, only admins can create users.
     */
    public function create(User $user): bool
    {
        // Admins can always create users
        if ($user->hasPermission('admin.all')) {
            return true;
        }

        // Check if user has create permission
        return $user->hasPermission('user.create');
    }

    /**
     * Determine if the user can update the user profile.
     */
    public function update(User $user, \Illuminate\Database\Eloquent\Model $model): bool
    {
        // Cast model to User for type safety
        /** @var User $model */
        $model = $model instanceof User ? $model : null;

        if (!$model) {
            return false;
        }

        // Admins can update any user
        if ($user->hasPermission('admin.all')) {
            return true;
        }

        // Users can update their own profile
        if ($user->id === $model->id) {
            return true;
        }

        // Check if user has permission to update any users
        return $user->hasPermission('user.update_any');
    }

    /**
     * Determine if the user can delete the user.
     */
    public function delete(User $user, \Illuminate\Database\Eloquent\Model $model): bool
    {
        // Cast model to User for type safety
        /** @var User $model */
        $model = $model instanceof User ? $model : null;

        if (!$model) {
            return false;
        }

        // Admins can delete any user (except themselves)
        if ($user->hasPermission('admin.all') && $user->id !== $model->id) {
            return true;
        }

        // Users cannot delete themselves or others
        // In most cases, user deletion is handled by admins only
        return false;
    }

    /**
     * Apply ownership-based filtering to a query for index operations.
     *
     * For User model, regular users only see themselves unless they have view_any permission.
     */
    public function viewAnyQuery(User $user, \Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        // Admins see all users
        if ($user->hasPermission('admin.all')) {
            return $query;
        }

        // Users with view_any permission see all users
        if ($user->hasPermission('user.view_any')) {
            return $query;
        }

        // Regular users only see themselves
        return $query->where('id', $user->id);
    }

    /**
     * Determine if the user can view user statistics.
     */
    public function viewStatistics(User $user): bool
    {
        // Only admins and users with stats permission can view statistics
        return $user->hasPermission('admin.all') ||
               $user->hasPermission('user.statistics');
    }

    /**
     * Determine if the user can search users.
     */
    public function search(User $user): bool
    {
        // Admins can always search
        if ($user->hasPermission('admin.all')) {
            return true;
        }

        // Check if user has search permission or view_any permission
        return $user->hasPermission('user.search') ||
               $user->hasPermission('user.view_any');
    }
}

