<?php

namespace App\Modules\Currency\Contracts;

use App\Core\Contracts\ServiceInterface;
use App\Core\Http\ServiceResponse;

interface CurrencyServiceContract extends ServiceInterface
{

    /**
     * Get currency by code
     * @param string $code
     * @return ServiceResponse
     */
    public function getCurrencyByCode(string $code): ServiceResponse;

    /**
     * Get default currency
     * @return ServiceResponse
     */
    public function getDefaultCurrency(): ServiceResponse;


    /**
     * Get currency by type
     * @param string $type
     * @return ServiceResponse
     */
    public function getCurrencyByType(string $type): ServiceResponse;


    /**
     * Get currency by id
     * @param int $id
     * @return ServiceResponse
     */
    public function getCurrencyTypeById(int $id): ServiceResponse;

    /**
     * get currency
     *
     * @param int $id
     * @return ServiceResponse
     */
    public function getCurrency(int $id): ServiceResponse;

    /**
     * Check if currency is fiat
     * @param int $id
     * @return bool
     */
    public function isFiatCurrency(int $id): bool;
}
