<?php

namespace App\Modules\Funding\Policies;

use App\Core\Policies\BasePolicy;
use App\Modules\User\Database\Models\User;

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

}
