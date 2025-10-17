<?php

namespace App\Modules\Withdrawal\Repositories;

use App\Modules\Withdrawal\Database\Models\Withdrawal;
use App\Core\Repositories\BaseRepository;

class WithdrawalRepository extends BaseRepository
{
    /**
     * Constructor
     */
    public function __construct(Withdrawal $model)
    {
        parent::__construct($model);
    }
}
