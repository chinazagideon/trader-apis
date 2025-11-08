<?php

namespace App\Modules\Client\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Client\Services\ClientService;
use App\Core\Http\ServiceResponse;

class ClientController extends CrudController
{
    public function __construct(
        private ClientService $clientService,
    ) {
        parent::__construct($clientService);
    }

    public function getClientScope(array $request): ServiceResponse
    {

        // $request->validated();
        $getClientFromHeader = $this->clientService->getClientByApiKey($request['api_key']);
        if (!$getClientFromHeader->isSuccess()) {
            return $getClientFromHeader;
        }

        return $getClientFromHeader;
    }
}
