<?php

namespace App\Modules\User\Enums;

enum UserBalanceEnum: string
{
    case Commission = 'commission';
    case Withdraw = 'withdraw';
    case Deposit = 'deposit';
    case Transfer = 'transfer';
    case Payment = 'payment';


    /**
     * Get the label for the user balance type
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::Commission => 'Commission',
            self::Withdraw => 'Withdraw',
            self::Deposit => 'Deposit',
            self::Transfer => 'Transfer',
            self::Payment => 'Payment',
            default => 'Unknown',
        };
    }
}
