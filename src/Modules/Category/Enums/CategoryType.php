<?php

namespace App\Modules\Category\Enums;

enum CategoryType: int
{
    case Investment = 1;
    case User = 2;
    case Payment = 5;

    /**
     * Get the label for the category type
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::Investment => 'Investment',
            self::User => 'User',
            self::Payment => 'Payment',
            default => 'Unknown',
        };
    }

    /**
     * Get the default type
     * @return int
     */
    public static function getDefaultType(): int
    {
        return self::Investment->value;
    }
}
