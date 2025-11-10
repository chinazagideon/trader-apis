<?php

namespace App\Modules\Withdrawal\Enums;

/**
 * Withdrawal types enum
 */
enum WithdrawalTypes: string
{
    case Fee = 'fee';
    case Tax = 'tax';
    case CashOut = 'cash_out';

    /**
     * Get the label for the withdrawal type
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::Fee => 'Fee',
            self::Tax => 'Tax',
            self::CashOut => 'Cash Out',
            default => 'Unknown',
        };
    }
}
