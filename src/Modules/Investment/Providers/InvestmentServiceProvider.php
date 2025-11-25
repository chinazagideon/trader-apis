<?php

namespace App\Modules\Investment\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use App\Modules\Investment\Services\InvestmentService;
use Illuminate\Support\Facades\Gate;
use App\Modules\Investment\Database\Models\Investment;
use App\Modules\Investment\Policies\InvestmentPolicy;
use App\Modules\Investment\Observer\InvestmentObserver;

class InvestmentServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Module namespace
     */
    protected string $moduleNamespace = 'App\\Modules\\Investment';

    /**
     * Module name
     */
    protected string $moduleName = 'Investment';

    /**
     * Allowed types
     */
    protected array $allowedTypes;

    /**
     * Services
     */
    protected array $services = [
        InvestmentService::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'investment',
    ];

    /**
     * Policies
     */
    protected array $policies = [
        Investment::class => InvestmentPolicy::class,
    ];

    /**
     * Bootstrap the service provider.
     */
    public function boot(): void
    {
        parent::boot();

        Investment::observe(InvestmentObserver::class);
    }
}
