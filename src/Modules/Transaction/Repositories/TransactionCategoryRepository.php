<?php

namespace App\Modules\Transaction\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Transaction\Database\Models\TransactionCategory;

class TransactionCategoryRepository extends BaseRepository
{
    public function __construct(TransactionCategory $model)
    {
        parent::__construct($model);
    }

}
