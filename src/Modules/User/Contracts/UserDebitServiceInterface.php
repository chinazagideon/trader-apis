<?php

namespace App\Modules\User\Contracts;

use App\Core\Contracts\ServiceInterface;

interface UserDebitServiceInterface extends ServiceInterface
{
    /**
     * Debit
     * @param array $data
     * @return void
     */
    public function debit(array $data = []): void;

    /**
     * Debit available balance
     * @param array $data
     * @return void
     */
    public function debitAvailableBalance(array $data = []): void;

    /**
     * Debit total balance
     * @param array $data
     * @return void
     */
    public function debitTotalBalance(array $data = []): void;

    /**
     * Get the allowed debit types
     * @return array
     */
    public function allowedDebitTypes(): array;
}
