<?php

namespace App\Modules\Payment\Policies;

use App\Core\Policies\BasePolicy;
use App\Modules\Payment\Database\Models\PaymentProcessor;
use App\Modules\User\Database\Models\User;
use App\Modules\User\Enums\RolesEnum;

class PaymentProcessorPolicy extends BasePolicy
{
    /**
     * Permission prefix for PaymentProcessor module
     */
    protected string $permissionPrefix = 'payment-processor';

}
