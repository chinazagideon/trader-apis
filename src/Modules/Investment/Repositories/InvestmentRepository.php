<?php

namespace App\Modules\Investment\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Investment\Database\Models\Investment;
use Illuminate\Pagination\LengthAwarePaginator;

class InvestmentRepository extends BaseRepository
{
    public function __construct(Investment $model)
    {
        parent::__construct($model);
    }


}
