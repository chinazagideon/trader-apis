<?php

namespace App\Modules\User\Contracts;

use App\Core\Contracts\ServiceInterface;
use App\Core\Http\ServiceResponse;

interface UserServiceInterface extends ServiceInterface
{
    /**
     * Create a new user
     */
    public function create(array $data): ServiceResponse;

    /**
     * Find user by ID
     */
    public function findById(int $id): ServiceResponse;

    /**
     * Find user by UUID
     */
    public function findByUuid(string $uuid): ServiceResponse;

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ServiceResponse;

    /**
     * Update user
     */
    public function update(int $id, array $data): ServiceResponse;

    /**
     * Delete user
     */
    public function delete(int $id): ServiceResponse;

    /**
     * Get all users with pagination
     */
    public function getAll(array $filters = [], int $page = 1, int $perPage = 15): ServiceResponse;

    /**
     * Search users
     */
    public function searchUsers(string $search, int $page = 1, int $perPage = 15): ServiceResponse;

    /**
     * Get user statistics
     */
    public function getUserStats(): ServiceResponse;

    /**
     * Credit available balance
     */
    public function creditAvailableBalance(array $data = []): ServiceResponse;

    /**
     * Credit commission balance
     */
    public function creditCommissionBalance(array $data = []): ServiceResponse;
}
