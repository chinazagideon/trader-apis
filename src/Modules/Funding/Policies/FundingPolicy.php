<?php

namespace App\Modules\Funding\Policies;

use App\Core\Policies\BasePolicy;
use App\Modules\User\Database\Models\User;
use Illuminate\Database\Eloquent\Model;

class FundingPolicy extends BasePolicy
{

    /**
     * Permission prefix for Funding module
     */
    protected string $permissionPrefix = 'funding';

    /**
     * Ownership column for Funding model is 'user_id'
     */
    protected string $ownershipColumn = 'user_id';

    /**
     * Determine if the user can view the funding.
     */
    public function view(User $user, Model $model): bool
    {
        /** @var Funding $funding  = $model */
        return $this->isOwner($user, $model);
    }



}
