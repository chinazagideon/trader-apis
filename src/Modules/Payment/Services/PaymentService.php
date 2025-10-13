<?php

namespace App\Modules\Payment\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Payment\Repositories\PaymentRepository;

class PaymentService extends BaseService
{
    protected string $serviceName = 'PaymentService';

    public function __construct(
        private PaymentRepository $PaymentRepository
    )
    {
        parent::__construct($PaymentRepository);
    }

}