<?php

namespace App\Modules\Funding\Enums;

/**
 * Funding types enum
 */
enum FundingType: string
{
    case Deposit = 'deposit';
    case Profit = 'profit';
    case Commission = 'commission';
    case Transfer = 'transfer';

    /**
     * Get the label for the funding type
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::Deposit => 'Deposit',
            self::Profit => 'Profit',
            self::Commission => 'Commission',
            self::Transfer => 'Transfer',
            default => 'Unknown',
        };
    }

}
