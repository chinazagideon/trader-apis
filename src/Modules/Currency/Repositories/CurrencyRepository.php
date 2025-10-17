<?php

namespace App\Modules\Currency\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Currency\Database\Models\Currency;

class CurrencyRepository extends BaseRepository
{
    protected string $serviceName = 'CurrencyRepository';

    /**
     * Constructor
     */
    public function __construct(Currency $model)
    {
        parent::__construct($model);
    }
}
