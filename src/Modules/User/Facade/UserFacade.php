<?php

namespace App\Modules\User\Facade;

use App\Modules\User\Services\UserService;
use Illuminate\Support\Facades\Facade;

/**
 * User Facade
 *
 * Provides static access to the UserService instance.
 * Usage: UserFacade::methodName()
 */
class UserFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return UserService::class;
    }
}
