<?php

namespace App\Modules\Role\Services;

use App\Core\Services\BaseService;
use App\Modules\Role\Repositories\RoleRepository;
use App\Core\Http\ServiceResponse;
use App\Modules\Role\Contracts\RoleServiceContract;
use App\Modules\User\Enums\RolesEnum;

class RoleService extends BaseService implements RoleServiceContract
{
    protected string $serviceName = 'RoleService';

    /**
     * Constructor
     */
    public function __construct(
        private RoleRepository $RoleRepository
    ) {
        parent::__construct($RoleRepository);
    }

    /**
     * Get user role
     * @return ServiceResponse
     */
    public function getUserRole(): ServiceResponse
    {
        return $this->executeServiceOperation(function () {
            return $this->RoleRepository->find(
                RolesEnum::USER->value,
                ['id', 'name']
            );
        }, 'get user role by id');
    }

    /**
     * Get moderator role
     * @return ServiceResponse
     */
    public function getModeratorRole(): ServiceResponse
    {
        return $this->executeServiceOperation(function () {
            return $this->RoleRepository->find(RolesEnum::MODERATOR->value, ['id', 'name']);
        }, 'get moderator role');
    }

    /**
     * Get admin role
     * @return ServiceResponse
     */
    public function getAdminRole(): ServiceResponse
    {
        return $this->executeServiceOperation(function () {
            return $this->RoleRepository->find(RolesEnum::ADMIN->value, ['id', 'name']);
        }, 'get admin role');
    }
}
