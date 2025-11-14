<?php

namespace App\Modules\User\Enums;

enum UserStatus: string
{
    case Active = 1;
    case Suspended = 2;
    case Inactive = 3;


    /**
     * Get the label for the user status
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Suspended => 'Suspended',
            self::Inactive => 'Inactive',
        };
    }

    /**
     * Get the value for the user status
     * @return int
     */
    public function value(): int
    {
        return $this->value;
    }

    /**
     * Get the default status
     * @return int
     */
    public static function defaultStatus(): int
    {
        return self::Active->value;
    }
}
