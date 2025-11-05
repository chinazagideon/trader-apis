<?php

namespace App\Modules\Investment\Policies;

use App\Modules\User\Database\Models\User;
use App\Modules\Investment\Database\Models\Investment;
use App\Core\Policies\BasePolicy;
use Illuminate\Database\Eloquent\Model;
class InvestmentPolicy extends BasePolicy
{
    /**
     * Permission prefix for Investment module
     */
    protected string $permissionPrefix = 'investment';

    /**
     * Ownership column for Investment model is 'user_id'
     */
    protected string $ownershipColumn = 'user_id';

    /**
     * Determine if the user can view investment statistics
     */
    public function viewStatistics(User $user): bool
    {
        return $user->hasPermission('investment.statistics') ||
               $user->hasPermission('admin.all');
    }

    public function view(User $user, Model $model): bool
    {
        /** @var Investment $investment  = $model */
        return $this->isOwner($user, $model);
    }
}
