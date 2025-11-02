<?php

namespace App\Modules\Role\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Role\Database\Models\Role;

class RoleRepository extends BaseRepository
{
    protected string $serviceName = 'RoleRepository';

    /**
     * Constructor
     */
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }
}
