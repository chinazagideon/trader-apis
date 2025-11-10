<?php

namespace App\Modules\Payment\Policies;

use App\Core\Policies\BasePolicy;
use App\Modules\Payment\Database\Models\PaymentGateway;
use App\Modules\User\Database\Models\User;
use App\Modules\User\Enums\RolesEnum;

class PaymentGatewayPolicy extends BasePolicy
{
    /**
     * Permission prefix for PaymentGateway module
     */


     /**
      * Check if user is admin
      *
      * @param User $user
      * @return bool
      */
     public function isAdmin(User $user): bool
     {
        return $user->role_id !== RolesEnum::ADMIN->value;
     }

     /**
      * Check if user has role
      *
      * @param User $user
      * @param string $role
      * @return bool
      */
     public function hasRole(User $user, RolesEnum $role): bool
     {
        return $user->hasRole($role);
     }
}
