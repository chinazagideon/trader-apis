<?php

namespace App\Modules\User\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\User\Database\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository
{
    /**
     * The key to get the user ID
     */
    public string $getUserIdKey = 'user_id';

    /**
     * Constructor
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }



    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findBy('email', $email);
    }

    /**
     * Find active users
     */
    public function findActiveUsers(): Collection
    {
        return $this->query()->active()->get();
    }

    /**
     * Find verified users
     */
    public function findVerifiedUsers(): Collection
    {
        return $this->query()->verified()->get();
    }

    /**
     * Search users
     */
    public function searchUsers(string $search, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->search($search)
            ->paginate($perPage);
    }

    /**
     * Get users with filters
     */
    public function getUsersWithFilters(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->queryUnfiltered();
        $this->applyBusinessFilters($query, $filters);
        return $this->withRelationships($query)->paginate($perPage);
    }

    /**
     * Get user statistics
     */
    public function getUserStats(): array
    {
        return [
            'total' => $this->count(),
            'active' => $this->query()->active()->count(),
            'verified' => $this->query()->verified()->count(),
            'inactive' => $this->query()->where('is_active', false)->count(),
            'unverified' => $this->query()->whereNull('email_verified_at')->count(),
        ];
    }

    /**
     * Get default relationships for the user model
     */
    protected function getDefaultRelationships(): array
    {
        return ['role', 'payments'];
    }
}
