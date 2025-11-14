<?php

namespace App\Modules\User\Providers;

use App\Modules\User\Contracts\UserServiceInterface;
use App\Modules\User\Contracts\UserBalanceServiceInterface;
use App\Modules\User\Contracts\UserCreditServiceInterface;
use App\Modules\User\Contracts\UserRepositoryInterface;
use App\Modules\User\Contracts\UserDebitServiceInterface;
use App\Modules\User\Database\Models\User;
use App\Modules\User\Policies\UserPolicy;
use App\Modules\User\Repositories\UserRepository;
use App\Modules\User\Services\UserBalanceService;
use App\Modules\User\Services\UserCreditService;
use App\Modules\User\Services\UserService;
use App\Modules\User\Services\UserDebitService;
use App\Core\Providers\BaseModuleServiceProvider;
use Illuminate\Support\Facades\Gate;

class UserServiceProvider extends BaseModuleServiceProvider
{
    /**
     * The module namespace
     */
    protected string $moduleNamespace = 'App\\Modules\\User';

    /**
     * The module name
     */
    protected string $moduleName = 'User';

    /**
     * Services
     */
    protected array $services = [
        UserService::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'user',
    ];

    /**
     * Register services
     */
    protected function registerServices(): void
    {
        /**
         * Register UserRepository as singleton
         */
        $this->app->singleton(UserRepository::class, function ($app) {
            return new UserRepository($app->make(User::class));
        });

        /**
         * Register UserService as singleton for facade access
         */
        $this->app->singleton(UserService::class);

        /**
         * Bind UserServiceInterface to UserService
         */
        $this->app->bind(UserServiceInterface::class, UserService::class);

        /**
         * Bind UserBalanceServiceInterface to UserBalanceService
         */
        $this->app->bind(UserBalanceServiceInterface::class, UserBalanceService::class);

        /**
         * Bind UserCreditServiceInterface to UserCreditService
         */
        $this->app->bind(UserCreditServiceInterface::class, UserCreditService::class);

        /**
         * Bind UserRepositoryInterface to UserRepository
         */
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        /**
         * Bind UserDebitServiceInterface to UserDebitService
         */
        $this->app->bind(UserDebitServiceInterface::class, UserDebitService::class);
    }

    /**
     * Get module namespace
     */
    public function getModuleNamespace(): string
    {
        return $this->moduleNamespace;
    }

    /**
     * Get module name
     */
    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    /**
     * Bootstrap the application services.
     * Register the UserPolicy with the User model.
     */
    public function boot(): void
    {
        parent::boot();

        // Register UserPolicy
        Gate::policy(User::class, UserPolicy::class);

        //register events
        $this->app->register(UserEventServiceProvider::class);
    }
}
