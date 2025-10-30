<?php

namespace App\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Modules\User\Enums\RolesEnum;
use App\Core\Exceptions\UnauthenticatedException;
use App\Core\Exceptions\UnauthorizedException;
use Illuminate\Database\Eloquent\Model;

trait AppliesOwnershipFilters
{
    private string $defaultUserIdKey = 'user_id';
    private array $allowedActions = ['view', 'create', 'update', 'delete'];
    private array $statuses = ['pending', 'cancelled', 'completed'];

     /**
     * Apply ownership and role-based filters to query
     */
    protected function applyOwnershipFilters(Builder $query, string $action = 'view'): Builder
    {
        $user = auth()->user();

        if (!$user) {
            throw new UnauthenticatedException('User not authenticated');
        }

        // Admin users can do everything
        if ($user->role_id === RolesEnum::ADMIN->value) {
            return $query;
        }

        // Check if model implements OwnershipBased interface
        if ($this->modelImplementsOwnership()) {
            // Moderator users can see limited data
            if ($user->role_id === RolesEnum::MODERATOR->value) {
                return $this->applyModeratorFilters($query, $action);
            }

            // Regular users can only see their own data
            return $query->where($this->defaultUserIdKey, $user->id);
        }

        // For non-ownership models, apply role-based access
        return $this->applyRoleBasedAccess($query, $user, $action);
    }

    /**
     * Check if the model implements OwnershipBased interface
     */
    protected function modelImplementsOwnership(): bool
    {
        return $this->model instanceof \App\Core\Contracts\OwnershipBased;
    }

    /**
     * Apply ownership validation for create operations
     */
    protected function validateCreateOwnership(array $data): array
    {
        $user = auth()->user();

        if (!$user) {
            throw new UnauthenticatedException('User not authenticated');
        }

        // Admin users can create for anyone
        if ($user->role_id === RolesEnum::ADMIN->value) {
            return $data;
        }

        // For ownership-based models, ensure user_id is set to current user
        if ($this->modelImplementsOwnership()) {
            // Force user_id to current user (prevent privilege escalation)
            $data[$this->defaultUserIdKey] = $user->id;

            // Moderators can create but with restrictions
            if ($user->role_id === RolesEnum::MODERATOR->value) {
                $data = $this->applyModeratorCreateRestrictions($data);
            }
        }
        // For non-ownership models, only admins can create
        else {
            if ($user->role_id !== RolesEnum::ADMIN->value) {
                throw new UnauthorizedException('Insufficient permissions to create this resource');
            }
        }

        return $data;
    }

    /**
     * Apply ownership validation for update operations
     */
    protected function validateUpdateOwnership(Model $model, array $data): array
    {
        $user = auth()->user();

        if (!$user) {
            throw new UnauthenticatedException('User not authenticated');
        }

        // Admin users can update anything
        if ($user->role_id === RolesEnum::ADMIN->value) {
            return $data;
        }

        // For ownership-based models, check ownership
        if ($this->modelImplementsOwnership()) {
            // Check if user owns the model
            if ($model->{$this->defaultUserIdKey} !== $user->id) {
                throw new UnauthorizedException('You can only update your own resources');
            }

            // Moderators have additional restrictions
            if ($user->role_id === RolesEnum::MODERATOR->value) {
                $data = $this->applyModeratorUpdateRestrictions($model, $data);
            }

            // Prevent privilege escalation - don't allow changing user_id
            unset($data[$this->defaultUserIdKey]);
        } else {
            // For non-ownership models, only admins can update
            throw new UnauthorizedException('Insufficient permissions to update this resource');
        }

        return $data;
    }

    /**
     * Apply moderator restrictions for create operations
     */
    protected function applyModeratorCreateRestrictions(array $data): array
    {
        // Moderators can only create with specific statuses
        if (isset($data['status']) && !in_array($data['status'], ['pending', 'cancelled'])) {
            $data['status'] = 'pending'; // Force to pending
        }

        return $data;
    }

    /**
     * Apply moderator restrictions for update operations
     */
    protected function applyModeratorUpdateRestrictions(Model $model, array $data): array
    {
        // Moderators can only update pending records
        if (method_exists($model, 'getStatusColumn')) {
            $statusColumn = $model->getStatusColumn();
            if ($model->{$statusColumn} !== 'pending') {
                throw new UnauthorizedException('You can only update pending records');
            }
        }

        // Moderators can only update to specific statuses
        if (isset($data['status']) && !in_array($data['status'], ['cancelled', 'completed'])) {
            unset($data['status']);
        }

        return $data;
    }
    /**
     * Apply moderator-specific filters
     */
    protected function applyModeratorFilters(Builder $query, string $action): Builder
    {
        // Moderators can see all records but with limited actions
        switch ($action) {
            case 'view':
                // Moderators can view all but only pending/cancelled/completed
                if (method_exists($this->model, 'getStatusColumn')) {
                    $statusColumn = $this->model->getStatusColumn();
                    $query->whereIn($statusColumn, $this->statuses);
                }
                break;

            case 'update':
                // Moderators can only update pending records
                if (method_exists($this->model, 'getStatusColumn')) {
                    $statusColumn = $this->model->getStatusColumn();
                    $query->where($statusColumn, 'pending');
                }
                break;
        }

        return $query;
    }

    /**
     * Apply role-based access for models without user ownership
     */
    protected function applyRoleBasedAccess(Builder $query, $user, string $action): Builder
    {
        // For system models (like Currency, Role, etc.), only admins can access
        // if ($user->role_id !== RolesEnum::ADMIN->value) {
            // Return empty query for non-admin users
            // return $query->whereRaw('1 = 0');
        // }

        return $query;
    }

    /**
     * Get the status column name for the model
     */
    protected function getModelStatusColumn(): ?string
    {
        $fillable = $this->model->getFillable();

        // Common status column names
        $statusColumns = ['status', 'state', 'approval_status'];

        foreach ($statusColumns as $column) {
            if (in_array($column, $fillable)) {
                return $column;
            }
        }

        return null;
    }
}
