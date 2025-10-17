<?php

namespace App\Core\Traits;

trait HasPermissions
{
    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        // For now, return true for all permissions
        // You can implement role-based permissions later
        return true;
    }

    /**
     * Check if user is the owner of a model
     */
    public function isOwnerOf($model): bool
    {
        if (!$model || !isset($model->user_id)) {
            return false;
        }

        return $this->id === $model->user_id;
    }

    /**
     * Check if user can perform action on model based on ownership
     */
    public function canPerformActionOn($model, string $action = 'view'): bool
    {
        // Admin users can do anything
        if ($this->hasPermission('admin.all')) {
            return true;
        }

        // Check ownership
        if ($this->isOwnerOf($model)) {
            return true;
        }

        // Check specific permissions
        $modelClass = class_basename($model);
        $permission = strtolower($modelClass) . '.' . $action;

        return $this->hasPermission($permission);
    }
}
