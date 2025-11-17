<?php

namespace App\Modules\Client\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use App\Modules\Client\Services\ClientService;
use App\Modules\Client\Contracts\ClientServiceContract;
use App\Modules\Client\Contracts\ClientRepositoryContract;
use App\Modules\Client\Policies\ClientPolicy;
use App\Modules\Client\Repositories\ClientRepository;
use App\Modules\Client\Database\Models\Client;

class ClientServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Namespace for the Client module
     */
    protected string $moduleNamespace = 'App\\Modules\\Client';
    protected string $moduleName = 'Client';

    /**
     * Services for the Client module
     */
    protected array $services = [
        ClientService::class,
    ];

    /**
     * Config files for the Client module
     */
    protected array $configFiles = [
        'client',
    ];

    /**
     * Policies for the Client module
     */
    protected array $policies = [
        ClientPolicy::class => Client::class,
    ];

    protected function registerServices(): void
    {
        $this->app->bind(
            ClientServiceContract::class,
            ClientService::class
        );

        $this->app->bind(
            ClientRepositoryContract::class,
            ClientRepository::class
        );
    }
}
