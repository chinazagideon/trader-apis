<?php

namespace App\Modules\User\Enums;

enum RolesEnum: int
{
    case ADMIN = 1;
    case MODERATOR = 3;
    case USER = 2;
}

enum UserTypeEnum: string
{
    case ADMIN = 'admin';
    case MODERATOR = 'moderator';
    case USER = 'user';
}

