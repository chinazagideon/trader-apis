<?php

namespace App\Modules\Client\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use App\Modules\Client\Services\ClientService;
use App\Modules\Client\Contracts\ClientServiceContract;
use App\Modules\Client\Contracts\ClientRepositoryContract;

use App\Modules\Client\Repositories\ClientRepository;

class ClientServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleNamespace = 'App\\Modules\\Client';
    protected string $moduleName = 'Client';

    protected array $services = [
        ClientService::class,
    ];

    protected array $configFiles = [
        'client',
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
