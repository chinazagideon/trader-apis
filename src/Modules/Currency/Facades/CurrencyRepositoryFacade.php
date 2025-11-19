<?php

namespace App\Modules\Currency\Facades;

use App\Modules\Currency\Repositories\CurrencyRepository;
use Illuminate\Support\Facades\Facade;

/**
 * Currency Repository Facade
 *
 * Provides static access to the CurrencyRepository instance.
 * Usage: CurrencyRepositoryFacade::methodName()
 */
class CurrencyRepositoryFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return CurrencyRepository::class;
    }
}
