<?php

namespace App\Modules\Client\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Client\Database\Models\ClientSecret;

class ClientSecretRepository extends BaseRepository
{
    protected string $serviceName = 'ClientSecretRepository';

    /**
     * Constructor
     * @param ClientSecret $model
     */
    public function __construct(ClientSecret $model)
    {
        parent::__construct($model);
    }

}
