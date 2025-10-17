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
}
