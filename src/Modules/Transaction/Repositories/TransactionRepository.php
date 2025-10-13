<?php

namespace App\Modules\Transaction\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Transaction\Database\Models\Transaction;

class TransactionRepository extends BaseRepository
{
    public function __construct(Transaction $model)
    {
        parent::__construct($model);
    }

}
 