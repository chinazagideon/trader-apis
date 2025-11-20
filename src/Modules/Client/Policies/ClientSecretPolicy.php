<?php

namespace App\Modules\Client\Policies;

use App\Core\Policies\BasePolicy;

class ClientSecretPolicy extends BasePolicy {

    /**
     * Permission prefix for ClientSecret module
     */
    protected string $permissionPrefix = 'client_secret';

    /**
     * Ownership column for ClientSecret model is 'client_id'
     */
    protected string $ownershipColumn = 'client_id';
}
