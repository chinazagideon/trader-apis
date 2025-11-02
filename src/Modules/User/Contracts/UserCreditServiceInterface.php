<?php

namespace App\Modules\User\Contracts;

use App\Core\Contracts\ServiceInterface;

interface UserCreditServiceInterface extends ServiceInterface
{
    /**
     * Add credit to user
     */
    public function credit(array $data = []): bool;

}
