<?php

namespace App\Modules\Swap\Policies;

use App\Core\Policies\BasePolicy;

class SwapPolicy extends BasePolicy
{
    /**
     * The permission prefix used for the swap module
     * @var string
     */
    protected string $permissionPrefix = 'swap';

    /**
     * The column name used for ownership
     * @var string
     */
    protected string $ownershipColumn = 'user_id';

}
