<?php

namespace App\Modules\Client\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Client\Database\Models\ClientSecret;

class ClientSecretRepository extends BaseRepository
{
    protected string $serviceName = 'ClientSecretRepository';

    public function __construct(ClientSecret $model)
    {
        parent::__construct($model);
    }

    /**
     * Get active secret for a client, module, and action
     */
    public function getByClientModuleAndAction(
        int $clientId,
        string $moduleName,
        ?string $action = null
    ): ?ClientSecret {
        return $this->model
            ->where('client_id', $clientId)
            ->where('module_name', $moduleName)
            ->byAction($action)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all active secrets for a client and module (all actions)
     */
    public function getAllByClientAndModule(int $clientId, string $moduleName): array
    {
        return $this->model
            ->where('client_id', $clientId)
            ->where('module_name', $moduleName)
            ->where('is_active', true)
            ->get()
            ->toArray();
    }

    /**
     * Get secrets JSON for a client, module, and action
     */
    public function getSecretsByClientModuleAndAction(
        int $clientId,
        string $moduleName,
        ?string $action = null
    ): ?array {
        $secret = $this->getByClientModuleAndAction($clientId, $moduleName, $action);
        return $secret?->secrets;
    }

    /**
     * Get a specific secret key from a module's secrets
     */
    public function getSecretKey(
        int $clientId,
        string $moduleName,
        string $key,
        $default = null,
        ?string $action = null
    ) {
        $secrets = $this->getSecretsByClientModuleAndAction($clientId, $moduleName, $action);

        if (!$secrets) {
            return $default;
        }

        return data_get($secrets, $key, $default);
    }
}
