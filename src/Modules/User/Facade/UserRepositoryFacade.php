<?php

namespace App\Modules\User\Facade;

use App\Modules\User\Repositories\UserRepository as RepositoriesUserRepository;
use Illuminate\Support\Facades\Facade;

/**
 * User Repository Facade
 *
 * Provides static access to the UserRepository instance.
 * Usage: UserRepositoryFacade::methodName()
 */
class UserRepositoryFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return RepositoriesUserRepository::class;
    }
}
