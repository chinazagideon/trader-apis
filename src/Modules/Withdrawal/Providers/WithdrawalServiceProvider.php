<?php

namespace App\Modules\Withdrawal\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use App\Modules\Withdrawal\Services\WithdrawalService;
use App\Modules\Withdrawal\Database\Models\Withdrawal;
use App\Modules\Withdrawal\Policies\WithdrawalPolicy;

class WithdrawalServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Module namespace
     */
    protected string $moduleNamespace = 'App\\Modules\\Withdrawal';

     /**
     * Services
     */
    protected array $services = [
        WithdrawalService::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'withdrawal',
    ];

    /**
     * Policies
     */
    protected array $policies = [
        Withdrawal::class => WithdrawalPolicy::class,
    ];

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Register events
        $this->app->register(WithdrawalEventServiceProvider::class);
    }
}
