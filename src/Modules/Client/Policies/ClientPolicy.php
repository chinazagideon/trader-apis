<?php

namespace App\Modules\Client\Policies;

use App\Core\Policies\BasePolicy;
use App\Modules\User\Database\Models\User;
use App\Modules\User\Enums\RolesEnum;

class ClientPolicy extends BasePolicy
{
    /**
     * Permission prefix for Client module
     */
    protected string $permissionPrefix = 'client';

    /**
     * Ownership column for Client model is 'user_id'
     */
    // protected string $ownershipColumn = 'user_id';

    /**
     * Check if user is super admin
     * @param User $user
     * @return bool
     */
    public function isSuperAdmin(User $user): bool
    {
        dd($user);
        return $user->role_id === RolesEnum::SUPER_ADMIN->value;
    }
}
