<?php

namespace App\Modules\Client\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Client\Services\ClientService;
use App\Core\Http\ServiceResponse;
use Illuminate\Http\Request;

class ClientController extends CrudController
{
    public function __construct(
        private ClientService $clientService,
    ) {
        parent::__construct($clientService);
    }

    public function getClientScope(array $request): ServiceResponse
    {

        $requestApiKey = $request['api_key'];
        $getClientFromHeader = $this->clientService->getClientByApiKey($requestApiKey);
        if (!$getClientFromHeader->isSuccess()) {
            return $getClientFromHeader;
        }

        return $getClientFromHeader;
    }

    /**
     * Activate a client
     * @param array $request
     * @return ServiceResponse
     */
    public function activateClient(array $request): ServiceResponse
    {
        return $this->clientService->activateClient($request);
    }

    /**
     * Deactivate a client
     * @param array $request
     * @return ServiceResponse
     */
    public function deactivateClient(array $request): ServiceResponse
    {
        return $this->clientService->deactivateClient($request);
    }

    /**
     * before store
     * @param array $data
     * @param Request $request
     * @return array
     */
    protected function beforeStore(array $data, Request $request): array
    {
        $processData = $this->clientService->prepareClientData($data);
        return $processData;
    }

    /**
     * update client config
     * @param array $data
     * @return ServiceResponse
     */
    public function ConfigUpdate(array $data): ServiceResponse
    {
        return $this->clientService->executeServiceOperation(function () use ($data) {
            $client = $this->clientService->updateClientConfig($data);
            return ServiceResponse::success($client, 'Client config updated successfully');
        }, 'update client config');
    }
}
