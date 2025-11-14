<?php

namespace App\Modules\User\Enums;

enum UserPaymentTypes: string
{
    case Funding = 'funding';
    case Withdrawal = 'withdrawal';

    /**
     * Get the label for the user payment type
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::Funding => 'Funding',
            self::Withdrawal => 'Withdrawal',
            default => 'Unknown',
        };
    }

    /**
     * Get the type for the user payment type
     * @return string
     */
    public function type(): ?string
    {
        return match($this) {
            self::Funding => self::Funding->value,
            self::Withdrawal => self::Withdrawal->value,
            default => null,
        };
    }
}
