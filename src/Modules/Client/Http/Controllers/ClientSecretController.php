<?php

namespace App\Modules\Client\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Client\Services\ClientSecretService;

class ClientSecretController extends CrudController
{
    /**
     * Constructor
     * @param ClientSecretService $clientSecretService
     */
    public function __construct(
        private ClientSecretService $clientSecretService,
    ) {
        parent::__construct($clientSecretService);
    }
}
