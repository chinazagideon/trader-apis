<?php

namespace App\Modules\Withdrawal\Policies;

use App\Modules\Withdrawal\Database\Models\Withdrawal;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Core\Policies\BasePolicy;

class WithdrawalPolicy extends BasePolicy
{

    protected string $permissionPrefix = 'withdrawal';

    /**
     * The column name used for ownership
     * @var string
     */
    protected string $ownershipColumn = 'user_id';


}
