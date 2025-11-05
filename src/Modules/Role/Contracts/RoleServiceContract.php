<?php

namespace App\Modules\Role\Contracts;

use App\Core\Http\ServiceResponse;

interface RoleServiceContract
{
    /**
     * Get user role
     * @return ServiceResponse
     */
    public function getUserRole(): ServiceResponse;
    /**
     * Get moderator role
     * @return ServiceResponse
     */
    public function getModeratorRole(): ServiceResponse;
    /**
     * Get admin role
     * @return ServiceResponse
     */
    public function getAdminRole(): ServiceResponse;
}
