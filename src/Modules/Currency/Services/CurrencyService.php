<?php

namespace App\Modules\Currency\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Currency\Repositories\CurrencyRepository;

class CurrencyService extends BaseService
{
    protected string $serviceName = 'CurrencyService';

    public function __construct(
        private CurrencyRepository $currencyRepository
    )
    {
        parent::__construct($currencyRepository);
    }

    /**
     * get currency by code
     * @param string $code
     * @return ServiceResponse
     */
    public function getCurrencyByCode(string $code): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($code) {
            $result = $this->currencyRepository->findBy('code', $code, ['id']);
            return $result ? $result->id : null;
        }, 'getCurrencyByCode');
    }

    /**
     * get currency by id
     * @param int $id
     * @return ServiceResponse
     */
    public function getCurrencyById(int $id): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($id) {
            $result = $this->currencyRepository->find($id, ['id']);
            return $result ? $result->id : null;
        }, 'getCurrencyById');
    }


    /**
     * Get currencies with filters and pagination
     * @param array $filters
     * @param int $perPage
     * @return ServiceResponse
     */
    public function index(array $filters = [], int $perPage = 15): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($filters, $perPage) {
            $paginator = $this->currencyRepository->getCurrencies($filters, $perPage);
            return $this->createPaginatedResponse($paginator, 'Currencies retrieved successfully');
        }, 'index');
    }

    /**
     * Get default currency
     * @return ServiceResponse
     */
    public function getDefaultCurrency(): ServiceResponse
    {
        return $this->executeServiceOperation(function () {
            $currency = $this->currencyRepository->getDefaultCurrency();
            return ServiceResponse::success($currency, 'Default currency retrieved successfully');
        }, 'getDefaultCurrency');
    }

    /**
     * Get currency by id
     * @param int $id
     * @return ServiceResponse
     */
    public function getCurrency(int $id): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($id) {
            $result = $this->currencyRepository->find($id, ['id', 'name', 'symbol', 'code', 'type', 'is_default']);
            return $result ? $result : null;
        }, 'getCurrency');
    }
}
