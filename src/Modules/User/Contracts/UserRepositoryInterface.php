<?php

namespace App\Modules\User\Contracts;

use App\Core\Contracts\RepositoryInterface;
use App\Modules\User\Database\Models\User;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email): ?User;
}
