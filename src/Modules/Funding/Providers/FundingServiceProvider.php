<?php

namespace App\Modules\Funding\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use App\Modules\Funding\Database\Models\Funding;
use App\Modules\Funding\Policies\FundingPolicy;
use App\Modules\Funding\Events\FundingWasCompleted;
use Illuminate\Support\Facades\Gate;
use App\Modules\Funding\Services\FundingService;

class FundingServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Module namespace
     */
    protected string $moduleNamespace = 'App\\Modules\\Funding';

     /**
     * Services
     */
    protected array $services = [
        FundingService::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'funding',
    ];

    /**
     * Policies
     */
    protected array $policies = [
        Funding::class => FundingPolicy::class,
    ];

    /**
     * Events
     */
    protected array $events = [
        FundingWasCompleted::class,
    ];

    public function boot(): void
    {
        parent::boot();

    }

}
