<?php

namespace App\Modules\Investment\Policies;

use App\Modules\User\Database\Models\User;
use App\Modules\Investment\Database\Models\Investment;

class InvestmentPolicy
{
    /**
     * Determine if the user can view any investments
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('investment.view_any') ||
               $user->hasPermission('admin.all');
    }

    /**
     * Determine if the user can view the investment
     */
    public function view(User $user, Investment $investment): bool
    {
        // Owner can always view their investments
        if ($user->isOwnerOf($investment)) {
            return true;
        }

        // Check if user has permission to view all investments
        return $user->hasPermission('investment.view_any');
    }

    /**
     * Determine if the user can create investments
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('investment.create') ||
               $user->hasPermission('admin.all');
    }

    /**
     * Determine if the user can update the investment
     */
    public function update(User $user, Investment $investment): bool
    {
        // Owner can update their investments
        if ($user->isOwnerOf($investment)) {
            return true;
        }

        // Check admin permissions
        return $user->hasPermission('investment.update_any') ||
               $user->hasPermission('admin.all');
    }

    /**
     * Determine if the user can delete the investment
     */
    public function delete(User $user, Investment $investment): bool
    {
        // Owner can delete their investments
        if ($user->isOwnerOf($investment)) {
            return true;
        }

        // Check admin permissions
        return $user->hasPermission('investment.delete_any') ||
               $user->hasPermission('admin.all');
    }

    /**
     * Determine if the user can view investment statistics
     */
    public function viewStatistics(User $user): bool
    {
        return $user->hasPermission('investment.statistics') ||
               $user->hasPermission('admin.all');
    }
}
