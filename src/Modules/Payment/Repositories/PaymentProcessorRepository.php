<?php

namespace App\Modules\Payment\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Payment\Database\Models\PaymentProcessor;

class PaymentProcessorRepository extends BaseRepository
{
    protected string $serviceName = 'PaymentProcessorRepository';

    public function __construct(PaymentProcessor $model)
    {
        parent::__construct($model);
    }

}
