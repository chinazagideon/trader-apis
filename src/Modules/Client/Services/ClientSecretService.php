<?php

namespace App\Modules\Client\Services;

use App\Core\Services\BaseService;
use App\Modules\Client\Repositories\ClientSecretRepository;

class ClientSecretService extends BaseService
{
    protected string $serviceName = 'ClientSecretService';

    /**
     * Constructor
     */
    public function __construct(
        private ClientSecretRepository $clientSecretRepository,
    ) {
        parent::__construct($clientSecretRepository);
    }
}
