<?php

namespace App\Modules\Client\Services;

use App\Core\Services\BaseService;
use App\Core\Traits\HasClientApp;
use App\Modules\Client\Repositories\ClientSecretRepository;
use App\Modules\Client\Database\Models\ClientSecret;

class ClientSecretService extends BaseService
{
    use HasClientApp;

    protected string $serviceName = 'ClientSecretService';

    public function __construct(
        private ClientSecretRepository $clientSecretRepository,
    ) {
        parent::__construct($clientSecretRepository);
    }

    /**
     * Get secrets for current client context and module
     */
    public function getSecretsForModule(string $moduleName, ?string $action = null): ?array
    {
        $clientId = $this->getClientId();

        if (!$clientId) {
            return null; 
        }

        return $this->clientSecretRepository->getSecretsByClientModuleAndAction(
            $clientId,
            $moduleName,
            $action
        );
    }

    /**
     * Get a specific secret key for current client, module, and action
     */
    public function getSecretKey(
        string $moduleName,
        string $key,
        $default = null,
        string $action
    ) {
        $clientId = $this->getClientId();

        if (!$clientId) {
            return $default;
        }

        return $this->clientSecretRepository->getSecretKey(
            $clientId,
            $moduleName,
            $key,
            $default,
            $action
        );
    }

    /**
     * Get ClientSecret model for current client, module, and action
     */
    public function getSecretForModule(string $moduleName, string $action): ?ClientSecret
    {
        $clientId = $this->getClientId();

        if (!$clientId) {
            return null;
        }

        return $this->clientSecretRepository->getByClientModuleAndAction(
            $clientId,
            $moduleName,
            $action
        );
    }


    /**
     * Get secrets for a specific client, module, and action (bypasses context)
     */
    public function getSecretsForClientModuleAndAction(
        int $clientId,
        string $moduleName,
        string $action
    ): ?array {
        return $this->clientSecretRepository->getSecretsByClientModuleAndAction(
            $clientId,
            $moduleName,
            $action
        );
    }
}
