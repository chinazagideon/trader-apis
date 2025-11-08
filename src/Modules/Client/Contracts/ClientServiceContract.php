<?php

namespace App\Modules\Client\Contracts;

use App\Core\Http\ServiceResponse;
use App\Modules\Client\Database\Models\Client;

interface ClientServiceContract
{
    /**
     * Register a new client
     * @param array $data
     * @return Object|null
     */
    public function registerClient(array $data): ?Object;

    /**
     * Generate a client API key
     * @param array $data
     * @return String|null
     */
    public function generateClientApiKey(array $data): ?String;

    /**
     * Generate a client secret
     * @param array $data
     * @return String|null
     */
    public function generateClientSecret(array $data): ?String;

    /**
     * Get a client by API key
     * @param string $apiKey
     * @return Object|null
     */
    public function getClientByApiKey(string $apiKey): ?Object;


}
