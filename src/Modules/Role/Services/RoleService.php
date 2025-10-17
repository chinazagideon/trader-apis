<?php

namespace App\Modules\Role\Services;

use App\Core\Services\BaseService;
use App\Modules\Role\Repositories\RoleRepository;

class RoleService extends BaseService
{
    protected string $serviceName = 'RoleService';

    /**
     * Constructor
     */
    public function __construct(
        private RoleRepository $RoleRepository
    )
    {
        parent::__construct($RoleRepository);
    }

}
