<?php

namespace App\Modules\User\Enums;

enum RolesEnum: int
{
    case ADMIN = 1;
    case MODERATOR = 3;
    case USER = 2;
    case SUPER_ADMIN = 4;

    /**
     * Get the label for the role
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::MODERATOR => 'Moderator',
            self::USER => 'User',
            default => 'Unknown',
        };
    }

    /**
     * Get the default role
     * @return int
     */
    public static function defaultRole(): int
    {
        return self::USER->value;
    }

    /**
     * Get the role by value
     * @param int $value
     * @return RolesEnum|null
     */
    public static function getRoleByValue(int $value): ?RolesEnum
    {
        return self::tryFrom($value);
    }
}


enum UserTypeEnum: string
{
    case ADMIN = 'admin';
    case MODERATOR = 'moderator';
    case USER = 'user';
    case SUPER_ADMIN = 'super_admin';
}

