<?php

namespace App\Modules\Payment\Policies;

use App\Core\Policies\BasePolicy;
use App\Modules\Payment\Database\Models\Payment;
use App\Modules\User\Database\Models\User;

class PaymentPolicy extends BasePolicy
{
    protected string $permissionPrefix = 'payment';
    protected string $ownershipColumn = 'user_id';

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('payment.view_any');
    }
}
