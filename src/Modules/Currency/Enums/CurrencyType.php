<?php

namespace App\Modules\Currency\Enums;


/**
 * Currency types enum
 */
enum CurrencyType: string
{
    case Fiat = 'fiat';
    case Crypto = 'crypto';

    /**
     * Get the label for the currency type
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::Fiat => 'Fiat',
            self::Crypto => 'Crypto',
            default => 'Unknown',
        };
    }

    /**
     * Get the default type
     * @return string
     */
    public static function getDefaultType(): string
    {
        return self::Fiat->value;
    }
}
