<?php

namespace App\Modules\User\Enums;

enum RolesEnum: int
{
    case ADMIN = 1;
    case MODERATOR = 2;
    case USER = 3;
}
