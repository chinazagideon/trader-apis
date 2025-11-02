<?php

namespace App\Modules\Market\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Market\Database\Models\Market;

class MarketRepository extends BaseRepository
{
    protected string $serviceName = 'MarketRepository';

    public function __construct(Market $model)
    {
        parent::__construct($model);
    }
}
