<?php

namespace App\Modules\Role\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Role\Services\RoleService;
use App\Core\Http\ServiceResponse;
use Illuminate\Http\JsonResponse;

class RoleController extends CrudController
{
    public function __construct(
        private RoleService $roleService
    ) {
        parent::__construct($roleService);
    }

    public function hello(): JsonResponse
    {
        return $this->successResponse([], 'Hello from Role module');
    }
}
