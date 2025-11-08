<?php

namespace App\Modules\Client\Services;

use App\Core\Services\BaseService;
use App\Modules\Client\Repositories\ClientRepository;
use App\Modules\Client\Contracts\ClientServiceContract;
use Illuminate\Support\Str;

class ClientService extends BaseService implements ClientServiceContract
{
    protected string $serviceName = 'ClientService';

    public function __construct(
        private ClientRepository $clientRepository
    ) {
        parent::__construct($clientRepository);
    }

    /**
     * @inheritDoc
     */
    public function registerClient(array $data): ?object
    {
        if(app()->runningInConsole()) {
            $response = parent::store($data);
            if($response->isSuccess()) {
                return $response->getData();
            }
            return null;
        }

        return null;
    }

    /**
     * Generate a client API key
     * @param array $data
     * @return String|null
     */
    public function generateClientApiKey(array $data): ?string
    {
        return Str::uuid();
    }

    /**
     * Generate a client secret
     * @param array $data
     * @return String|null
     */
    public function generateClientSecret(array $data): ?string
    {
        return Str::uuid();
    }

    /**
     * Get a client by API key
     * @param string $apiKey
     * @return Object|null
     */
    public function getClientByApiKey(string $apiKey): ?object
    {
        return $this->executeServiceOperation(function () use ($apiKey) {
            $client = $this->clientRepository->findByApiKey($apiKey);
            if (!$client) {
                return null;
            }
            return $client;
        }, 'get client by api key');
    }
}
