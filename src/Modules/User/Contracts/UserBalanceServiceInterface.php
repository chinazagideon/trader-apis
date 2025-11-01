<?php

namespace App\Modules\User\Contracts;

use App\Core\Contracts\ServiceInterface;

interface UserBalanceServiceInterface extends ServiceInterface
{
    /**
     * Check if user has sufficient balance for a transaction
     */
    public function checkBalance(array $data = []): bool;
}
