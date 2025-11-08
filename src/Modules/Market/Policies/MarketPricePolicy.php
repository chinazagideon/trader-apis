<?php

namespace App\Modules\Market\Policies;

use App\Core\Policies\BasePolicy;

class MarketPricePolicy extends BasePolicy
{

    /**
     * @inheritDoc
     *
     * @var string
     */
    protected string $permissionPrefix = 'market';

}
