<?php

namespace App\Core\Traits;

trait FiltersResponsesByOwnership
{
    /**
     * Filter collection by ownership
     */
    protected function filterByOwnership($query, $user = null)
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return $query;
        }

        // If user can view all, don't filter
        if ($user->hasPermission('admin.all')) {
            return $query;
        }

        // Filter by user_id
        return $query->where('user_id', $user->id);
    }

    /**
     * Filter single model by ownership
     */
    protected function filterModelByOwnership($model, $user = null)
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return null;
        }

        // If user can view all, return model
        if ($user->hasPermission('admin.all')) {
            return $model;
        }

        // Check ownership
        if ($user->isOwnerOf($model)) {
            return $model;
        }

        return null;
    }
}
