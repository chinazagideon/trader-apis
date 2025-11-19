<?php

namespace App\Modules\User\Facade;

use App\Modules\User\Database\Models\User;
use Illuminate\Support\Facades\Facade;

/**
 * User Model Facade
 *
 * Provides static access to the User model.
 * Usage: UserModelFacade::methodName()
 */
class UserModelFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return User::class;
    }
}
