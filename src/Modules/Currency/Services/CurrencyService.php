<?php

namespace App\Modules\Currency\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Currency\Repositories\CurrencyRepository;
use App\Modules\Currency\Contracts\CurrencyServiceContract;
use App\Modules\Currency\Enums\CurrencyType;

class CurrencyService extends BaseService implements CurrencyServiceContract
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
     * @param ?array $columns
     * @return ServiceResponse
     */
    public function getCurrencyByCode(string $code, ?array $columns = null): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($code, $columns) {
            $result = $this->currencyRepository->findBy('code', $code, $columns ?? ['*']);
            return empty($columns) ? $result->id : $result;
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

    /**
     * Get currency by type
     * @param string $type
     * @return ServiceResponse
     */
    public function getCurrencyByType(string $type): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($type) {
            $result = $this->currencyRepository->findBy('type', $type, ['id', 'name', 'symbol', 'code', 'type', 'is_default']);
            return $result ? $result : null;
        }, 'getCurrencyByType');
    }

    /**
     * Get currency type by id
     * @param int $id
     * @return ServiceResponse
     */
    public function getCurrencyTypeById(int $id): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($id) {
            $result = $this->currencyRepository->find($id, ['type']);
            return $result ? $result->type : null;
        }, 'getCurrencyTypeById');
    }

    /**
     * Check if currency is fiat
     * @param int $id
     * @return bool
     */
    public function isFiatCurrency(int $id): bool
    {
        $currency = $this->getCurrency($id);
        if (!$currency->isSuccess()) {
            throw new \App\Core\Exceptions\ServiceException($currency->getMessage());
        }
        return $currency->getData()->type === CurrencyType::Fiat->value;
    }
}
