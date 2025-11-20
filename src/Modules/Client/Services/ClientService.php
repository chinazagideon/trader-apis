<?php

namespace App\Modules\Client\Services;

use App\Core\Exceptions\AppException;
use App\Core\Services\BaseService;
use App\Modules\Client\Repositories\ClientRepository;
use App\Modules\Client\Contracts\ClientServiceContract;
use App\Modules\Client\Exceptions\ClientException;
use App\Core\Http\ServiceResponse;
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
        if (app()->runningInConsole()) {
            $response = parent::store($data);
            if ($response->isSuccess()) {
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
    public function generateClientApiKey(?array $data = []): ?string
    {
        return Str::uuid();
    }

    /**
     * Generate a client secret
     * @param array $data
     * @return String|null
     */
    public function generateClientSecret(?array $data = []): ?string
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

    /**
     * Activate a client
     * @param string $apiKey
     * @return Object|null
     */
    public function activateClient(array $data): ?object
    {
        return $this->executeServiceOperation(function () use ($data) {
            $client = $this->clientRepository->findByApiKey($data['api_key']);
            if ($client && $client->is_active) {
                throw new AppException('Client is already activated');
            }
            $client->update(['is_active' => true]);
            $client = $this->clientRepository->refresh($client);
            return ServiceResponse::success($client, 'Client activated successfully');
        }, 'activate client');
    }

    /**
     * Deactivate a client
     * @param string $apiKey
     * @return Object|null
     */
    public function deactivateClient(array $data): ?object
    {
        return $this->executeServiceOperation(function () use ($data) {
            $client = $this->clientRepository->findByApiKey($data['api_key']);
            if (!$client->is_active) {
                throw new AppException('Client is already deactivated');
            }
            $client->update(['is_active' => false]);
            $client = $this->clientRepository->refresh($client);
            return ServiceResponse::success($client, 'Client deactivated successfully');
        }, 'deactivate client');
    }

    /**
     * Update a client
     * @param string $apiKey
     * @param array $data
     * @return Object|null
     */
    public function updateClient(string $apiKey, array $data): ?object
    {
        return $this->executeServiceOperation(function () use ($apiKey, $data) {
            $client = $this->clientRepository->findByApiKey($apiKey);
            $client->update($data);
            return $client;
        }, 'update client');
    }

    /**
     * Prepare client data
     * @param array $data
     * @return array
     */
    public function prepareClientData(array $data): array
    {
        $apiKey = $this->generateClientApiKey();
        $apiSecret = $this->generateClientSecret();
        $this->validateRequiredClientConfigKeys($data);
        return [
            "name" => $data['name'],
            "slug" => $data['slug'],
            "config" => $data['config'],
            "api_key" => $apiKey,
            "api_secret" => $apiSecret
        ];
    }

    /**
     * Validate required client config keys
     * @param array $data
     * @return void
     */
    private function validateRequiredClientConfigKeys(array $data): void
    {
        $requiredKeys = [
            'guest_view_enabled',
            'auth_view_enabled'
        ];

        if (!isset($data['config']) || !is_array($data['config'])) {
            throw new AppException('Config is required and must be an array');
        }

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $data['config'])) {
                throw new AppException("{$key} is missing in configuration");
            }
        }
    }

    /**
     * Update a client config
     * @param array $data
     * @return Object|null
     */
    public function updateClientConfig(array $data): ?object
    {

        $preparedData = $this->prepareClientUpdateData($data);
        $client = $this->clientRepository->findByApiKey($data['api_key']);

        $existingConfig = (array) $client->config;
        $existingFeatures = (array) $client->features;


        $client->config = array_merge($existingConfig, $preparedData);
        $client->features = array_merge($existingFeatures, $preparedData);

        $client = $this->clientRepository->update($client->id, $client->config, $client->features);

        return $client->refresh();
    }

    /**
     * Prepare client update data
     * @param array $data
     * @return array
     */
    public function prepareClientUpdateData(array $data): array
    {
        $this->validateRequiredClientConfigKeys($data);
        return [
            "config" => $data['config'],
            "features" => $data['features'],
        ];
    }
}
