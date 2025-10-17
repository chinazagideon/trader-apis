<?php

namespace App\Modules\Funding\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Funding\Repositories\FundingRepository;

class FundingService extends BaseService
{
    protected string $serviceName = 'FundingService';

    public function __construct(
        private FundingRepository $FundingRepository
    )
    {
        parent::__construct($FundingRepository);
    }

}