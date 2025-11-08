<?php

namespace App\Modules\Client\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Client\Database\Models\Client;
use App\Modules\Client\Contracts\ClientRepositoryContract;

class ClientRepository extends BaseRepository implements ClientRepositoryContract
{
    protected string $serviceName = 'ClientRepository';

    public function __construct(Client $model)
    {
        parent::__construct($model);
    }

    /**
     * find client by api key
     *
     * @param string $apiKey
     * @return Client|null
     */
    public function findByApiKey(string $apiKey): ?Client
    {
        return $this->queryUnfiltered()
            ->where('api_key', $apiKey)
            ->where('is_active', true)
            ->first();
    }

    /**
     * find client by slug
     *
     * @param string $slug
     * @return Client|null
     */
    public function findBySlug(string $slug): ?Client
    {
        return $this->queryUnfiltered()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }
}
