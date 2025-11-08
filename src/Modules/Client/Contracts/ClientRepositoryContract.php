<?php

namespace App\Modules\Client\Contracts;

use App\Modules\Client\Database\Models\Client;

/**
 * Client repository contract
 *
 * @package App\Modules\Client\Contracts
 */
interface ClientRepositoryContract
{
    /**
     * find a client by api key
     *
     * @param string $apiKey
     * @return Client|null
     */
    public function findByApiKey(string $apiKey): ?Client;

    /**
     * find a client by slug
     *
     * @param string $slug
     * @return Client|null
     */
    public function findBySlug(string $slug): ?Client;

}
